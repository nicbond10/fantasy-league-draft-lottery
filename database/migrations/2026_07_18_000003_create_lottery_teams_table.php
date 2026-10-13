<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_teams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lottery_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('abbreviation', 10)->nullable();
            $table->unsignedInteger('weight');
            $table->unsignedInteger('display_order');
            $table->timestamps();

            $table->index('lottery_id');
            $table->unique(['lottery_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_teams');
    }
};
