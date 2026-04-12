<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AuditLog;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('view_calendar')) {
            abort(403, 'Шумо ҳуқуқи дидани тақвимро надоред.');
        }

        $query = CalendarEvent::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%")
                  ->orWhere('type', 'ilike', "%{$search}%");
            });
        }

        return Inertia::render('Calendar', [
            'eventsList' => $query->get(),
            'filters' => $request->only('search')
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('add_calendar_events')) {
            abort(403, 'Шумо ҳуқуқи илова кардани ҳаводисро надоред.');
        }

        $data = $request->validate([
            'title' => 'required|string',
            'date' => 'required|date',
            'type' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $event = CalendarEvent::create($data);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Create Event',
            'entity_type' => 'CalendarEntry',
            'description' => json_encode($data),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function destroy(Request $request, CalendarEvent $event)
    {
        if (!$request->user()->hasPermission('delete_calendar_events')) {
            abort(403, 'Шумо ҳуқуқи нест кардани ҳаводисро надоред.');
        }

        $event->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Delete Event',
            'entity_type' => 'CalendarEntry',
            'description' => json_encode(['id' => $event->id, 'title' => $event->title]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }
}
