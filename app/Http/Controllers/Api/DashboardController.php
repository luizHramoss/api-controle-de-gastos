<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user      = $request->user();
        $now       = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth   = $now->copy()->endOfMonth();

        // Total gasto no mês atual
        $totalThisMonth = $user->expenses()
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Últimas 5 despesas
        $latestExpenses = $user->expenses()
            ->with('category')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Resumo por categoria no mês atual
        $categoryBreakdown = $user->expenses()
            ->with('category')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->select('category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category_id'   => $item->category_id,
                    'category_name' => $item->category?->name ?? 'Sem categoria',
                    'total'         => number_format((float) $item->total, 2, '.', ''),
                    'count'         => $item->count,
                ];
            });

        return response()->json([
            'message' => 'Dashboard data retrieved successfully.',
            'data'    => [
                'current_month'      => $now->format('Y-m'),
                'total_this_month'   => number_format((float) $totalThisMonth, 2, '.', ''),
                'latest_expenses'    => ExpenseResource::collection($latestExpenses),
                'category_breakdown' => $categoryBreakdown,
            ],
        ]);
    }
}
