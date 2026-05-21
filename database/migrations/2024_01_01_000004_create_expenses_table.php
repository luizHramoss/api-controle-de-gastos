<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('description', 255);
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->timestamps();

            // Índices para performance nas queries de dashboard
            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
