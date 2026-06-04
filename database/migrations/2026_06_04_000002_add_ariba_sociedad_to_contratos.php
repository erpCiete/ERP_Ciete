<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table): void {
            $table->string('ariba_sociedad', 120)->nullable()->after('ariba_nombre_proveedor');
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table): void {
            $table->dropColumn('ariba_sociedad');
        });
    }
};
