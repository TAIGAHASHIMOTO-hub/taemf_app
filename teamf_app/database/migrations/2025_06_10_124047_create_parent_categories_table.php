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
        Schema::create('parent_categories', function (Blueprint $table) {
            $table->id();                // 主キー（自動でインクリメント）
            $table->string('name');      // 親カテゴリ名
            $table->timestamps();        // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_categories');
    }
};
