<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Import;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ImportController extends Controller
{
    protected $importService;

    public function __construct(ImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index()
    {
        $imports = Import::with('user')
            ->where('branch_id', auth()->user()->branch_id)
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Imports/Index', [
            'imports' => $imports
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:products,customers,suppliers',
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ]);

        $path = $request->file('file')->store('imports');

        $import = $this->importService->import(
            auth()->id(),
            auth()->user()->branch_id,
            $request->type,
            $path
        );

        return redirect()->back()->with('success', 'Import started successfully.');
    }
}
