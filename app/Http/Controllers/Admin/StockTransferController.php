<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StockTransferStoreRequest;
use App\Http\Requests\Admin\StockTransferUpdateRequest;
use App\Models\StockTransfer;
use App\Models\StockTransferLine;
use App\Services\StockTransferService;
use App\Traits\HasCrud;
use App\Utils\CrudConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockTransferController extends Controller
{
    use HasCrud;

    protected $transferService;

    public function __construct(StockTransferService $transferService)
    {
        $this->transferService = $transferService;
        $this->init(new CrudConfig(
            resource: 'stock-transfers',
            modelClass: StockTransfer::class,
            storeRequestClass: StockTransferStoreRequest::class,
            updateRequestClass: StockTransferUpdateRequest::class,
            componentPath: 'Admin/Inventory/Transfers/Index',
            searchColumns: ['transfer_no', 'notes'],
            withRelations: ['fromBranch', 'toBranch', 'fromWarehouse', 'toWarehouse', 'creator'],
        ));
    }

    protected function addProps(): array
    {
        return [
            'branches' => \App\Models\Branch::active()->get(['id', 'name']),
            'warehouses' => \App\Models\Warehouse::where('status', true)->get(['id', 'name']),
        ];
    }



    protected function beforeStore(array $data): array
    {
        $data['transfer_no'] = 'TRF-' . strtoupper(uniqid());
        $data['created_by'] = auth()->id();
        $data['from_branch_id'] = auth()->user()->branch_id;
        return $data;
    }

    protected function afterStore($model, array $data): void
    {
        foreach ($data['lines'] as $line) {
            $model->lines()->create($line);
        }
    }

    public function show(StockTransfer $transfer)
    {
        $transfer->load(['fromBranch', 'toBranch', 'fromWarehouse', 'toWarehouse', 'lines.product', 'lines.variation', 'creator']);
        
        return Inertia::render('Admin/Inventory/Transfers/Show', [
            'transfer' => $transfer,
        ]);
    }

    public function send(StockTransfer $transfer)
    {
        try {
            $this->transferService->send($transfer);
            return back()->with('success', 'Transfer marked as sent.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receive(Request $request, StockTransfer $transfer)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:stock_transfer_lines,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
        ]);

        $quantities = collect($validated['items'])->pluck('received_quantity', 'id')->toArray();

        try {
            $this->transferService->receive($transfer, $quantities);
            return back()->with('success', 'Transfer received successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
