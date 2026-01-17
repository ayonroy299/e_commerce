<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceAction;
use App\Models\ServiceTicket;
use Illuminate\Http\Request;

class ServiceActionController extends Controller
{
    public function store(Request $request, ServiceTicket $ticket)
    {
        $validated = $request->validate([
            'notes' => 'required|string',
            'internal_notes' => 'nullable|string',
            'cost_estimate' => 'nullable|numeric|min:0',
        ]);

        ServiceAction::create([
            'service_ticket_id' => $ticket->id,
            'technician_id' => auth()->id(),
            'notes' => $validated['notes'],
            'internal_notes' => $validated['internal_notes'],
            'cost_estimate' => $validated['cost_estimate'] ?? 0,
        ]);

        return back()->with('success', 'Action recorded.');
    }
}
