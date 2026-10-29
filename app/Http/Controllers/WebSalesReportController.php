<?php
// app/Http/Controllers/WebSalesReportController.php

namespace App\Http\Controllers;

use App\Models\WebOrderSnapshot;
use App\Models\WebOrderItemSnapshot;
use App\Models\WebSyncLog;
use App\Services\WebOrderSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebSalesReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->toDateString());
        $search    = $request->input('q');
        $payment   = $request->input('payment_status');

        // ====== Query dasar (SNAPSHOT lokal, bukan DB web) ======
        $baseQuery = WebOrderSnapshot::query()
            ->completed()
            ->betweenDates($startDate, $endDate);

        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }
        if ($payment && $payment !== 'all') {
            $baseQuery->where('payment_status', $payment);
        }

        $orders = (clone $baseQuery)
            ->orderByDesc('web_created_at')
            ->paginate(25)
            ->withQueryString();

        // ====== Ringkasan ======
        $summary = (clone $baseQuery)->selectRaw('
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount), 0) as total_revenue,
            COALESCE(AVG(total_amount), 0) as avg_order_value,
            COALESCE(SUM(shipping_cost), 0) as total_shipping,
            COALESCE(SUM(discount_amount), 0) as total_discount
        ')->first();

        // ====== Top 10 produk terlaris ======
        $topProducts = WebOrderItemSnapshot::query()
            ->join('web_orders_snapshot as o', 'o.web_order_id', '=', 'web_order_items_snapshot.web_order_id')
            ->where('o.status', 'completed')
            ->whereBetween('o.web_created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ])
            ->selectRaw('
                web_order_items_snapshot.product_id,
                web_order_items_snapshot.product_name,
                SUM(web_order_items_snapshot.quantity) as total_qty,
                SUM(web_order_items_snapshot.subtotal) as total_revenue
            ')
            ->groupBy('web_order_items_snapshot.product_id', 'web_order_items_snapshot.product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // ====== Distribusi pendapatan per kategori ======
        $byCategory = WebOrderItemSnapshot::query()
            ->join('web_orders_snapshot as o', 'o.web_order_id', '=', 'web_order_items_snapshot.web_order_id')
            ->where('o.status', 'completed')
            ->whereBetween('o.web_created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ])
            ->whereNotNull('web_order_items_snapshot.category_name')
            ->selectRaw('
                web_order_items_snapshot.category_name,
                SUM(web_order_items_snapshot.subtotal) as total_revenue,
                SUM(web_order_items_snapshot.quantity) as total_qty
            ')
            ->groupBy('web_order_items_snapshot.category_name')
            ->orderByDesc('total_revenue')
            ->get();

        // ====== Info sync terakhir ======
        $lastSync = WebSyncLog::where('type', 'orders')
            ->where('status', 'success')
            ->latest('finished_at')
            ->first();

        return view('reports.web-sales.index', compact(
            'orders', 'summary', 'topProducts', 'byCategory',
            'startDate', 'endDate', 'search', 'payment', 'lastSync'
        ));
    }

    public function show($id)
    {
        $order = WebOrderSnapshot::with(['items'])
            ->where('web_order_id', $id)
            ->firstOrFail();

        return view('reports.web-sales.show', compact('order'));
    }

    public function syncNow(WebOrderSyncService $service)
    {
        $log = $service->syncCompletedOrders();

        if ($log->status === 'success') {
            $msg = "Sinkronisasi berhasil: {$log->records_created} baru, {$log->records_updated} diperbarui.";
            if ($log->records_skipped > 0) {
                $msg .= " {$log->records_skipped} dilewati.";
            }
            return back()->with('success', $msg);
        }

        return back()->with('error', "Sinkronisasi gagal: {$log->error_message}");
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // Cari berdasarkan ID web_order_id
            $order = WebOrderSnapshot::where('web_order_id', $id)->firstOrFail();
            
            // Hapus item pesanan terlebih dahulu
            WebOrderItemSnapshot::where('web_order_id', $id)->delete();
            
            // Hapus pesanan
            $order->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data pesanan berhasil dihapus.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }
}