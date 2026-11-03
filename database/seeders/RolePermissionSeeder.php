<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Idempotent: syncs the permission catalogue and system roles from config/kisan.php.
 * Safe to run in production — it never deletes users or business data, and it only
 * assigns default permissions to a role when that role is first created.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $keys = [];
            foreach (config('kisan.permissions') as $group => $permissions) {
                foreach ($permissions as $key => $label) {
                    Permission::updateOrCreate(['key' => $key], ['group' => $group, 'label' => $label]);
                    $keys[] = $key;
                }
            }
            // Remove permissions that no longer exist in the catalogue.
            Permission::whereNotIn('key', $keys)->delete();

            foreach (config('kisan.roles') as $name => $def) {
                $role = Role::firstOrNew(['name' => $name]);
                $isNew = ! $role->exists;
                $role->fill([
                    'display_name' => $def['display_name'],
                    'description' => $def['description'],
                    'is_system' => true,
                ])->save();

                if ($name === Role::ADMIN) {
                    $role->permissions()->sync(Permission::pluck('id'));
                } elseif ($isNew) {
                    $role->permissions()->sync(Permission::whereIn('key', $def['permissions'])->pluck('id'));
                }
            }
        });
    }
}
