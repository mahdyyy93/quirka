<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'group_name',
    ];

    public function getGroupDisplayAttribute(): string
    {
        if (! empty($this->group_name)) {
            return ucfirst($this->group_name);
        }

        if (str_contains($this->name, '.')) {
            return ucfirst(explode('.', $this->name)[0]);
        }

        return 'General';
    }
}
