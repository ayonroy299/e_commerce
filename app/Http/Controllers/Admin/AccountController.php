<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AccountStoreRequest;
use App\Http\Requests\Admin\AccountUpdateRequest;
use App\Models\Account;
use App\Traits\HasCrud;
use App\Utils\CrudConfig;
use Inertia\Inertia;

class AccountController extends Controller
{
    use HasCrud;

    public function __construct()
    {
        $this->init(new CrudConfig(
            resource: 'accounts',
            modelClass: Account::class,
            storeRequestClass: AccountStoreRequest::class,
            updateRequestClass: AccountUpdateRequest::class,
            componentPath: 'Admin/Accounting/Accounts/Index',
            searchColumns: ['name', 'code'],
        ));
    }

    protected function addProps(): array
    {
        return [
            'types' => ['asset', 'liability', 'equity', 'revenue', 'expense'],
        ];
    }



    protected function beforeStore(array $data): array
    {
        $data['branch_id'] = auth()->user()->branch_id;
        return $data;
    }
}
