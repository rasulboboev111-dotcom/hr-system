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
        if (!$request->user()->hasPermission('edit_all')) {
            abort(403, 'Шумо ҳуқуқи таҳрири танзимотро надоред.');
        }
        $data = $request->validate([
            'companyName' => 'required|string',
            'theme' => 'required|string',
            'notifications' => 'boolean'
        ]);

        // Just mock updating settings by writing an Audit log
        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Update Settings',
            'entity_type' => 'Settings',
            'description' => json_encode($data),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back()->with('success', 'Танзимот бо муваффақият сабт шуд');
    }
}
