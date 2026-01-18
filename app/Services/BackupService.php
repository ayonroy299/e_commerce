<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupService
{
    /**
     * Trigger a system backup.
     */
    public function createBackup(?int $branchId, int $userId)
    {
        $backupName = 'backup-' . now()->format('Y-m-d-H-i-s') . '.sql';
        $disk = 'local';
        $filePath = 'backups/' . $backupName;

        try {
            // In a real environment, you might use:
            // Artisan::call('backup:run');
            // For now, we simulate the database dump or use spatie/laravel-backup
            
            // Simulation of file creation
            Storage::disk($disk)->put($filePath, '-- MySQL dump simulation');

            return Backup::create([
                'branch_id' => $branchId,
                'user_id' => $userId,
                'name' => $backupName,
                'file_path' => $filePath,
                'disk' => $disk,
                'size' => Storage::disk($disk)->size($filePath),
                'status' => 'completed',
            ]);
        } catch (\Exception $e) {
            return Backup::create([
                'branch_id' => $branchId,
                'user_id' => $userId,
                'name' => $backupName,
                'status' => 'failed',
            ]);
        }
    }

    public function getBackups(?int $branchId)
    {
        return Backup::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->paginate(15);
    }
}
