<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'all');

        $query = ExpenseCategory::query()
            ->withCount('expenses')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            });

        if ($tab === 'active') {
            $query->where('is_active', true);
        } elseif ($tab === 'inactive') {
            $query->where('is_active', false);
        } elseif ($tab === 'with_expenses') {
            $query->has('expenses');
        } elseif ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categoryTotals = Expense::where('status', Expense::STATUS_APPROVED)
            ->groupBy('expense_category_id')
            ->selectRaw('expense_category_id, SUM(amount) as total_amount')
            ->pluck('total_amount', 'expense_category_id');

        $categoryVouchers = Expense::where('status', Expense::STATUS_APPROVED)
            ->groupBy('expense_category_id')
            ->selectRaw('expense_category_id, COUNT(*) as voucher_count')
            ->pluck('voucher_count', 'expense_category_id');

        $metrics = [
            'totalCategories' => ExpenseCategory::count(),
            'activeCategories' => ExpenseCategory::where('is_active', true)->count(),
            'inactiveCategories' => ExpenseCategory::where('is_active', false)->count(),
            'withExpensesCount' => ExpenseCategory::has('expenses')->count(),
            'totalVouchers' => Expense::where('status', Expense::STATUS_APPROVED)->count(),
            'totalApprovedAmount' => (float) Expense::where('status', Expense::STATUS_APPROVED)->sum('amount'),
        ];

        return view('masters.expense_categories.index', compact('categories', 'metrics', 'tab', 'categoryTotals', 'categoryVouchers'));
    }

    public function export(Request $request): StreamedResponse
    {
        $tab = $request->input('tab', 'all');

        $query = ExpenseCategory::query()
            ->withCount('expenses')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            });

        if ($tab === 'active') {
            $query->where('is_active', true);
        } elseif ($tab === 'inactive') {
            $query->where('is_active', false);
        } elseif ($tab === 'with_expenses') {
            $query->has('expenses');
        } elseif ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->orderBy('name')->get();

        $categoryTotals = Expense::where('status', Expense::STATUS_APPROVED)
            ->groupBy('expense_category_id')
            ->selectRaw('expense_category_id, SUM(amount) as total_amount')
            ->pluck('total_amount', 'expense_category_id');

        $categoryVouchers = Expense::where('status', Expense::STATUS_APPROVED)
            ->groupBy('expense_category_id')
            ->selectRaw('expense_category_id, COUNT(*) as voucher_count')
            ->pluck('voucher_count', 'expense_category_id');

        $filename = 'expense-categories-master-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($categories, $categoryTotals, $categoryVouchers) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, [
                'ID',
                'Category Name',
                'Code',
                'Description',
                'Approved Vouchers Count',
                'Total Approved Spend (INR)',
                'Status',
            ]);

            foreach ($categories as $cat) {
                $approvedSpend = (float) ($categoryTotals[$cat->id] ?? 0);
                $approvedVouchers = (int) ($categoryVouchers[$cat->id] ?? 0);

                fputcsv($handle, [
                    $cat->id,
                    $cat->name,
                    $cat->code,
                    $cat->description ?? '',
                    $approvedVouchers,
                    number_format($approvedSpend, 2, '.', ''),
                    $cat->is_active ? 'Active' : 'Inactive',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:30', 'unique:expense_categories,code'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));

        ExpenseCategory::create($data);

        return back()->with('success', "Expense category {$data['name']} created.");
    }

    public function update(Request $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:30', Rule::unique('expense_categories', 'code')->ignore($expenseCategory->id)],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));

        $expenseCategory->update($data);

        return back()->with('success', "Expense category updated.");
    }

    public function toggle(ExpenseCategory $expenseCategory): RedirectResponse
    {
        $expenseCategory->update(['is_active' => ! $expenseCategory->is_active]);

        return back()->with('success', "Expense category status toggled.");
    }
}
