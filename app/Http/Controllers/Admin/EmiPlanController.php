<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmiPlanStoreRequest;
use App\Http\Requests\Admin\EmiPlanUpdateRequest;
use App\Models\EmiPlan;
use App\Traits\HasCrud;
use App\Utils\CrudConfig;

class EmiPlanController extends Controller
{
    use HasCrud;

    public function __construct()
    {
        $this->init(new CrudConfig(
            resource: 'emi_plans',
            modelClass: EmiPlan::class,
            storeRequestClass: EmiPlanStoreRequest::class,
            updateRequestClass: EmiPlanUpdateRequest::class,
            componentPath: 'Admin/Emi/Plans/Index',
            searchColumns: ['name'],
            withRelations: ['creator'],
        ));
    }
}
