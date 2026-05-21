<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    private static array $categoryNames = [
        'Alimentação', 'Transporte', 'Moradia', 'Saúde', 'Educação',
        'Lazer', 'Roupas', 'Tecnologia', 'Viagem', 'Assinaturas',
        'Academia', 'Farmácia', 'Restaurante', 'Combustível', 'Internet',
    ];

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name'    => fake()->unique()->randomElement(self::$categoryNames),
        ];
    }
}
