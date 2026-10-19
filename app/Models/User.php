<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            $roleNeedsSync = $user->wasRecentlyCreated || $user->wasChanged('role_id');

            if ($roleNeedsSync) {
                if ($user->role_id) {
                    $role = Role::find($user->role_id);
                    if ($role) {
                        $user->syncRoles([$role]);
                        if ($user->role !== $role->name) {
                            $user->updateQuietly(['role' => $role->name]);
                        }
                    }
                } else {
                    $user->syncRoles([]);
                }
            } elseif ($user->role && ! $user->role_id) {
                $role = Role::where('name', $user->role)->first();
                if ($role) {
                    $user->updateQuietly(['role_id' => $role->id]);
                    $user->syncRoles([$role]);
                }
            }
        });
    }

    public function currentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function assignSingleRole(Role|string|int $role): self
    {
        $roleModel = is_numeric($role)
            ? Role::findById((int) $role)
            : (is_string($role) ? Role::findByName($role) : $role);

        $this->role_id = $roleModel->id;
        $this->role = $roleModel->name;
        $this->saveQuietly();
        $this->syncRoles([$roleModel]);

        return $this;
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }
}
