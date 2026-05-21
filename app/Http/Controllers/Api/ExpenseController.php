<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $expenses = $request->user()
            ->expenses()
            ->with('category')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'message' => 'Expenses retrieved successfully.',
            'data'    => ExpenseResource::collection($expenses),
            'meta'    => [
                'current_page' => $expenses->currentPage(),
                'last_page'    => $expenses->lastPage(),
                'per_page'     => $expenses->perPage(),
                'total'        => $expenses->total(),
            ],
        ]);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = $request->user()->expenses()->create([
            'description' => $request->description,
            'amount'      => $request->amount,
            'date'        => $request->date,
            'category_id' => $request->category_id,
        ]);

        $expense->load('category');

        return response()->json([
            'message' => 'Expense created successfully.',
            'data'    => new ExpenseResource($expense),
        ], 201);
    }

    public function show(Request $request, Expense $expense): JsonResponse
    {
        $this->authorizeExpense($request, $expense);

        $expense->load('category');

        return response()->json([
            'message' => 'Expense retrieved successfully.',
            'data'    => new ExpenseResource($expense),
        ]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        $this->authorizeExpense($request, $expense);

        $expense->update([
            'description' => $request->description,
            'amount'      => $request->amount,
            'date'        => $request->date,
            'category_id' => $request->category_id,
        ]);

        $expense->load('category');

        return response()->json([
            'message' => 'Expense updated successfully.',
            'data'    => new ExpenseResource($expense),
        ]);
    }

    public function destroy(Request $request, Expense $expense): JsonResponse
    {
        $this->authorizeExpense($request, $expense);

        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully.',
            'data'    => null,
        ]);
    }

    private function authorizeExpense(Request $request, Expense $expense): void
    {
        if ($expense->user_id !== $request->user()->id) {
            abort(403, 'You do not have permission to access this expense.');
        }
    }
}
