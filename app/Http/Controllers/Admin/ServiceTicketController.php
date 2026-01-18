<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceTicketStoreRequest;
use App\Http\Requests\Admin\ServiceTicketUpdateRequest;
use App\Models\ServiceTicket;
use App\Traits\HasCrud;
use App\Utils\CrudConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceTicketController extends Controller
{
    use HasCrud;

    public function __construct()
    {
        $this->init(new CrudConfig(
            resource: 'service-tickets',
            modelClass: ServiceTicket::class,
            storeRequestClass: ServiceTicketStoreRequest::class,
            updateRequestClass: ServiceTicketUpdateRequest::class,
            componentPath: 'Admin/Service/Tickets/Index',
            searchColumns: ['ticket_no', 'serial_no', 'issue'],
            withRelations: ['customer', 'product', 'assignee', 'creator'],
        ));
    }

    protected function addProps(): array
    {
        return [
            'customers' => \App\Models\Customer::all(['id', 'name']),
            'products' => \App\Models\Product::all(['id', 'name']),
            'users' => \App\Models\User::all(['id', 'name']),
        ];
    }

    protected function beforeStore(array $data): array
    {
        $data['ticket_no'] = 'TKT-' . strtoupper(uniqid());
        $data['created_by'] = auth()->id();
        $data['branch_id'] = auth()->user()->branch_id;
        return $data;
    }

    public function show(ServiceTicket $ticket)
    {
        $ticket->load(['customer', 'product.media', 'variation', 'assignee', 'creator', 'actions.technician']);
        
        return Inertia::render('Admin/Service/Tickets/Show', [
            'ticket' => $ticket,
            'technicians' => \App\Models\User::all(), // Simplified, filter by role if needed
        ]);
    }

    public function updateStatus(Request $request, ServiceTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,diagnosing,waiting_parts,repaired,delivered,closed',
        ]);

        $ticket->update($validated);

        if ($validated['status'] === 'closed') {
            $ticket->update(['completed_at' => now()]);
        }

        return back()->with('success', 'Ticket status updated.');
    }
}
