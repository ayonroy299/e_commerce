<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Traits\HasCrud;
use App\Utils\CrudConfig;
use Inertia\Inertia;

class AccountController extends Controller
{
    use HasCrud;

    protected function getCrudConfig(): CrudConfig
    {
        return new CrudConfig(
            model: Account::class,
            component: 'Admin/Accounting/Accounts/Index',
            searchColumns: ['name', 'code'],
        );
    }

    public function index()
    {
        return Inertia::render($this->getCrudConfig()->component, array_merge(
            $this->getIndexData(),
            [
                'types' => ['asset', 'liability', 'equity', 'revenue', 'expense'],
            ]
        ));
    }

    protected function beforeStore(array $data): array
    {
        $data['branch_id'] = auth()->user()->branch_id;
        return $data;
    }
}
