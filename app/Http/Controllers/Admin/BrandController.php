<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BrandExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandStoreRequest;
use App\Http\Requests\Admin\BrandUpdateRequest;
use App\Models\Brand;
use App\Traits\HasCrud;
use App\Utils\CrudConfig;

class BrandController extends Controller
{
    use HasCrud;

    public function __construct()
    {
        $this->init(new CrudConfig(
            resource: 'brands',
            modelClass: Brand::class,
            storeRequestClass: BrandStoreRequest::class,
            updateRequestClass: BrandUpdateRequest::class,
            componentPath: 'Admin/Brands/Index',
            searchColumns: ['name'],
            exportClass: BrandExport::class,
            withRelations: [],
        ));
    }
}
