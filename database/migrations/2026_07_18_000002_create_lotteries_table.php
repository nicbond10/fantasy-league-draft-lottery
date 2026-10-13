<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotteries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('mode', 20);
            $table->string('format', 40)->default('weighted_single_pick');
            $table->string('status', 20)->default('draft');
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotteries');
    }
};
