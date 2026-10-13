<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_draw_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lottery_draw_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lottery_team_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('selection_position');
            $table->json('team_snapshot');
            $table->timestamps();

            $table->index('lottery_draw_id');
            $table->unique(['lottery_draw_id', 'selection_position']);
            $table->unique(['lottery_draw_id', 'lottery_team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_draw_results');
    }
};
