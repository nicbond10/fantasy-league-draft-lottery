<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_draws', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lottery_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('initiated_by_user_id')->constrained('users');
            $table->string('algorithm_version', 60);
            $table->json('configuration_snapshot');
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->index('initiated_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_draws');
    }
};
