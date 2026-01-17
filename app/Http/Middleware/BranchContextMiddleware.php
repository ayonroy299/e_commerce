<?php

namespace App\Http\Middleware;

use App\Domain\Auth\Services\BranchContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BranchContextMiddleware
{
    public function __construct(
        protected BranchContext $branchContext
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // If session doesn't have a branch, try to set the default from the user
            if (!session()->has('current_branch_id') && $user->branch_id) {
                $this->branchContext->setCurrentBranchId($user->branch_id);
            }
        }

        return $next($request);
    }
}
