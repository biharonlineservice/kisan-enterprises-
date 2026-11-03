<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'all');

        $query = User::with('role')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('mobile', 'like', $term)
                    ->orWhere('designation', 'like', $term));
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role_id', $request->role));

        if ($tab === 'active') {
            $query->where('is_active', true);
        } elseif ($tab === 'inactive') {
            $query->where('is_active', false);
        } elseif ($tab === 'admins') {
            $query->whereHas('role', fn ($r) => $r->where('name', Role::ADMIN));
        } elseif ($tab === 'temp_pwd') {
            $query->where('must_change_password', true);
        } elseif ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $metrics = [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'inactiveUsers' => User::where('is_active', false)->count(),
            'adminUsers' => User::whereHas('role', fn ($r) => $r->where('name', Role::ADMIN))->count(),
            'tempPasswordUsers' => User::where('must_change_password', true)->count(),
            'recentLoginUsers' => User::where('last_login_at', '>=', now()->subDays(7))->count(),
        ];

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('id')->get(),
            'metrics' => $metrics,
            'tab' => $tab,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $tab = $request->input('tab', 'all');

        $query = User::with('role')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('mobile', 'like', $term)
                    ->orWhere('designation', 'like', $term));
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role_id', $request->role));

        if ($tab === 'active') {
            $query->where('is_active', true);
        } elseif ($tab === 'inactive') {
            $query->where('is_active', false);
        } elseif ($tab === 'admins') {
            $query->whereHas('role', fn ($r) => $r->where('name', Role::ADMIN));
        } elseif ($tab === 'temp_pwd') {
            $query->where('must_change_password', true);
        } elseif ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('name')->get();
        $filename = 'users-staff-master-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, [
                'ID',
                'Full Name',
                'Mobile Number',
                'Email Address',
                'System Role',
                'Designation',
                'Account Status',
                'Password Status',
                'Last Login Timestamp',
                'Account Created Date',
            ]);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->mobile,
                    $user->email,
                    $user->role?->display_name ?? 'No Role',
                    $user->designation ?? '',
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->must_change_password ? 'Must Change Password' : 'Password Configured',
                    $user->last_login_at ? $user->last_login_at->toDateTimeString() : 'Never Logged In',
                    $user->created_at ? $user->created_at->toDateString() : '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(['is_active' => true]), 'roles' => Role::orderBy('id')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $user = DB::transaction(function () use ($data, $request) {
            $user = User::create([
                ...$data,
                'is_active' => $request->boolean('is_active'),
                'must_change_password' => true,
                'created_by' => $request->user()->id,
            ]);
            AuditLogger::log('user.created', $user, [], [
                'name' => $user->name, 'email' => $user->email, 'role' => $user->role->display_name,
            ]);

            return $user;
        });

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} created. They must change the temporary password at first sign-in.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user, 'roles' => Role::orderBy('id')->get()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        $isActive = $request->boolean('is_active');

        $this->guardLastAdmin($request, $user, (int) $data['role_id'], $isActive);

        DB::transaction(function () use ($user, $data, $isActive) {
            $original = $user->getOriginal();
            $payload = collect($data)->except('password')->all();
            $payload['is_active'] = $isActive;
            if (! empty($data['password'])) {
                $payload['password'] = $data['password'];
                $payload['must_change_password'] = true;
            }
            $user->update($payload);
            AuditLogger::changes('user.updated', $user, $original);
        });

        return redirect()->route('users.index')->with('success', "User {$user->name} updated.");
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }
        $this->guardLastAdmin($request, $user, $user->role_id, ! $user->is_active);

        $user->update(['is_active' => ! $user->is_active]);
        AuditLogger::log($user->is_active ? 'user.activated' : 'user.deactivated', $user);

        return back()->with('success', "User {$user->name} ".($user->is_active ? 'activated' : 'deactivated').'.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $rawMobile = trim((string) $request->input('mobile', $user?->mobile ?? ''));
        $digits = preg_replace('/\D+/', '', $rawMobile) ?? '';
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        $emailInput = trim((string) $request->input('email', ''));
        if ($emailInput === '' && preg_match('/^[6-9]\d{9}$/', $digits)) {
            $emailInput = $user?->email ?: ($digits.'@kisan.local');
        }

        $request->merge([
            'mobile' => $digits !== '' ? $digits : $rawMobile,
            'email' => $emailInput,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'regex:/^[6-9]\d{9}$/', Rule::unique('users', 'mobile')->ignore($user?->id)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'designation' => ['nullable', 'string', 'max:100'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ], [
            'mobile.required' => 'A 10-digit mobile number is required for staff sign-in.',
            'mobile.regex' => 'Enter a valid 10-digit Indian mobile number.',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    /** Never allow the system to end up with zero active Admin accounts. */
    private function guardLastAdmin(Request $request, User $user, int $newRoleId, bool $willBeActive): void
    {
        $adminRoleId = Role::where('name', Role::ADMIN)->value('id');
        $isCurrentlyActiveAdmin = $user->role_id === $adminRoleId && $user->is_active;
        $staysActiveAdmin = $newRoleId === $adminRoleId && $willBeActive;

        if ($isCurrentlyActiveAdmin && ! $staysActiveAdmin) {
            $otherAdmins = User::where('role_id', $adminRoleId)->where('is_active', true)->where('id', '!=', $user->id)->count();
            if ($otherAdmins === 0) {
                abort(redirect()->back()->withInput()->with('error', 'At least one active Admin account is required.'));
            }
        }
    }
}
