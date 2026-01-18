<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\PurchaseOrder;
use App\Models\StockLedgerEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSalesReport(array $filters)
    {
        $query = Sale::with(['customer', 'branch', 'cashier'])
            ->where('status', 'completed');

        $this->applyFilters($query, $filters);

        return $query->latest('sold_at')->paginate($filters['per_page'] ?? 15);
    }

    public function getStockReport(array $filters)
    {
        $query = StockLedgerEntry::with(['productVariant.product', 'branch']);

        $this->applyFilters($query, $filters);

        return $query->latest('occurred_at')->paginate($filters['per_page'] ?? 15);
    }

    public function getPurchaseReport(array $filters)
    {
        $query = PurchaseOrder::with(['supplier', 'branch'])
            ->where('status', 'completed');

        $this->applyFilters($query, $filters);

        return $query->latest('created_at')->paginate($filters['per_page'] ?? 15);
    }

    protected function applyFilters($query, array $filters)
    {
        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $dateField = $this->getDateField($query->getModel());
            $query->whereBetween($dateField, [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay()
            ]);
        }

        if (!empty($filters['search'])) {
            // Implementation depends on model fields, generic search can be added here
        }
    }

    protected function getDateField($model): string
    {
        return match (get_class($model)) {
            Sale::class => 'sold_at',
            StockLedgerEntry::class => 'occurred_at',
            default => 'created_at',
        };
    }
}
