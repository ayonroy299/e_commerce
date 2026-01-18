<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationJob;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationJobController extends Controller
{
    public function index()
    {
        $jobs = NotificationJob::with(['template', 'recipient'])
            ->where('branch_id', auth()->user()->branch_id)
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Notifications/Jobs/Index', [
            'jobs' => $jobs
        ]);
    }
}
