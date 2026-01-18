<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BackupController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index()
    {
        $backups = $this->backupService->getBackups(auth()->user()->branch_id);
        return Inertia::render('Admin/Backups/Index', [
            'backups' => $backups
        ]);
    }

    public function store()
    {
        $this->backupService->createBackup(auth()->user()->branch_id, auth()->id());
        return redirect()->back()->with('success', 'Backup created successfully.');
    }
}
