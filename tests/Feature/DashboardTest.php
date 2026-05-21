<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_correct_structure(): void
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        Expense::factory()->count(3)->create([
            'user_id'     => $user->id,
            'category_id' => $category->id,
            'date'        => now()->toDateString(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'current_month',
                    'total_this_month',
                    'latest_expenses',
                    'category_breakdown',
                ],
            ]);
    }

    public function test_dashboard_total_reflects_current_month_only(): void
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        // Despesa do mês atual: R$ 100
        Expense::factory()->create([
            'user_id'     => $user->id,
            'category_id' => $category->id,
            'amount'      => 100.00,
            'date'        => now()->toDateString(),
        ]);

        // Despesa do mês passado: não deve entrar no total
        Expense::factory()->create([
            'user_id'     => $user->id,
            'category_id' => $category->id,
            'amount'      => 500.00,
            'date'        => now()->subMonth()->toDateString(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_this_month', '100.00');
    }

    public function test_dashboard_latest_expenses_returns_at_most_five(): void
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        Expense::factory()->count(8)->create([
            'user_id'     => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/dashboard');

        $this->assertCount(5, $response->json('data.latest_expenses'));
    }

    public function test_dashboard_does_not_show_other_users_data(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $catA = Category::factory()->create(['user_id' => $userA->id]);
        $catB = Category::factory()->create(['user_id' => $userB->id]);

        Expense::factory()->create([
            'user_id'     => $userA->id,
            'category_id' => $catA->id,
            'amount'      => 50.00,
            'date'        => now()->toDateString(),
        ]);

        Expense::factory()->create([
            'user_id'     => $userB->id,
            'category_id' => $catB->id,
            'amount'      => 9999.00,
            'date'        => now()->toDateString(),
        ]);

        $response = $this->actingAs($userA, 'sanctum')
            ->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_this_month', '50.00');
    }
}
