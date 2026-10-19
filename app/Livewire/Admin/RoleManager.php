<?php

namespace App\Livewire\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.app')]
class RoleManager extends Component
{
    use WithPagination;

    public string $activeTab = 'roles'; // 'roles', 'matrix', 'permissions', 'users'

    public string $flashMessage = '';

    public string $flashType = 'success';

    // Role state
    public string $roleSearch = '';

    public bool $showRoleModal = false;

    public ?int $roleIdBeingEdited = null;

    public string $roleName = '';

    // Permission state
    public string $permissionSearch = '';

    public string $permissionGroupFilter = '';

    public bool $showPermissionModal = false;

    public string $permissionName = '';

    public string $permissionGroup = '';

    // Matrix state
    public ?int $selectedRoleId = null;

    public array $selectedPermissions = [];

    public string $matrixSearch = '';

    // User management state
    public string $userSearch = '';

    public string $userRoleFilter = '';

    protected function rules(): array
    {
        return [
            'roleName' => ['required', 'string', 'min:2', 'max:50'],
            'permissionName' => ['required', 'string', 'min:3', 'max:100'],
            'permissionGroup' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function mount(): void
    {
        $firstRole = Role::first();
        if ($firstRole) {
            $this->selectRoleForMatrix($firstRole->id);
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // --- Role Management ---

    public function openCreateRoleModal(): void
    {
        $this->resetValidation();
        $this->roleIdBeingEdited = null;
        $this->roleName = '';
        $this->showRoleModal = true;
    }

    public function openEditRoleModal(int $id): void
    {
        $this->resetValidation();
        $role = Role::findOrFail($id);
        $this->roleIdBeingEdited = $role->id;
        $this->roleName = $role->name;
        $this->showRoleModal = true;
    }

    public function saveRole(): void
    {
        $this->validate([
            'roleName' => [
                'required',
                'string',
                'min:2',
                'max:50',
                Rule::unique('roles', 'name')->ignore($this->roleIdBeingEdited),
            ],
        ]);

        $normalizedName = strtolower(trim($this->roleName));

        if ($this->roleIdBeingEdited) {
            $role = Role::findOrFail($this->roleIdBeingEdited);
            $oldName = $role->name;
            $role->update(['name' => $normalizedName]);

            // Sync legacy role column on affected users
            User::where('role_id', $role->id)->update(['role' => $normalizedName]);

            $this->notify("Role '{$normalizedName}' updated successfully.");
        } else {
            $role = Role::create([
                'name' => $normalizedName,
                'guard_name' => 'web',
            ]);

            $this->notify("Role '{$role->name}' created successfully.");
        }

        $this->showRoleModal = false;
        $this->roleName = '';
        $this->roleIdBeingEdited = null;
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function deleteRole(int $id): void
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'admin') {
            $this->notify("The 'admin' role cannot be deleted.", 'error');

            return;
        }

        $assignedUsersCount = User::where('role_id', $role->id)->count();
        if ($assignedUsersCount > 0) {
            $this->notify("Cannot delete role '{$role->name}' because it is assigned to {$assignedUsersCount} user(s).", 'error');

            return;
        }

        $role->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        if ($this->selectedRoleId === $id) {
            $this->selectedRoleId = Role::first()?->id;
            if ($this->selectedRoleId) {
                $this->selectRoleForMatrix($this->selectedRoleId);
            }
        }

        $this->notify('Role deleted successfully.');
    }

    // --- Role Permissions Matrix ---

    public function selectRoleForMatrix(int $roleId): void
    {
        $this->selectedRoleId = $roleId;
        $role = Role::find($roleId);
        if ($role) {
            $this->selectedPermissions = $role->permissions()->pluck('name')->toArray();
        } else {
            $this->selectedPermissions = [];
        }
    }

    public function togglePermission(string $permissionName): void
    {
        if (in_array($permissionName, $this->selectedPermissions)) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, [$permissionName]));
        } else {
            $this->selectedPermissions[] = $permissionName;
        }

        $this->saveRolePermissions();
    }

    public function toggleGroup(string $groupName): void
    {
        if (! $this->selectedRoleId) {
            return;
        }

        $groupPermissions = Permission::all()
            ->filter(fn (Permission $p) => $p->group_display === $groupName)
            ->pluck('name')
            ->toArray();

        $allChecked = count(array_intersect($groupPermissions, $this->selectedPermissions)) === count($groupPermissions);

        if ($allChecked) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $groupPermissions));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $groupPermissions)));
        }

        $this->saveRolePermissions();
    }

    public function saveRolePermissions(): void
    {
        if (! $this->selectedRoleId) {
            return;
        }

        $role = Role::findOrFail($this->selectedRoleId);
        $role->syncPermissions($this->selectedPermissions);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->notify("Permissions for role '{$role->name}' updated.");
    }

    // --- Permission Management ---

    public function openCreatePermissionModal(): void
    {
        $this->resetValidation();
        $this->permissionName = '';
        $this->permissionGroup = '';
        $this->showPermissionModal = true;
    }

    public function savePermission(): void
    {
        $this->validate([
            'permissionName' => [
                'required',
                'string',
                'min:3',
                'max:100',
                Rule::unique('permissions', 'name'),
            ],
            'permissionGroup' => ['nullable', 'string', 'max:50'],
        ]);

        $normalizedName = strtolower(trim($this->permissionName));
        $group = ! empty($this->permissionGroup) ? trim($this->permissionGroup) : null;

        Permission::create([
            'name' => $normalizedName,
            'guard_name' => 'web',
            'group_name' => $group,
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->showPermissionModal = false;
        $this->permissionName = '';
        $this->permissionGroup = '';
        $this->notify("Permission '{$normalizedName}' created successfully.");
    }

    public function deletePermission(int $id): void
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Refresh selected permissions array if open in matrix
        $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, [$permission->name]));

        $this->notify('Permission deleted successfully.');
    }

    // --- User Roles Assignment ---

    public function updateUserRole(int $userId, ?int $roleId): void
    {
        $user = User::findOrFail($userId);
        $user->role_id = $roleId ?: null;
        $user->save(); // Triggers booted observer to sync Spatie roles and string role column

        $roleName = $user->role_id ? Role::find($user->role_id)?->name : 'No role';
        $this->notify("User '{$user->name}' role updated to '{$roleName}'.");
    }

    // --- Utilities ---

    public function notify(string $message, string $type = 'success'): void
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
    }

    public function clearNotification(): void
    {
        $this->flashMessage = '';
    }

    public function render(): View
    {
        $rolesQuery = Role::withCount('assignedUsers', 'permissions')
            ->when($this->roleSearch, fn ($q) => $q->where('name', 'like', "%{$this->roleSearch}%"));

        $roles = $rolesQuery->get();

        $allPermissions = Permission::when($this->permissionSearch, fn ($q) => $q->where('name', 'like', "%{$this->permissionSearch}%"))
            ->when($this->permissionGroupFilter, fn ($q) => $q->where('group_name', $this->permissionGroupFilter))
            ->get();

        $groupedPermissions = Permission::when($this->matrixSearch, fn ($q) => $q->where('name', 'like', "%{$this->matrixSearch}%"))
            ->get()
            ->groupBy(fn (Permission $p) => $p->group_display);

        $availableGroups = Permission::whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name')
            ->toArray();

        $users = User::with('currentRole')
            ->when($this->userSearch, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->userSearch}%")
                        ->orWhere('email', 'like', "%{$this->userSearch}%");
                });
            })
            ->when($this->userRoleFilter, function ($q) {
                if ($this->userRoleFilter === 'none') {
                    $q->whereNull('role_id');
                } else {
                    $q->where('role_id', $this->userRoleFilter);
                }
            })
            ->paginate(15);

        return view('livewire.admin.role-manager', [
            'roles' => $roles,
            'allRoles' => Role::all(),
            'allPermissions' => $allPermissions,
            'groupedPermissions' => $groupedPermissions,
            'availableGroups' => $availableGroups,
            'users' => $users,
            'selectedRole' => $this->selectedRoleId ? Role::find($this->selectedRoleId) : null,
        ]);
    }
}
