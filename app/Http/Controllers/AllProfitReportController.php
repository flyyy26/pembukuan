<?php

namespace App\Http\Controllers;

use App\Services\AllProfitReportService;
use Illuminate\Http\Request;

class AllProfitReportController extends Controller
{
    public function index(Request $request, AllProfitReportService $service)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $data = $service->build($from, $to);

        return view('reports.all-profit.index', [
            'from'    => $from,
            'to'      => $to,
            'summary' => $data['summary'],
            'total'   => $data['total'],
            'monthly' => $data['monthly'],
            'daily'   => $data['daily'],
        ]);
    }
}