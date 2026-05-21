<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------
    // ESCOPO: valor da despesa deve ser positivo
    // -------------------------------------------------------

    public function test_expense_amount_must_be_positive(): void
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/expenses', [
                'description' => 'Test expense',
                'amount'      => 0,
                'date'        => now()->toDateString(),
                'category_id' => $category->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_expense_amount_cannot_be_negative(): void
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/expenses', [
                'description' => 'Test expense',
                'amount'      => -50.00,
                'date'        => now()->toDateString(),
                'category_id' => $category->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_expense_amount_must_be_greater_than_zero(): void
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/expenses', [
                'description' => 'Valid expense',
                'amount'      => 0.01,
                'date'        => now()->toDateString(),
                'category_id' => $category->id,
            ]);

        $response->assertStatus(201);
    }

    // -------------------------------------------------------
    // ESCOPO: categoria deve pertencer ao usuário autenticado
    // -------------------------------------------------------

    public function test_expense_category_must_belong_to_authenticated_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Categoria que pertence ao userB
        $categoryOfB = Category::factory()->create(['user_id' => $userB->id]);

        $response = $this->actingAs($userA, 'sanctum')
            ->postJson('/api/v1/expenses', [
                'description' => 'Test expense',
                'amount'      => 100.00,
                'date'        => now()->toDateString(),
                'category_id' => $categoryOfB->id,   // <-- category do outro user
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_expense_can_be_created_with_own_category(): void
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/expenses', [
                'description' => 'Almoço',
                'amount'      => 45.90,
                'date'        => now()->toDateString(),
                'category_id' => $category->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.description', 'Almoço');
    }

    // -------------------------------------------------------
    // ESCOPO: usuário não acessa dados de outro usuário
    // -------------------------------------------------------

    public function test_user_cannot_view_another_users_expense(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryOfB = Category::factory()->create(['user_id' => $userB->id]);
        $expenseOfB  = Expense::factory()->create([
            'user_id'     => $userB->id,
            'category_id' => $categoryOfB->id,
        ]);

        $response = $this->actingAs($userA, 'sanctum')
            ->getJson("/api/v1/expenses/{$expenseOfB->id}");

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_another_users_expense(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryOfB = Category::factory()->create(['user_id' => $userB->id]);
        $categoryOfA = Category::factory()->create(['user_id' => $userA->id]);

        $expenseOfB = Expense::factory()->create([
            'user_id'     => $userB->id,
            'category_id' => $categoryOfB->id,
        ]);

        $response = $this->actingAs($userA, 'sanctum')
            ->putJson("/api/v1/expenses/{$expenseOfB->id}", [
                'description' => 'Hacked',
                'amount'      => 1.00,
                'date'        => now()->toDateString(),
                'category_id' => $categoryOfA->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_another_users_expense(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryOfB = Category::factory()->create(['user_id' => $userB->id]);
        $expenseOfB  = Expense::factory()->create([
            'user_id'     => $userB->id,
            'category_id' => $categoryOfB->id,
        ]);

        $response = $this->actingAs($userA, 'sanctum')
            ->deleteJson("/api/v1/expenses/{$expenseOfB->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('expenses', ['id' => $expenseOfB->id]);
    }

    // -------------------------------------------------------
    // Data futura
    // -------------------------------------------------------

    public function test_expense_date_cannot_be_more_than_one_day_in_future(): void
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/expenses', [
                'description' => 'Future expense',
                'amount'      => 100.00,
                'date'        => now()->addDays(2)->toDateString(),
                'category_id' => $category->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    public function test_expense_date_allows_tomorrow(): void
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/expenses', [
                'description' => 'Tomorrow expense',
                'amount'      => 100.00,
                'date'        => now()->addDay()->toDateString(),
                'category_id' => $category->id,
            ]);

        $response->assertStatus(201);
    }

    // -------------------------------------------------------
    // Index paginado
    // -------------------------------------------------------

    public function test_expense_index_returns_only_own_expenses(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catA = Category::factory()->create(['user_id' => $userA->id]);
        $catB = Category::factory()->create(['user_id' => $userB->id]);

        Expense::factory()->count(3)->create(['user_id' => $userA->id, 'category_id' => $catA->id]);
        Expense::factory()->count(5)->create(['user_id' => $userB->id, 'category_id' => $catB->id]);

        $response = $this->actingAs($userA, 'sanctum')
            ->getJson('/api/v1/expenses');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }
}
