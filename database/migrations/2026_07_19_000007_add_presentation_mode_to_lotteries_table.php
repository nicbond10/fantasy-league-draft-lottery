<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotteries', function (Blueprint $table): void {
            $table->string('presentation_mode', 20)->default('manual')->after('format');
        });
    }

    public function down(): void
    {
        Schema::table('lotteries', function (Blueprint $table): void {
            $table->dropColumn('presentation_mode');
        });
    }
};
