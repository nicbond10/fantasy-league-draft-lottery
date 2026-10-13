<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->index(['is_platform_admin', 'status'], 'users_admin_status_index');
        });

        Schema::table('user_invitations', function (Blueprint $table): void {
            $table->index(['accepted_at', 'revoked_at', 'expires_at'], 'invitations_usable_state_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_admin_status_index');
        });

        Schema::table('user_invitations', function (Blueprint $table): void {
            $table->dropIndex('invitations_usable_state_index');
        });
    }
};
