<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------
    // ESCOPO: usuário não acessa dados de outro usuário
    // -------------------------------------------------------

    public function test_user_cannot_view_another_users_category(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryOfB = Category::factory()->create([
            'user_id' => $userB->id,
            'name'    => 'B Category',
        ]);

        $response = $this->actingAs($userA, 'sanctum')
            ->getJson("/api/v1/categories/{$categoryOfB->id}");

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_another_users_category(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryOfB = Category::factory()->create([
            'user_id' => $userB->id,
            'name'    => 'B Category',
        ]);

        $response = $this->actingAs($userA, 'sanctum')
            ->putJson("/api/v1/categories/{$categoryOfB->id}", [
                'name' => 'Hacked',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_another_users_category(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $categoryOfB = Category::factory()->create([
            'user_id' => $userB->id,
            'name'    => 'B Category',
        ]);

        $response = $this->actingAs($userA, 'sanctum')
            ->deleteJson("/api/v1/categories/{$categoryOfB->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('categories', ['id' => $categoryOfB->id]);
    }

    public function test_index_returns_only_authenticated_users_categories(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Category::factory()->create(['user_id' => $userA->id, 'name' => 'A Category']);
        Category::factory()->create(['user_id' => $userB->id, 'name' => 'B Category']);

        $response = $this->actingAs($userA, 'sanctum')
            ->getJson('/api/v1/categories');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals('A Category', $data[0]['name']);
    }

    // -------------------------------------------------------
    // CRUD básico
    // -------------------------------------------------------

    public function test_user_can_create_a_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/categories', ['name' => 'Alimentação']);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Alimentação');

        $this->assertDatabaseHas('categories', [
            'name'    => 'Alimentação',
            'user_id' => $user->id,
        ]);
    }

    public function test_category_name_must_be_unique_per_user(): void
    {
        $user = User::factory()->create();
        Category::factory()->create(['user_id' => $user->id, 'name' => 'Transporte']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/categories', ['name' => 'Transporte']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_same_category_name_can_be_used_by_different_users(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Category::factory()->create(['user_id' => $userA->id, 'name' => 'Alimentação']);

        $response = $this->actingAs($userB, 'sanctum')
            ->postJson('/api/v1/categories', ['name' => 'Alimentação']);

        $response->assertStatus(201);
    }

    public function test_user_can_update_own_category(): void
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/categories/{$category->id}", ['name' => 'New Name']);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_user_can_delete_own_category(): void
    {
        $user     = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'To Delete']);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
