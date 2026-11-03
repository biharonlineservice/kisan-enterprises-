<?php

namespace App\Services;

use App\Models\Crop;
use App\Models\InventoryTransaction;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InventoryService
{
    /**
     * Get real-time stock balance in quintals for a given warehouse and crop/variety.
     */
    public function getStock(int $warehouseId, int $cropId, ?int $varietyId = null): string
    {
        return InventoryTransaction::getStock($warehouseId, $cropId, $varietyId);
    }

    /**
     * Get stock balances grouped by warehouse and crop, joined with crop names and low stock status.
     */
    public function getStockSummary(?int $warehouseId = null): Collection
    {
        $threshold = (float) setting('inventory.low_stock_threshold_qtl', 10);

        return DB::table('inventory_transactions as it')
            ->join('warehouses as w', 'w.id', '=', 'it.warehouse_id')
            ->join('crops as c', 'c.id', '=', 'it.crop_id')
            ->leftJoin('crop_varieties as cv', 'cv.id', '=', 'it.crop_variety_id')
            ->when($warehouseId, fn ($q) => $q->where('it.warehouse_id', $warehouseId))
            ->select(
                'it.warehouse_id',
                'w.name as warehouse_name',
                'w.code as warehouse_code',
                'it.crop_id',
                'c.name as crop_name',
                'c.code as crop_code',
                'c.unit as crop_unit',
                'it.crop_variety_id',
                'cv.name as variety_name',
                DB::raw('SUM(it.quantity_kg) as total_kg'),
                DB::raw('SUM(it.quantity_qtl) as total_qtl')
            )
            ->groupBy('it.warehouse_id', 'w.name', 'w.code', 'it.crop_id', 'c.name', 'c.code', 'c.unit', 'it.crop_variety_id', 'cv.name')
            ->havingRaw('total_qtl != 0')
            ->orderBy('w.name')
            ->orderBy('c.name')
            ->get()
            ->map(function ($row) use ($threshold) {
                $row->total_qtl_rounded = bc_round((string) $row->total_qtl, 3);
                $row->total_kg_rounded = bc_round((string) $row->total_kg, 3);
                $row->is_low_stock = (float) $row->total_qtl <= $threshold;

                return $row;
            });
    }

    /**
     * Transfer stock between two warehouses.
     */
    public function transfer(array $data, int $userId): StockTransfer
    {
        $fromWh = (int) $data['from_warehouse_id'];
        $toWh = (int) $data['to_warehouse_id'];
        $cropId = (int) $data['crop_id'];
        $varietyId = ! empty($data['crop_variety_id']) ? (int) $data['crop_variety_id'] : null;

        if ($fromWh === $toWh) {
            throw new InvalidArgumentException('Source and destination warehouses cannot be the same.');
        }

        $qtyKg = bc_round((string) $data['quantity_kg'], 3);
        if (bccomp($qtyKg, '0', 3) <= 0) {
            throw new InvalidArgumentException('Transfer quantity must be greater than zero.');
        }
        $qtyQtl = bcdiv($qtyKg, '100', 3);

        // Check source stock unless negative stock is explicitly permitted in Settings
        $allowNegative = (bool) setting('inventory.allow_negative_stock', false);
        if (! $allowNegative) {
            $available = $this->getStock($fromWh, $cropId, $varietyId);
            if (bccomp($available, $qtyQtl, 3) < 0) {
                throw new RuntimeException("Insufficient stock in source warehouse. Available: {$available} Qtl, Requested: {$qtyQtl} Qtl.");
            }
        }

        return DB::transaction(function () use ($data, $fromWh, $toWh, $cropId, $varietyId, $qtyKg, $qtyQtl, $userId) {
            $transferNumber = DocumentNumber::next('stock_transfer');

            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'transfer_date' => $data['transfer_date'] ?? now(),
                'from_warehouse_id' => $fromWh,
                'to_warehouse_id' => $toWh,
                'crop_id' => $cropId,
                'crop_variety_id' => $varietyId,
                'quantity_kg' => $qtyKg,
                'quantity_qtl' => $qtyQtl,
                'vehicle_number' => $data['vehicle_number'] ?? null,
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // Ledger entry 1: Outward from source warehouse
            InventoryTransaction::create([
                'warehouse_id' => $fromWh,
                'crop_id' => $cropId,
                'crop_variety_id' => $varietyId,
                'transaction_date' => $transfer->transfer_date,
                'quantity_kg' => bcmul($qtyKg, '-1', 3),
                'quantity_qtl' => bcmul($qtyQtl, '-1', 3),
                'source_type' => 'stock_transfer',
                'source_id' => $transfer->id,
                'reference_number' => $transferNumber,
                'direction' => 'out',
                'description' => "Transfer out to {$transfer->toWarehouse->name} ({$transferNumber})",
                'created_by' => $userId,
            ]);

            // Ledger entry 2: Inward to destination warehouse
            InventoryTransaction::create([
                'warehouse_id' => $toWh,
                'crop_id' => $cropId,
                'crop_variety_id' => $varietyId,
                'transaction_date' => $transfer->transfer_date,
                'quantity_kg' => $qtyKg,
                'quantity_qtl' => $qtyQtl,
                'source_type' => 'stock_transfer',
                'source_id' => $transfer->id,
                'reference_number' => $transferNumber,
                'direction' => 'in',
                'description' => "Transfer in from {$transfer->fromWarehouse->name} ({$transferNumber})",
                'created_by' => $userId,
            ]);

            AuditLogger::log('stock.transferred', $transfer, [], $transfer->toArray(), "Transferred {$qtyQtl} Qtl from {$transfer->fromWarehouse->name} to {$transfer->toWarehouse->name}", $userId);

            return $transfer;
        });
    }

    /**
     * Request a stock adjustment (damage, reconciliation, shrinkage).
     */
    public function requestAdjustment(array $data, int $userId): StockAdjustment
    {
        $qtyKg = bc_round((string) $data['quantity_kg'], 3);
        if (bccomp($qtyKg, '0', 3) === 0) {
            throw new InvalidArgumentException('Adjustment quantity cannot be zero.');
        }
        $qtyQtl = bcdiv($qtyKg, '100', 3);

        $adjustmentNumber = DocumentNumber::next('stock_adjustment');

        $adj = StockAdjustment::create([
            'adjustment_number' => $adjustmentNumber,
            'adjustment_date' => $data['adjustment_date'] ?? now(),
            'warehouse_id' => $data['warehouse_id'],
            'crop_id' => $data['crop_id'],
            'crop_variety_id' => $data['crop_variety_id'] ?? null,
            'adjustment_type' => $data['adjustment_type'],
            'quantity_kg' => $qtyKg,
            'quantity_qtl' => $qtyQtl,
            'status' => StockAdjustment::STATUS_PENDING,
            'reason' => $data['reason'],
            'created_by' => $userId,
        ]);

        AuditLogger::log('stock_adjustment.requested', $adj, [], $adj->toArray(), "Requested stock adjustment {$adj->adjustment_number} of {$qtyQtl} Qtl", $userId);

        return $adj;
    }

    /**
     * Approve stock adjustment: records in ledger.
     */
    public function approveAdjustment(StockAdjustment $adj, int $userId): void
    {
        if (! $adj->isPending()) {
            throw new RuntimeException("Stock adjustment {$adj->adjustment_number} is already {$adj->status}.");
        }

        DB::transaction(function () use ($adj, $userId) {
            $direction = bccomp((string) $adj->quantity_kg, '0', 3) > 0 ? 'in' : 'out';

            InventoryTransaction::create([
                'warehouse_id' => $adj->warehouse_id,
                'crop_id' => $adj->crop_id,
                'crop_variety_id' => $adj->crop_variety_id,
                'transaction_date' => now(),
                'quantity_kg' => $adj->quantity_kg,
                'quantity_qtl' => $adj->quantity_qtl,
                'source_type' => 'stock_adjustment',
                'source_id' => $adj->id,
                'reference_number' => $adj->adjustment_number,
                'direction' => $direction,
                'description' => "{$adj->type_name}: {$adj->reason} ({$adj->adjustment_number})",
                'created_by' => $userId,
            ]);

            $adj->update([
                'status' => StockAdjustment::STATUS_APPROVED,
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            AuditLogger::log('stock_adjustment.approved', $adj, ['status' => 'pending'], ['status' => 'approved'], "Approved stock adjustment {$adj->adjustment_number}", $userId);
        });
    }

    /**
     * Reject stock adjustment.
     */
    public function rejectAdjustment(StockAdjustment $adj, string $reason, int $userId): void
    {
        if (! $adj->isPending()) {
            throw new RuntimeException("Stock adjustment {$adj->adjustment_number} is already {$adj->status}.");
        }

        $adj->update([
            'status' => StockAdjustment::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        AuditLogger::log('stock_adjustment.rejected', $adj, ['status' => 'pending'], ['status' => 'rejected', 'reason' => $reason], "Rejected stock adjustment {$adj->adjustment_number}", $userId);
    }
}
