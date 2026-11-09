@extends('layouts.app')

@section('title', 'Website Inquiries')

@section('content')
<div class="page-toolbar">
    <div>
        <h2 class="h5 fw-bold mb-1">Trade Inquiries & Public Messages</h2>
        <p class="subtitle">Review leads and inquiries submitted by grain buyers, rice mills, and farmers through the public website.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('website.admin.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil-square"></i> Website CMS Editor
        </a>
    </div>
</div>

<div class="card">
    {{-- Filter Bar --}}
    <div class="p-3 bg-light bg-opacity-50 border-bottom erp-filter-collapsible">
        <form method="GET" action="{{ route('website.admin.inquiries') }}" class="row g-2 align-items-end" data-autosubmit>
            <div class="col-6 col-md-5">
                <label class="form-label small fw-semibold text-muted mb-1">Filter by Inquiry Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Inquiry Types</option>
                    <option value="bulk_buyer" {{ request('type') === 'bulk_buyer' ? 'selected' : '' }}>Bulk Buyer / Rice Mill</option>
                    <option value="farmer" {{ request('type') === 'farmer' ? 'selected' : '' }}>Farmer Procurement</option>
                    <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>General Inquiry</option>
                </select>
            </div>

            <div class="col-6 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New Unread Only</option>
                    <option value="responded" {{ request('status') === 'responded' ? 'selected' : '' }}>Responded Only</option>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1 d-none d-md-block">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold shadow-sm flex-grow-1"><i class="bi bi-funnel me-1"></i> Filter</button>
                    @if(request()->anyFilled(['type', 'status']))
                        <a href="{{ route('website.admin.inquiries') }}" class="btn btn-sm btn-outline-secondary" title="Clear Filters"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Inquiries Table --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 130px;">Received At</th>
                    <th>Sender & Contact</th>
                    <th>Nature</th>
                    <th>Subject & Message</th>
                    <th>Status</th>
                    <th class="text-end" style="width: 140px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inquiries as $inquiry)
                    <tr>
                        <td class="small text-muted">
                            <div>{{ $inquiry->created_at->format('d M Y') }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $inquiry->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $inquiry->name }}</div>
                            <div class="small text-muted">
                                <a href="tel:{{ $inquiry->phone }}" class="text-decoration-none text-muted">
                                    <i class="bi bi-telephone me-1"></i> {{ $inquiry->phone }}
                                </a>
                                @if($inquiry->email)
                                    <br>
                                    <a href="mailto:{{ $inquiry->email }}" class="text-decoration-none text-muted">
                                        <i class="bi bi-envelope me-1"></i> {{ $inquiry->email }}
                                    </a>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($inquiry->inquiry_type === 'bulk_buyer')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <i class="bi bi-building me-1"></i> Bulk Buyer
                                </span>
                            @elseif($inquiry->inquiry_type === 'farmer')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-person me-1"></i> Farmer
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                    <i class="bi bi-chat-text me-1"></i> General
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($inquiry->subject)
                                <div class="fw-semibold text-dark small mb-1">{{ $inquiry->subject }}</div>
                            @endif
                            <div class="small text-muted text-break" style="max-width: 480px;">
                                {{ $inquiry->message }}
                            </div>
                        </td>
                        <td>
                            @if($inquiry->isNew())
                                <span class="badge bg-danger">New</span>
                            @else
                                <span class="badge bg-success-subtle text-success">Responded</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form action="{{ route('website.admin.inquiries.toggle', $inquiry) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                @if($inquiry->isNew())
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as Responded">
                                        <i class="bi bi-check2"></i> Mark Done
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mark as New">
                                        <i class="bi bi-arrow-counterclockwise"></i> Re-open
                                    </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                            No inquiries match the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($inquiries->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $inquiries->links() }}
        </div>
    @endif
</div>
@endsection
