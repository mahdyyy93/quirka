<div class="py-8">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header & Notification --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Roles & Permissions</h1>
                <p class="text-sm text-gray-500 mt-1">Manage dynamic system roles, define modular permissions, and assign access control.</p>
            </div>

            {{-- Flash Alert --}}
            @if($flashMessage)
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium shadow-sm transition-all {{ $flashType === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($flashType === 'error')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @endif
                    </svg>
                    <span>{{ $flashMessage }}</span>
                    <button wire:click="clearNotification" class="ms-auto text-gray-400 hover:text-gray-600">&times;</button>
                </div>
            @endif
        </div>

        {{-- Modern Tab Navigation --}}
        <div class="border-b border-gray-200 mb-8">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button
                    wire:click="setTab('roles')"
                    class="py-4 px-1 inline-flex items-center gap-2 border-b-2 font-semibold text-sm transition-colors {{ $activeTab === 'roles' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Roles ({{ count($roles) }})</span>
                </button>

                <button
                    wire:click="setTab('matrix')"
                    class="py-4 px-1 inline-flex items-center gap-2 border-b-2 font-semibold text-sm transition-colors {{ $activeTab === 'matrix' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Role Permissions Matrix</span>
                </button>

                <button
                    wire:click="setTab('permissions')"
                    class="py-4 px-1 inline-flex items-center gap-2 border-b-2 font-semibold text-sm transition-colors {{ $activeTab === 'permissions' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <span>Permissions ({{ count($allPermissions) }})</span>
                </button>

                <button
                    wire:click="setTab('users')"
                    class="py-4 px-1 inline-flex items-center gap-2 border-b-2 font-semibold text-sm transition-colors {{ $activeTab === 'users' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>User Roles</span>
                </button>
            </nav>
        </div>

        {{-- ==================== TAB 1: ROLES LIST ==================== --}}
        @if($activeTab === 'roles')
            <div class="bg-white rounded-none border border-stone-300 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="w-full sm:max-w-md">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="roleSearch"
                            placeholder="Search roles by name..."
                            class="border border-stone-300 rounded-none px-4 py-2.5 text-sm w-full focus:ring-1 focus:ring-stone-800 focus:border-stone-800 focus:outline-none"
                        >
                    </div>

                    <button
                        wire:click="openCreateRoleModal"
                        class="bg-[#222522] hover:bg-[#753D45] text-white text-sm font-semibold px-4 py-2.5 rounded-none shadow-sm transition-all shrink-0 inline-flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Add New Role</span>
                    </button>
                </div>

                <div class="overflow-x-auto border border-stone-300 rounded-none">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-stone-100 text-stone-700 uppercase text-xs tracking-wider border-b border-stone-300">
                            <tr>
                                <th class="px-6 py-3.5 font-semibold">Role Name</th>
                                <th class="px-6 py-3.5 font-semibold">Assigned Users</th>
                                <th class="px-6 py-3.5 font-semibold">Assigned Permissions</th>
                                <th class="px-6 py-3.5 font-semibold">Guard</th>
                                <th class="px-6 py-3.5 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200 text-sm text-stone-800 bg-white">
                            @forelse($roles as $role)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900 flex items-center gap-2.5">
                                        <span class="w-2.5 h-2.5 rounded-full {{ $role->name === 'admin' ? 'bg-amber-500' : 'bg-blue-600' }}"></span>
                                        <span>{{ ucfirst($role->name) }}</span>
                                        @if($role->name === 'admin')
                                            <span class="bg-amber-100 text-amber-800 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full">Super Admin</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            {{ $role->assigned_users_count }} users
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                            {{ $role->permissions_count }} permissions
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500 font-mono">
                                        {{ $role->guard_name }}
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <button
                                            wire:click="selectRoleForMatrix({{ $role->id }}); setTab('matrix')"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg transition-colors"
                                            title="Configure Permissions"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>Permissions</span>
                                        </button>

                                        <button
                                            wire:click="openEditRoleModal({{ $role->id }})"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg transition-colors"
                                        >
                                            Edit
                                        </button>

                                        @if($role->name !== 'admin')
                                            <button
                                                wire:click="deleteRole({{ $role->id }})"
                                                wire:confirm="Are you sure you want to delete role '{{ $role->name }}'?"
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors"
                                            >
                                                Delete
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                        No roles found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ==================== TAB 2: ROLE PERMISSION MATRIX ==================== --}}
        @if($activeTab === 'matrix')
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                {{-- Role Selection Bar --}}
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 pb-6 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-semibold text-gray-700">Select Role to Configure:</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allRoles as $role)
                                <button
                                    type="button"
                                    wire:click="selectRoleForMatrix({{ $role->id }})"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $selectedRoleId === $role->id ? 'bg-gray-900 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                >
                                    {{ ucfirst($role->name) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="w-full lg:max-w-xs">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="matrixSearch"
                            placeholder="Filter permissions..."
                            class="border border-gray-200 rounded-xl px-4 py-2 text-sm w-full focus:ring-2 focus:ring-gray-900 focus:outline-none"
                        >
                    </div>
                </div>

                @if($selectedRole)
                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">
                                    Permissions for <span class="text-blue-600">{{ ucfirst($selectedRole->name) }}</span>
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Changes are synchronized instantly. Currently granted {{ count($selectedPermissions) }} permission(s).
                                </p>
                            </div>

                            <button
                                wire:click="saveRolePermissions"
                                class="bg-gray-900 text-white text-xs font-semibold px-4 py-2 rounded-xl shadow-sm hover:bg-gray-800 transition-all inline-flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Save Changes</span>
                            </button>
                        </div>

                        {{-- Grouped Permissions Grid --}}
                        <div class="space-y-6">
                            @forelse($groupedPermissions as $groupName => $permissions)
                                <div class="bg-gray-50/80 rounded-xl border border-gray-200/70 p-5">
                                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-200">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-gray-900"></span>
                                            <h4 class="font-bold text-gray-900 text-sm tracking-wide">{{ $groupName }}</h4>
                                            <span class="text-xs text-gray-400">({{ count($permissions) }} permissions)</span>
                                        </div>

                                        <button
                                            type="button"
                                            wire:click="toggleGroup('{{ $groupName }}')"
                                            class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors"
                                        >
                                            Toggle All in {{ $groupName }}
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($permissions as $permission)
                                            @php
                                                $isChecked = in_array($permission->name, $selectedPermissions);
                                            @endphp
                                            <label class="flex items-start gap-3 p-3 rounded-lg border transition-all cursor-pointer {{ $isChecked ? 'bg-white border-blue-200 shadow-xs' : 'bg-white/60 border-gray-200/80 hover:bg-white' }}">
                                                <input
                                                    type="checkbox"
                                                    value="{{ $permission->name }}"
                                                    wire:click="togglePermission('{{ $permission->name }}')"
                                                    @checked($isChecked)
                                                    class="mt-0.5 rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                                                >
                                                <div class="text-xs">
                                                    <span class="font-medium text-gray-900 block font-mono">{{ $permission->name }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-400">
                                    No permissions match your search filter.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center text-gray-400">
                        Please select a role above to configure its permissions.
                    </div>
                @endif
            </div>
        @endif

        {{-- ==================== TAB 3: PERMISSIONS LIST ==================== --}}
        @if($activeTab === 'permissions')
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:max-w-xl">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="permissionSearch"
                            placeholder="Search permissions by name..."
                            class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm w-full focus:ring-2 focus:ring-gray-900 focus:outline-none"
                        >

                        <select
                            wire:model.live="permissionGroupFilter"
                            class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-gray-900 focus:outline-none"
                        >
                            <option value="">All Groups</option>
                            @foreach($availableGroups as $grp)
                                <option value="{{ $grp }}">{{ ucfirst($grp) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            wire:click="syncRoutes"
                            wire:loading.attr="disabled"
                            class="bg-white border border-gray-300 text-gray-700 text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition-all shrink-0 inline-flex items-center gap-2"
                            title="Scan application routes and automatically create any missing permissions"
                        >
                            <svg wire:loading.remove wire:target="syncRoutes" class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <svg wire:loading wire:target="syncRoutes" class="animate-spin w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="syncRoutes">Sync from Routes</span>
                            <span wire:loading wire:target="syncRoutes">Syncing...</span>
                        </button>

                        <button
                            wire:click="openCreatePermissionModal"
                            class="bg-[#222522] hover:bg-[#753D45] text-white text-sm font-semibold px-4 py-2.5 rounded-none shadow-sm transition-all shrink-0 inline-flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Add New Permission</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto border border-stone-300 rounded-none">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-stone-100 text-stone-700 uppercase text-xs tracking-wider border-b border-stone-300">
                            <tr>
                                <th class="px-6 py-3.5 font-semibold">Permission Key</th>
                                <th class="px-6 py-3.5 font-semibold">Group / Module</th>
                                <th class="px-6 py-3.5 font-semibold">Assigned Roles</th>
                                <th class="px-6 py-3.5 font-semibold">Guard</th>
                                <th class="px-6 py-3.5 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200 text-sm text-stone-800 bg-white">
                            @forelse($allPermissions as $perm)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-900">
                                        {{ $perm->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            {{ $perm->group_display }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($perm->roles as $r)
                                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700">
                                                    {{ $r->name }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-gray-400">Unassigned</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500 font-mono">
                                        {{ $perm->guard_name }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button
                                            wire:click="deletePermission({{ $perm->id }})"
                                            wire:confirm="Are you sure you want to delete permission '{{ $perm->name }}'?"
                                            class="text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                        No permissions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ==================== TAB 4: USER ROLES ASSIGNMENT ==================== --}}
        @if($activeTab === 'users')
            <div class="bg-white rounded-none border border-stone-300 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:max-w-xl">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="userSearch"
                            placeholder="Search users by name or email..."
                            class="border border-stone-300 rounded-none px-4 py-2.5 text-sm w-full focus:ring-1 focus:ring-stone-800 focus:border-stone-800 focus:outline-none"
                        >

                        <select
                            wire:model.live="userRoleFilter"
                            class="border border-stone-300 rounded-none px-4 py-2.5 text-sm bg-white focus:ring-1 focus:ring-stone-800 focus:outline-none"
                        >
                            <option value="">All Roles</option>
                            @foreach($allRoles as $r)
                                <option value="{{ $r->id }}">{{ ucfirst($r->name) }}</option>
                            @endforeach
                            <option value="none">No Role</option>
                        </select>
                    </div>

                    <p class="text-xs text-gray-500">Each user has exactly one primary role (role_id).</p>
                </div>

                <div class="overflow-x-auto border border-stone-300 rounded-none">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-stone-100 text-stone-700 uppercase text-xs tracking-wider border-b border-stone-300">
                            <tr>
                                <th class="px-6 py-3.5 font-semibold">User</th>
                                <th class="px-6 py-3.5 font-semibold">Email</th>
                                <th class="px-6 py-3.5 font-semibold">Current Role</th>
                                <th class="px-6 py-3.5 font-semibold text-right">Assign Single Role</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200 text-sm text-stone-800 bg-white">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-900 text-white flex items-center justify-center font-bold text-xs uppercase">
                                            {{ substr($user->name, 0, 2) }}
                                        </div>
                                        <span>{{ $user->name }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 text-xs">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($user->currentRole)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $user->currentRole->name === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700' }}">
                                                {{ ucfirst($user->currentRole->name) }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">None</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <select
                                            wire:change="updateUserRole({{ $user->id }}, $event.target.value)"
                                            class="text-xs font-semibold border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-gray-900 focus:outline-none"
                                        >
                                            <option value="" @selected(empty($user->role_id))>-- Select Role --</option>
                                            @foreach($allRoles as $role)
                                                <option value="{{ $role->id }}" @selected($user->role_id == $role->id)>
                                                    {{ ucfirst($role->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                        No users found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        @endif

    </div>

    {{-- ==================== MODAL: ADD / EDIT ROLE ==================== --}}
    @if($showRoleModal)
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl p-6 sm:p-8 w-full max-w-md shadow-2xl border border-gray-100">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $roleIdBeingEdited ? 'Edit Role' : 'Create New Role' }}
                    </h3>
                    <button wire:click="$set('showRoleModal', false)" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                </div>

                <form wire:submit="saveRole" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Role Name</label>
                        <input
                            type="text"
                            wire:model="roleName"
                            placeholder="e.g. supervisor, editor"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-gray-900 focus:outline-none"
                            required
                        >
                        @error('roleName')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button
                            type="button"
                            wire:click="$set('showRoleModal', false)"
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-gray-200 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="bg-gray-900 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors shadow-sm"
                        >
                            {{ $roleIdBeingEdited ? 'Update Role' : 'Create Role' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ==================== MODAL: ADD DYNAMIC PERMISSION ==================== --}}
    @if($showPermissionModal)
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl p-6 sm:p-8 w-full max-w-md shadow-2xl border border-gray-100">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-gray-900">Create Dynamic Permission</h3>
                    <button wire:click="$set('showPermissionModal', false)" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                </div>

                <form wire:submit="savePermission" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Permission Key</label>
                        <input
                            type="text"
                            wire:model="permissionName"
                            placeholder="e.g. reports.export, discounts.manage"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:ring-2 focus:ring-gray-900 focus:outline-none"
                            required
                        >
                        @error('permissionName')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-[11px] text-gray-400 mt-1">Recommended format: module.action (e.g. products.export)</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-1">Group / Module (Optional)</label>
                        <input
                            type="text"
                            wire:model="permissionGroup"
                            placeholder="e.g. Reports, Marketing"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-gray-900 focus:outline-none"
                        >
                        @error('permissionGroup')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button
                            type="button"
                            wire:click="$set('showPermissionModal', false)"
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-gray-200 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="bg-gray-900 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors shadow-sm"
                        >
                            Save Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
