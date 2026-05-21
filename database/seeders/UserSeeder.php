<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // -----------------------------------------------
        // Usuário demo principal
        // email: demo@financeapi.com | password: password
        // -----------------------------------------------
        $demo = User::firstOrCreate(
            ['email' => 'demo@financeapi.com'],
            [
                'name'     => 'Demo User',
                'password' => Hash::make('password'),
            ]
        );

        $categories = $this->seedCategories($demo);
        $this->seedExpenses($demo, $categories);

        // -----------------------------------------------
        // Usuário admin
        // email: admin@financeapi.com | password: password
        // -----------------------------------------------
        $admin = User::firstOrCreate(
            ['email' => 'admin@financeapi.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );

        $adminCategories = $this->seedCategories($admin);
        $this->seedExpenses($admin, $adminCategories);

        $this->command->info('✅  Seed concluído! Credenciais:');
        $this->command->table(
            ['Email', 'Password'],
            [
                ['demo@financeapi.com',  'password'],
                ['admin@financeapi.com', 'password'],
            ]
        );
    }

    private function seedCategories(User $user): \Illuminate\Support\Collection
    {
        $names = [
            'Alimentação', 'Transporte', 'Moradia', 'Saúde',
            'Educação', 'Lazer', 'Assinaturas', 'Outros',
        ];

        return collect($names)->map(function ($name) use ($user) {
            return Category::firstOrCreate([
                'user_id' => $user->id,
                'name'    => $name,
            ]);
        });
    }

    private function seedExpenses(User $user, \Illuminate\Support\Collection $categories): void
    {
        // Só semeia se o usuário ainda não tiver despesas
        if ($user->expenses()->count() > 0) {
            return;
        }

        $expensesData = [
            ['description' => 'Almoço no restaurante',   'amount' => 45.90,   'daysAgo' => 1],
            ['description' => 'Uber para o trabalho',    'amount' => 18.50,   'daysAgo' => 2],
            ['description' => 'Conta de luz',            'amount' => 198.00,  'daysAgo' => 3],
            ['description' => 'Consulta médica',         'amount' => 250.00,  'daysAgo' => 5],
            ['description' => 'Assinatura Netflix',      'amount' => 55.90,   'daysAgo' => 7],
            ['description' => 'Mercado mensal',          'amount' => 820.00,  'daysAgo' => 10],
            ['description' => 'Gasolina',                'amount' => 120.00,  'daysAgo' => 12],
            ['description' => 'Curso online Laravel',   'amount' => 149.90,  'daysAgo' => 15],
            ['description' => 'Cinema com família',      'amount' => 80.00,   'daysAgo' => 18],
            ['description' => 'Farmácia',                'amount' => 67.40,   'daysAgo' => 20],
            ['description' => 'Jantar com amigos',       'amount' => 135.00,  'daysAgo' => 25],
            ['description' => 'Academia mensal',         'amount' => 99.90,   'daysAgo' => 28],
            ['description' => 'Passagem de ônibus',      'amount' => 8.00,    'daysAgo' => 30],
            ['description' => 'Spotify Premium',         'amount' => 21.90,   'daysAgo' => 35],
            ['description' => 'Livro técnico',           'amount' => 75.00,   'daysAgo' => 40],
        ];

        $categoryMap = [
            'Alimentação' => $categories->firstWhere('name', 'Alimentação'),
            'Transporte'  => $categories->firstWhere('name', 'Transporte'),
            'Moradia'     => $categories->firstWhere('name', 'Moradia'),
            'Saúde'       => $categories->firstWhere('name', 'Saúde'),
            'Assinaturas' => $categories->firstWhere('name', 'Assinaturas'),
            'Alimentação' => $categories->firstWhere('name', 'Alimentação'),
            'Transporte'  => $categories->firstWhere('name', 'Transporte'),
            'Educação'    => $categories->firstWhere('name', 'Educação'),
            'Lazer'       => $categories->firstWhere('name', 'Lazer'),
            'Saúde'       => $categories->firstWhere('name', 'Saúde'),
            'Lazer'       => $categories->firstWhere('name', 'Lazer'),
            'Saúde'       => $categories->firstWhere('name', 'Saúde'),
            'Transporte'  => $categories->firstWhere('name', 'Transporte'),
            'Assinaturas' => $categories->firstWhere('name', 'Assinaturas'),
            'Educação'    => $categories->firstWhere('name', 'Educação'),
        ];

        $categoryList = $categories->values();

        foreach ($expensesData as $index => $data) {
            Expense::create([
                'user_id'     => $user->id,
                'category_id' => $categoryList[$index % $categoryList->count()]->id,
                'description' => $data['description'],
                'amount'      => $data['amount'],
                'date'        => now()->subDays($data['daysAgo'])->toDateString(),
            ]);
        }
    }
}
