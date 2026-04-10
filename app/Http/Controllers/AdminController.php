<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function usersIndex()
    {
        return Inertia::render('Admin/Users', [
            'users' => User::all()
        ]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|unique:users,username',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'username' => $data['username'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_ids' => [$data['role']],
        ]);

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Create User',
            'entity_type' => 'Admin',
            'description' => json_encode(['email' => $data['email'], 'role' => $data['role']]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'username' => 'required|string|unique:users,username,' . $user->id,
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|string',
            'password' => 'nullable|string|min:6'
        ]);

        $updateData = [
            'username' => $data['username'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'role_ids' => [$data['role']],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Update User',
            'entity_type' => 'Admin',
            'description' => json_encode(['id' => $user->id, 'email' => $user->email]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function destroyUser(User $user)
    {
        $email = $user->email;
        $user->delete();

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Delete User',
            'entity_type' => 'Admin',
            'description' => json_encode(['id' => $user->id]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function auditIndex()
    {
        return Inertia::render('Admin/Audit', [
            'logs' => AuditLog::orderBy('created_at', 'desc')->get()
        ]);
    }
}
