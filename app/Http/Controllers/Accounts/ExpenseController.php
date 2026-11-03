<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\FinanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    public function __construct(private FinanceService $finance) {}

    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'all');

        $query = Expense::with(['category', 'account', 'creator', 'reverser'])
            ->when($request->filled('q'), fn ($q) => $q->search($request->q))
            ->when($request->filled('category_id'), fn ($q) => $q->where('expense_category_id', $request->category_id))
            ->when($request->filled('method'), fn ($q) => $q->where('payment_method', $request->method))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->where('expense_date', '>=', Carbon::parse($request->from)->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('expense_date', '<=', Carbon::parse($request->to)->endOfDay()));

        if ($tab === 'approved') {
            $query->approved();
        } elseif ($tab === 'cash') {
            $query->approved()->where('payment_method', 'cash');
        } elseif ($tab === 'bank') {
            $query->approved()->whereIn('payment_method', ['bank_transfer', 'cheque']);
        } elseif ($tab === 'upi') {
            $query->approved()->where('payment_method', 'upi');
        } elseif ($tab === 'reversed') {
            $query->reversed();
        }

        $expenses = $query->latest('expense_date')->latest('id')->paginate(20)->withQueryString();

        $approvedExpenses = Expense::where('status', Expense::STATUS_APPROVED);
        $totalExpenses = (string) (clone $approvedExpenses)->sum('amount');
        $approvedCount = (clone $approvedExpenses)->count();
        $totalCount = Expense::count();
        $todayExpenses = (string) (clone $approvedExpenses)->whereDate('expense_date', now()->toDateString())->sum('amount');
        $todayCount = (clone $approvedExpenses)->whereDate('expense_date', now()->toDateString())->count();
        $cashExpenses = (string) (clone $approvedExpenses)->where('payment_method', 'cash')->sum('amount');
        $cashCount = (clone $approvedExpenses)->where('payment_method', 'cash')->count();
        $bankExpenses = (string) (clone $approvedExpenses)->whereIn('payment_method', ['bank_transfer', 'cheque'])->sum('amount');
        $bankCount = (clone $approvedExpenses)->whereIn('payment_method', ['bank_transfer', 'cheque'])->count();
        $upiExpenses = (string) (clone $approvedExpenses)->where('payment_method', 'upi')->sum('amount');
        $upiCount = (clone $approvedExpenses)->where('payment_method', 'upi')->count();
        $reversedCount = Expense::where('status', Expense::STATUS_REVERSED)->count();

        $kpis = [
            'totalExpenses' => $totalExpenses,
            'approvedCount' => $approvedCount,
            'totalCount' => $totalCount,
            'todayExpenses' => $todayExpenses,
            'todayCount' => $todayCount,
            'cashExpenses' => $cashExpenses,
            'cashCount' => $cashCount,
            'bankExpenses' => $bankExpenses,
            'bankCount' => $bankCount,
            'upiExpenses' => $upiExpenses,
            'upiCount' => $upiCount,
            'digitalExpenses' => (string) bcadd($bankExpenses, $upiExpenses, 2),
            'digitalCount' => $bankCount + $upiCount,
            'reversedCount' => $reversedCount,
        ];

        $tabCounts = [
            'all' => $totalCount,
            'approved' => $approvedCount,
            'cash' => $cashCount,
            'bank' => $bankCount,
            'upi' => $upiCount,
            'reversed' => $reversedCount,
        ];

        return view('accounts.expenses.index', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::where('is_active', true)->orderBy('name')->get(),
            'methods' => Expense::PAYMENT_METHODS,
            'kpis' => $kpis,
            'tabCounts' => $tabCounts,
            'activeTab' => $tab,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Expense::with(['category', 'account', 'creator', 'reverser'])
            ->when($request->filled('q'), fn ($q) => $q->search($request->q))
            ->when($request->filled('category_id'), fn ($q) => $q->where('expense_category_id', $request->category_id))
            ->when($request->filled('method'), fn ($q) => $q->where('payment_method', $request->method))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->where('expense_date', '>=', Carbon::parse($request->from)->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('expense_date', '<=', Carbon::parse($request->to)->endOfDay()));

        $tab = $request->input('tab');
        if ($tab === 'approved') {
            $query->approved();
        } elseif ($tab === 'cash') {
            $query->approved()->where('payment_method', 'cash');
        } elseif ($tab === 'bank') {
            $query->approved()->whereIn('payment_method', ['bank_transfer', 'cheque']);
        } elseif ($tab === 'upi') {
            $query->approved()->where('payment_method', 'upi');
        } elseif ($tab === 'reversed') {
            $query->reversed();
        }

        $filename = 'expenses_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Voucher #',
                'Expense Date',
                'Status',
                'Category Code',
                'Expense Category',
                'Paid To (Payee)',
                'Payment Mode',
                'Account Name',
                'Transaction Reference',
                'Amount (₹)',
                'Remarks & Purpose',
                'Recorded By',
                'Recorded At',
            ]);

            $query->latest('expense_date')->chunk(200, function ($expenses) use ($handle) {
                foreach ($expenses as $e) {
                    fputcsv($handle, [
                        $e->voucher_number,
                        $e->expense_date?->format('Y-m-d') ?? '',
                        strtoupper($e->status),
                        $e->category?->code ?? '',
                        $e->category?->name ?? '',
                        $e->paid_to,
                        $e->method_label,
                        $e->account?->name ?? '',
                        $e->transaction_reference ?? '',
                        $e->amount,
                        $e->remarks ?? '',
                        $e->creator?->name ?? '',
                        $e->created_at?->format('Y-m-d H:i:s') ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(): View
    {
        return view('accounts.expenses.form', [
            'expense' => new Expense([
                'expense_date' => now(),
                'payment_method' => 'cash',
            ]),
            'categories' => ExpenseCategory::where('is_active', true)->orderBy('name')->get(),
            'accounts' => CashAccount::where('is_active', true)->orderBy('name')->get(),
            'methods' => Expense::PAYMENT_METHODS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'expense_date' => ['required', 'date'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'cash_account_id' => ['nullable', 'exists:cash_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:10000000'],
            'payment_method' => ['required', 'in:cash,bank_transfer,upi,cheque'],
            'paid_to' => ['required', 'string', 'max:150'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $expense = $this->finance->recordExpense($data, $request->user()->id);

            return redirect()->route('expenses.show', $expense)
                ->with('success', "Expense voucher {$expense->voucher_number} recorded.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Expense $expense): View
    {
        $expense->load(['category', 'account', 'creator', 'reverser']);

        return view('accounts.expenses.show', compact('expense'));
    }

    public function reverse(Request $request, Expense $expense): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:5', 'max:255']]);

        try {
            $this->finance->reverseExpense($expense, $request->reason, $request->user()->id);

            return redirect()->route('expenses.show', $expense)
                ->with('success', "Expense voucher {$expense->voucher_number} reversed.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
