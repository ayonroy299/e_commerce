<?php

namespace App\Domain\Auth\Services;

class BranchContext
{
    private ?string $currentBranchId = null;

    /**
     * Get the current active branch ID.
     */
    public function getCurrentBranchId(): ?string
    {
        if ($this->currentBranchId) {
            return $this->currentBranchId;
        }

        return session('current_branch_id');
    }

    /**
     * Set the current active branch ID.
     */
    public function setCurrentBranchId(string $branchId): void
    {
        $this->currentBranchId = $branchId;
        session(['current_branch_id' => $branchId]);
    }

    /**
     * Clear the current branch context.
     */
    public function clear(): void
    {
        $this->currentBranchId = null;
        session()->forget('current_branch_id');
    }
}
