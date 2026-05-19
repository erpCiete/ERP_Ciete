<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_notices', function (Blueprint $table) {
            $table->bigIncrements('id_home_notice');
            $table->string('category', 40);
            $table->string('title_es', 180);
            $table->text('body_es');
            $table->string('title_en', 180);
            $table->text('body_en');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index(['category', 'is_active'], 'idx_home_notices_category_active');
            $table->index(['is_featured', 'is_active'], 'idx_home_notices_featured_active');
            $table->index('starts_at', 'idx_home_notices_starts_at');
            $table->index('ends_at', 'idx_home_notices_ends_at');

            $table->foreign('created_by', 'fk_home_notices_created_by')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('updated_by', 'fk_home_notices_updated_by')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_notices');
    }
};
