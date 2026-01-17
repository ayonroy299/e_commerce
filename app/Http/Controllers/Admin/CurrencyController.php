<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
     public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|size:3|unique:currencies,code',
            'symbol' => 'required|string|max:5',
            'exchange_rate' => 'required|numeric|min:0',
            'is_default' => 'boolean',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;
        
        if ($validated['is_default'] ?? false) {
             Currency::where('branch_id', $validated['branch_id'])->update(['is_default' => false]);
        }

        Currency::create($validated);

        return redirect()->back()->with('success', 'Currency created.');
    }

    public function update(Request $request, Currency $currency)
    {
         $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|size:3|unique:currencies,code,' . $currency->id,
            'symbol' => 'required|string|max:5',
            'exchange_rate' => 'required|numeric|min:0',
            'is_default' => 'boolean',
        ]);
        
        if ($validated['is_default'] ?? false) {
             Currency::where('branch_id', $currency->branch_id)->where('id', '!=', $currency->id)->update(['is_default' => false]);
        }
        
        $currency->update($validated);
        return redirect()->back()->with('success', 'Currency updated.');
    }

    public function destroy(Currency $currency)
    {
        $currency->delete();
        return redirect()->back()->with('success', 'Currency deleted.');
    }
}
