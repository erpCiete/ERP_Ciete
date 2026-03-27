<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('usuarios') || Schema::hasColumn('usuarios', 'avatar_key')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('avatar_key', 32)
                ->default('avatar-ciete-logo')
                ->after('telefono');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('usuarios') || ! Schema::hasColumn('usuarios', 'avatar_key')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('avatar_key');
        });
    }
};
