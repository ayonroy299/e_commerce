<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'rate_type' => 'required|in:percent,flat',
            'rate_value' => 'required|numeric|min:0',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;
        Tax::create($validated);

        return redirect()->back()->with('success', 'Tax created.');
    }

    public function update(Request $request, Tax $tax)
    {
         $validated = $request->validate([
            'name' => 'required|string|max:50',
            'rate_type' => 'required|in:percent,flat',
            'rate_value' => 'required|numeric|min:0',
        ]);
        
        $tax->update($validated);
        return redirect()->back()->with('success', 'Tax updated.');
    }

    public function destroy(Tax $tax)
    {
        $tax->delete();
        return redirect()->back()->with('success', 'Tax deleted.');
    }
}
