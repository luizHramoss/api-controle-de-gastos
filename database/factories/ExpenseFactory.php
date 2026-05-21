<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    private static array $descriptions = [
        'Almoço no restaurante', 'Uber para o trabalho', 'Conta de luz',
        'Consulta médica', 'Livro técnico', 'Cinema', 'Roupas novas',
        'Assinatura Netflix', 'Passagem de ônibus', 'Mercado mensal',
        'Jantar com amigos', 'Gasolina', 'Academia', 'Remédios', 'Cafezinho',
    ];

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'category_id' => Category::factory(),
            'description' => fake()->randomElement(self::$descriptions),
            'amount'      => fake()->randomFloat(2, 5, 1500),
            'date'        => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
        ];
    }

    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween(now()->startOfMonth(), now())->format('Y-m-d'),
        ]);
    }
}
