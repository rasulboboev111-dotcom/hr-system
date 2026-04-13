<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function usersIndex(Request $request)
    {
        if (!$request->user()->hasPermission('view_users')) {
            abort(403, 'Шумо ҳуқуқи дидани корбаронро надоред.');
        }

        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'ilike', "%{$search}%")
                  ->orWhere('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $rolesFile = storage_path('app/roles.json');
        $parsed = file_exists($rolesFile) ? json_decode(file_get_contents($rolesFile), true) : null;
        $rolesData = $parsed ?: ['roles' => [], 'permissions' => []];

        return Inertia::render('Admin/Users', [
            'users' => $query->get(),
            'filters' => $request->only('search'),
            'rolesData' => $rolesData
        ]);
    }

    public function storeRole(Request $request)
    {
        if (!$request->user()->hasPermission('manage_roles')) {
            abort(403, 'Шумо ҳуқуқи идораи нақшҳоро надоред.');
        }
        $data = $request->validate([
            'id' => 'required|string',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'permissionIds' => 'array'
        ]);

        $rolesFile = storage_path('app/roles.json');
        $rolesData = file_exists($rolesFile) ? json_decode(file_get_contents($rolesFile), true) : ['roles' => [], 'permissions' => []];
        
        // Check if role exists, if so update, otherwise append
        $found = false;
        foreach ($rolesData['roles'] as &$role) {
            if ($role['id'] === $data['id']) {
                $role = $data;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $rolesData['roles'][] = $data;
        }

        file_put_contents($rolesFile, json_encode($rolesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        \Illuminate\Support\Facades\Cache::forget('roles_json_data');

        return redirect()->back();
    }

    public function destroyRole(Request $request, $id)
    {
        if (!$request->user()->hasPermission('manage_roles')) {
            abort(403, 'Шумо ҳуқуқи идораи нақшҳоро надоред.');
        }
        
        if ($id === 'admin') {
            return redirect()->back()->withErrors(['error' => 'Cannot delete admin role.']);
        }

        $rolesFile = storage_path('app/roles.json');
        if (!file_exists($rolesFile)) return redirect()->back();
        
        $rolesData = json_decode(file_get_contents($rolesFile), true);
        $rolesData['roles'] = array_values(array_filter($rolesData['roles'], function($r) use ($id) {
            return $r['id'] !== $id;
        }));

        file_put_contents($rolesFile, json_encode($rolesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        \Illuminate\Support\Facades\Cache::forget('roles_json_data');

        return redirect()->back();
    }

    public function storePermission(Request $request)
    {
        if (!$request->user()->hasPermission('manage_roles')) {
            abort(403, 'Шумо ҳуқуқи идораи нақшҳоро надоред.');
        }

        $data = $request->validate([
            'id' => 'required|string',
            'name' => 'required|string',
            'category' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        $rolesFile = storage_path('app/roles.json');
        $rolesData = file_exists($rolesFile) ? json_decode(file_get_contents($rolesFile), true) : ['roles' => [], 'permissions' => []];

        // Check for duplicate permission id
        foreach ($rolesData['permissions'] as $perm) {
            if ($perm['id'] === $data['id']) {
                return redirect()->back()->withErrors(['error' => 'Ин ҳуқуқ аллакай мавҷуд аст.']);
            }
        }

        $rolesData['permissions'][] = $data;
        file_put_contents($rolesFile, json_encode($rolesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        \Illuminate\Support\Facades\Cache::forget('roles_json_data');

        return redirect()->back();
    }

    public function storeUser(Request $request)
    {
        if (!$request->user()->hasPermission('add_users')) {
            abort(403, 'Шумо ҳуқуқи иловаи корбаронро надоред.');
        }

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
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Create User',
            'entity_type' => 'Admin',
            'description' => json_encode(['email' => $data['email'], 'role' => $data['role']]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function updateUser(Request $request, User $user)
    {
        if (!$request->user()->hasPermission('edit_users')) {
            abort(403, 'Шумо ҳуқуқи таҳрири корбаронро надоред.');
        }

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
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Update User',
            'entity_type' => 'Admin',
            'description' => json_encode(['id' => $user->id, 'email' => $user->email]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function destroyUser(Request $request, User $user)
    {
        if (!$request->user()->hasPermission('delete_users')) {
            abort(403, 'Шумо ҳуқуқи нест кардани корбаронро надоред.');
        }
        
        $email = $user->email;
        $user->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Delete User',
            'entity_type' => 'Admin',
            'description' => json_encode(['id' => $user->id]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function exportCsv(Request $request)
    {
        if (!$request->user()->hasPermission('export_users')) {
            abort(403, 'Шумо ҳуқуқи экспорти корбаронро надоред.');
        }

        $users = User::all();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Export Users',
            'entity_type' => 'Admin',
            'description' => 'Exported users data to CSV',
            'timestamp' => now()->toIso8601String()
        ]);

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['ID', 'Логин', 'Ном', 'Насаб', 'Почтаи электронӣ', 'Нақш', 'Рамз'], ';');
            foreach ($users as $u) {
                fputcsv($file, [
                    $u->id,
                    $u->username,
                    $u->first_name,
                    $u->last_name,
                    $u->email,
                    implode(',', $u->role_ids ?? []),
                    ''
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="users_export.csv"',
        ]);
    }

    public function importCsv(Request $request)
    {
        if (!$request->user()->hasPermission('add_users')) {
            abort(403, 'Шумо ҳуқуқи иловаи корбаронро надоред.');
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:4096'
        ]);

        $path = $request->file('file')->getRealPath();
        $content = file_get_contents($path);

        // Detect and convert encoding from Windows-1251 to UTF-8 if needed
        if (!mb_check_encoding($content, 'UTF-8')) {
            $converted = @mb_convert_encoding($content, 'UTF-8', 'Windows-1251');
            if (mb_check_encoding($converted, 'UTF-8')) {
                $content = $converted;
                \Log::info("User CSV encoding converted from Windows-1251 to UTF-8");
            }
        }

        // Remove UTF-8 BOM if it exists
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Create a temporary stream for fgetcsv
        $file = fopen('php://temp', 'r+');
        fwrite($file, $content);
        rewind($file);

        if (!$file) {
            return redirect()->back()->withErrors(['file' => 'Failed to process file.']);
        }

        // Detect delimiter from the first line
        $firstLine = fgets($file);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        rewind($file);

        // Skip header
        fgetcsv($file, 0, $delimiter);

        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            // Clean and trim with security sanitization
            $cleanRow = array_map(function($val) {
                if ($val === null) return '';
                
                // 1. Strip HTML tags to prevent XSS
                $val = strip_tags($val);
                
                // 2. Clean weird characters and normalize spaces
                $val = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $val);
                $val = trim(preg_replace('/\s+/', ' ', $val));
                
                // 3. Prevent Formula Injection (prepend ' if starts with = + - @)
                if ($val !== '' && in_array($val[0], ['=', '+', '-', '@'])) {
                    $val = "'" . $val;
                }
                
                return $val;
            }, $row);

            if (count($cleanRow) < 5 || empty($cleanRow[1])) {
                continue;
            }
            
            $username = $cleanRow[1];
            $firstName = $cleanRow[2];
            $lastName = $cleanRow[3];
            $email = $cleanRow[4];
            $roleStr = $cleanRow[5] ?? '';
            $roleIds = array_filter(array_map('trim', explode(',', $roleStr)));
            if (empty($roleIds)) $roleIds = ['hr_mgr'];
            
            $passwordRaw = $cleanRow[6] ?? '';

            if (!empty($email) && !empty($username)) {
                $user = User::where('email', $email)->orWhere('username', $username)->first();
                if ($user) {
                    $updateData = [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'role_ids' => $roleIds
                    ];
                    if (!empty($passwordRaw)) {
                        $updateData['password'] = Hash::make($passwordRaw);
                    }
                    $user->update($updateData);
                } else {
                    User::create([
                        'username' => $username,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'password' => Hash::make($passwordRaw ?: \Illuminate\Support\Str::random(16)),
                        'role_ids' => $roleIds
                    ]);
                }
            }
        }
        fclose($file);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Import Users',
            'entity_type' => 'Admin',
            'description' => 'Imported users from CSV',
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function auditIndex(Request $request)
    {
        // Require direct admin role or specific permission for audit
        if (!$request->user()->hasPermission('view_audit')) {
            abort(403, 'Шумо ҳуқуқи дидани аудитро надоред.');
        }

        $query = AuditLog::orderBy('timestamp', 'desc');

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            
            // Map common RU/TJ terms to database keywords
            $mapping = [
                'actions' => [
                    'create' => ['илова', 'добав', 'созда', 'new'],
                    'update' => ['нав', 'обнов', 'ред', 'таҳрир', 'edit'],
                    'delete' => ['нест', 'удал', 'тоза', 'destroy'],
                    'import' => ['имп'],
                    'export' => ['эксп'],
                    'login' => ['вор', 'вход'],
                    'logout' => ['хор', 'вых'],
                    'clearaudit' => ['тоза', 'очист']
                ],
                'entities' => [
                    'employee' => ['корм', 'сотр', 'коргар', 'физики'],
                    'position' => ['манс', 'долж', 'вазифа'],
                    'user' => ['ист', 'польз', 'админ'],
                    'payroll' => ['маош', 'зарп', 'пул', 'фонд'],
                    'department' => ['шуъб', 'отдел', 'департамент'],
                    'timesheet' => ['табел', 'тав'],
                    'admin' => ['маъм', 'адм', 'система'],
                    'attendance' => ['давом', 'посещ'],
                    'calendarevent' => ['чора', 'событ', 'ивент'],
                    'shift' => ['баст', 'смен']
                ]
            ];

            $query->where(function($q) use ($search, $mapping) {
                // Personal search (User/Username)
                $q->where('user_name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");

                // Check for mapped actions
                foreach ($mapping['actions'] as $en => $locales) {
                    foreach ($locales as $term) {
                        if (str_contains($search, $term) || str_contains($en, $search)) {
                            $q->orWhere('action', 'ilike', "%{$en}%");
                            break;
                        }
                    }
                }

                // Check for mapped entities (Objects)
                foreach ($mapping['entities'] as $en => $locales) {
                    foreach ($locales as $term) {
                        if (str_contains($search, $term) || str_contains($en, $search)) {
                            $q->orWhere('entity_type', 'ilike', "%{$en}%");
                            break;
                        }
                    }
                }
            });
        }

        $logs = $query->paginate(50)->appends($request->all());
        
        $stats = [
            'totalActions' => AuditLog::count(),
            'todayActions' => AuditLog::whereDate('timestamp', now())->count(),
            'activeUsersCount' => AuditLog::where('timestamp', '>=', now()->subDay())->distinct('user_id')->count('user_id'),
        ];

        return Inertia::render('Admin/Audit', [
            'logs' => $logs,
            'stats' => $stats,
            'filters' => $request->only('search')
        ]);
    }

    public function clearAudit(Request $request)
    {
        $userRoles = $request->user()->role_ids ?? [];
        if (!in_array('admin', $userRoles)) {
            abort(403, 'Шумо ҳуқуқи тоза кардани аудитро надоред.');
        }

        AuditLog::truncate();

        // Log the deletion itself
        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'clearAudit',
            'entity_type' => 'Admin',
            'description' => 'Cleared all audit logs',
            'timestamp' => now()->toIso8601String(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip()
        ]);

        return redirect()->back();
    }
}
