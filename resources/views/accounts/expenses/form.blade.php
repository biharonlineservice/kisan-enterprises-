@extends('layouts.app')

@section('title', 'Record Expense')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
            <li class="breadcrumb-item active">New Voucher</li>
        </ol>
    </nav>
    <h2 class="h4 mb-0">Record Operating Expense</h2>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title h6 mb-0"><i class="bi bi-receipt text-primary me-1"></i> Expense Particulars</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('expenses.store') }}">
                    @csrf
                    <x-form-token />

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label required">Expense Date</label>
                            <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror"
                                value="{{ old('expense_date', now()->format('Y-m-d')) }}" required>
                            @error('expense_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required">Expense Category</label>
                            <select name="expense_category_id" class="form-select @error('expense_category_id') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('expense_category_id') == $cat->id)>
                                        {{ $cat->name }} ({{ $cat->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('expense_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required">{{ __('app.total_amount') }} ({{ setting('locale.currency_symbol', '₹') }})</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ setting('locale.currency_symbol', '₹') }}</span>
                                <input type="number" step="0.01" min="0.01" inputmode="decimal" name="amount"
                                    class="form-control text-end fw-bold @error('amount') is-invalid @enderror"
                                    value="{{ old('amount') }}" required placeholder="0.00">
                            </div>
                            @error('amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required">{{ __('procurement.payments.payment_mode') }}</label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                @foreach($methods as $k => $lbl)
                                    <option value="{{ $k }}" @selected(old('payment_method') === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required">{{ __('accounts.payee') }}</label>
                            <input type="text" name="paid_to" class="form-control @error('paid_to') is-invalid @enderror"
                                value="{{ old('paid_to') }}" placeholder="Vendor, labor contractor, transporter name" required>
                            @error('paid_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('accounts.from_account') }} (Optional)</label>
                            <select name="cash_account_id" class="form-select @error('cash_account_id') is-invalid @enderror">
                                <option value="">None / Not Tracked</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" @selected(old('cash_account_id') == $acc->id)>
                                        {{ $acc->name }} (Balance: {{ money($acc->current_balance) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('cash_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('procurement.payments.transaction_ref') }}</label>
                            <input type="text" name="transaction_reference" class="form-control @error('transaction_reference') is-invalid @enderror"
                                value="{{ old('transaction_reference') }}" placeholder="Bill #, UTR, Cheque #">
                            @error('transaction_reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('app.remarks') }}</label>
                            <textarea name="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror"
                                placeholder="Purpose of expenditure, labor count, godown reference...">{{ old('remarks') }}</textarea>
                            @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-none d-md-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> {{ __('accounts.add_expense') }}
                        </button>
                    </div>

                    {{-- Mobile Sticky Action Bar (< 768px) --}}
                    <div class="mobile-sticky-actions">
                        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">{{ __('app.cancel') }}</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> {{ __('app.save') }}
                        </button>
                    </div>
                    <div class="pb-5 mb-4 d-md-none"></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-start border-4 border-info">
            <div class="card-body">
                <h6 class="card-title text-info"><i class="bi bi-info-circle me-1"></i> Expense Recording Policy</h6>
                <p class="small text-muted mb-2">
                    - Direct operational expenses impact the business Profit & Loss account immediately.
                </p>
                <p class="small text-muted mb-2">
                    - If an account is selected, the expense amount is automatically deducted from that cash till or bank account.
                </p>
                <p class="small text-muted mb-0">
                    - In case of errors, an authorized manager can reverse the voucher, restoring the account balance.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
