<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationTemplateController extends Controller
{
    public function index()
    {
        $templates = NotificationTemplate::where('branch_id', auth()->user()->branch_id)
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Notifications/Templates/Index', [
            'templates' => $templates
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Notifications/Templates/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:notification_templates,code',
            'name' => 'required',
            'subject' => 'nullable',
            'body' => 'required',
            'channels' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;

        NotificationTemplate::create($validated);

        return redirect()->route('notification-templates.index')
            ->with('success', 'Template created successfully.');
    }

    public function edit(NotificationTemplate $notificationTemplate)
    {
        return Inertia::render('Admin/Notifications/Templates/Edit', [
            'template' => $notificationTemplate
        ]);
    }

    public function update(Request $request, NotificationTemplate $notificationTemplate)
    {
        $validated = $request->validate([
            'name' => 'required',
            'subject' => 'nullable',
            'body' => 'required',
            'channels' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $notificationTemplate->update($validated);

        return redirect()->route('notification-templates.index')
            ->with('success', 'Template updated successfully.');
    }
}
