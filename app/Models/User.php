<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'role_ids',
        'active_permissions',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role_ids' => 'array',
        'active_permissions' => 'array',
    ];

    public function hasPermission($permission)
    {
        $roleIds = $this->role_ids ?? [];
        if (in_array('admin', $roleIds)) {
            return true;
        }

        $rolesFile = storage_path('app/roles.json');
        if (!file_exists($rolesFile)) {
            return false;
        }

        $data = json_decode(file_get_contents($rolesFile), true);
        if (!$data || !isset($data['roles'])) {
            return false;
        }

        foreach ($data['roles'] as $role) {
            if (in_array($role['id'], $roleIds)) {
                $perms = $role['permissionIds'] ?? [];
                if (in_array('all', $perms) || in_array('view_all', $perms) || in_array('edit_all', $perms) || in_array($permission, $perms)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getPermissionIds()
    {
        $roleIds = $this->role_ids ?? [];
        if (in_array('admin', $roleIds)) {
            return ['all']; // Super admin has everything
        }

        $rolesFile = storage_path('app/roles.json');
        if (!file_exists($rolesFile)) {
            return [];
        }

        $data = json_decode(file_get_contents($rolesFile), true);
        if (!$data || !isset($data['roles'])) {
            return [];
        }

        $allPerms = [];
        foreach ($data['roles'] as $role) {
            if (in_array($role['id'], $roleIds)) {
                $rolePerms = $role['permissionIds'] ?? [];
                $allPerms = array_merge($allPerms, $rolePerms);
            }
        }

        return array_values(array_unique($allPerms));
    }
}
