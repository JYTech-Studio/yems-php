<?php

namespace App\Http\Controllers;

use App\Models\CreditTransaction;
use App\Models\LeaveRecord;
use App\Support\ReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        // 財務總覽：只統計有金額欄位的儲值交易（對齊 yems finance-summary）
        $purchases = CreditTransaction::where('tx_type', 'purchase')
            ->whereBetween('created_at', [$from, $to])
            ->where(fn ($q) => $q->whereNotNull('list_price_amount')->orWhereNotNull('paid_amount')->orWhereNotNull('discount_amount'))
            ->get();

        $finance = [
            'purchase_count'         => $purchases->count(),
            'total_paid'             => $purchases->sum('paid_amount'),
            'total_list_price'       => $purchases->sum('list_price_amount'),
            'total_discount'         => $purchases->sum('discount_amount'),
            'total_credits_purchased'=> $purchases->sum('amount'),
        ];

        return view('reports.index', compact('finance', 'from', 'to'));
    }

    public function creditTransactions(Request $request)
    {
        [$from, $to] = $this->range($request);

        $rows = CreditTransaction::with(['enrollment.student', 'enrollment.course'])
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->limit(10000)->get()
            ->map(fn ($tx) => [
                $tx->created_at->timezone('Asia/Taipei')->format('Y-m-d H:i:s'),
                $tx->enrollment?->student?->name ?? '',
                $tx->enrollment?->course?->name ?? '',
                $tx->typeLabel(),
                $tx->amount,
                $tx->balance_after,
                $tx->note ?? '',
            ])->all();

        $headers = ['交易時間', '學生', '課程', '類型', '點數異動', '異動後餘額', '備註'];

        return $this->download($request, $headers, $rows, 'credit-transactions');
    }

    public function leaveRecords(Request $request)
    {
        [$from, $to] = $this->range($request);

        $rows = LeaveRecord::with(['student', 'enrollment.course'])
            ->whereBetween('leave_date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('leave_date')
            ->limit(10000)->get()
            ->map(fn ($l) => [
                $l->leave_date->format('Y-m-d'),
                $l->student?->name ?? '',
                $l->enrollment?->course?->name ?? '',
                $l->reason ?? '',
                $l->is_made_up ? '已補課' : '未補課',
                $l->made_up_date?->format('Y-m-d') ?? '',
            ])->all();

        $headers = ['請假日期', '學生', '課程', '原因', '補課狀態', '補課日期'];

        return $this->download($request, $headers, $rows, 'leave-records');
    }

    private function download(Request $request, array $headers, array $rows, string $base)
    {
        $date = Carbon::now('Asia/Taipei')->format('Ymd');
        $format = $request->query('format') === 'xlsx' ? 'xlsx' : 'csv';
        $filename = "{$base}-{$date}.{$format}";

        return $format === 'xlsx'
            ? ReportExport::xlsx($headers, $rows, $filename)
            : ReportExport::csv($headers, $rows, $filename);
    }

    /** @return array{0:Carbon,1:Carbon} */
    private function range(Request $request): array
    {
        $tz = 'Asia/Taipei';
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfDay();

        return [$from, $to];
    }
}
