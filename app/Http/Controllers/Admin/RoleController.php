<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Permission matrix. The Admin role always holds every permission and is not editable,
 * so administrators can never lock themselves out.
 */
class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'overview');

        $roles = Role::with(['permissions:id,key,label,group', 'users' => function ($q) {
            $q->select('id', 'name', 'role_id', 'is_active')->take(6);
        }])->withCount('users')->orderBy('id')->get();

        $permissions = Permission::orderBy('id')->get();
        $groups = $permissions->groupBy('group');

        $metrics = [
            'totalRoles' => Role::count(),
            'totalPermissions' => $permissions->count(),
            'assignedStaff' => User::where('is_active', true)->count(),
            'permissionGroups' => $groups->count(),
        ];

        return view('admin.roles.index', [
            'roles' => $roles,
            'groups' => $groups,
            'metrics' => $metrics,
            'tab' => $tab,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->isAdmin()) {
            return back()->with('error', 'The Admin role always has full access and cannot be edited.');
        }

        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        DB::transaction(function () use ($role, $data) {
            $before = $role->permissions()->pluck('key')->sort()->values()->all();
            $role->permissions()->sync($data['permissions'] ?? []);
            $after = $role->permissions()->pluck('key')->sort()->values()->all();

            AuditLogger::log('role.permissions_updated', $role,
                ['removed' => array_values(array_diff($before, $after))],
                ['added' => array_values(array_diff($after, $before))],
                "Permissions updated for {$role->display_name}");
        });

        return redirect()->route('roles.index', ['tab' => $role->name])->with('success', "Permissions for {$role->display_name} saved.");
    }
}
