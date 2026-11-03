<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Services\FinanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CashAccountController extends Controller
{
    public function __construct(private FinanceService $finance) {}

    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'all');
        $query = CashAccount::query();

        if ($tab === 'cash') {
            $query->where('account_type', CashAccount::TYPE_CASH);
        } elseif ($tab === 'bank') {
            $query->where('account_type', CashAccount::TYPE_BANK);
        } elseif ($tab === 'active') {
            $query->where('is_active', true);
        }

        if ($search = $request->query('q')) {
            $query->search($search);
        }

        $accounts = $query->orderBy('account_type')->orderBy('name')->get();

        // Metrics across all accounts regardless of tab/search
        $allAccounts = CashAccount::orderBy('account_type')->orderBy('name')->get();
        $totalCash = '0.00';
        $totalBank = '0.00';
        $cashCount = 0;
        $bankCount = 0;
        $activeCount = 0;

        foreach ($allAccounts as $acc) {
            if ($acc->is_active) {
                $activeCount++;
            }
            if ($acc->isCash()) {
                $cashCount++;
                if ($acc->is_active) {
                    $totalCash = bcadd($totalCash, (string) $acc->current_balance, 2);
                }
            } else {
                $bankCount++;
                if ($acc->is_active) {
                    $totalBank = bcadd($totalBank, (string) $acc->current_balance, 2);
                }
            }
        }

        $totalLiquidity = bcadd($totalCash, $totalBank, 2);

        $counts = [
            'all' => $allAccounts->count(),
            'cash' => $cashCount,
            'bank' => $bankCount,
            'active' => $activeCount,
        ];

        return view('accounts.index', [
            'accounts' => $accounts,
            'allAccounts' => $allAccounts,
            'totalCash' => $totalCash,
            'totalBank' => $totalBank,
            'totalLiquidity' => $totalLiquidity,
            'counts' => $counts,
            'activeTab' => $tab,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $accounts = CashAccount::orderBy('account_type')->orderBy('name')->get();
        $filename = 'cash_bank_accounts_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($accounts) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // Excel UTF-8 BOM
            fputcsv($handle, ['ID', 'Account Name', 'Type', 'Bank Name', 'Account Number', 'IFSC Code', 'Opening Balance', 'Current Balance', 'Status']);

            foreach ($accounts as $acc) {
                fputcsv($handle, [
                    $acc->id,
                    $acc->name,
                    strtoupper($acc->account_type),
                    $acc->bank_name ?? '-',
                    $acc->account_number ?? '-',
                    $acc->ifsc_code ?? '-',
                    number_format((float) $acc->opening_balance, 2, '.', ''),
                    number_format((float) $acc->current_balance, 2, '.', ''),
                    $acc->is_active ? 'Active' : 'Inactive',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'account_type' => ['required', 'in:cash,bank'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'ifsc_code' => ['nullable', 'string', 'max:20'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
        ]);

        $data['current_balance'] = $data['opening_balance'];

        CashAccount::create($data);

        return back()->with('success', "Account {$data['name']} created.");
    }

    public function transfer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'transfer_date' => ['required', 'date'],
            'from_account_id' => ['required', 'exists:cash_accounts,id', 'different:to_account_id'],
            'to_account_id' => ['required', 'exists:cash_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'from_account_id.different' => 'Source and destination accounts must be different.',
        ]);

        try {
            $this->finance->transferAccount($data, $request->user()->id);

            return back()->with('success', 'Account transfer completed.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
