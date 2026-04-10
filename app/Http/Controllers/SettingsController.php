<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AuditLog;

class SettingsController extends Controller
{
    public function index()
    {
        // Typically settings would be fetched from database
        return Inertia::render('Settings');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'companyName' => 'required|string',
            'theme' => 'required|string',
            'notifications' => 'boolean'
        ]);

        // Just mock updating settings by writing an Audit log
        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Update Settings',
            'entity_type' => 'Settings',
            'description' => json_encode($data),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back()->with('success', 'Танзимот бо муваффақият сабт шуд');
    }
}
