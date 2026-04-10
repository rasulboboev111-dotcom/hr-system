<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AuditLog;

class CalendarController extends Controller
{
    public function index()
    {
        return Inertia::render('Calendar', [
            'eventsList' => CalendarEvent::all()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'date' => 'required|date',
            'type' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $event = CalendarEvent::create($data);

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Create Event',
            'entity_type' => 'CalendarEntry',
            'description' => json_encode($data),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function destroy(CalendarEvent $event)
    {
        $event->delete();

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Delete Event',
            'entity_type' => 'CalendarEntry',
            'description' => json_encode(['id' => $event->id, 'title' => $event->title]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }
}
