<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => Setting::getSettings(), // Fetch key-value pairs
            'taxes' => \App\Models\Tax::all(),
            'currencies' => \App\Models\Currency::all(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        $branchId = auth()->user()->branch_id;

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(
                ['branch_id' => $branchId, 'key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Settings updated.');
    }
}
