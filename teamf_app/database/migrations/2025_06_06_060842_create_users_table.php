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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // ER図の「id 主キー」
            $table->string('name'); // name列
            $table->string('email')->unique(); // メール
            $table->string('password'); // パスワード
            $table->string('gender'); // 性別
            $table->string('phone'); // 電話番号
            $table->string('birthdate'); // 誕生日
            $table->string('is_admin'); // 管理者か判別
            $table->timestamps(); // created_at と updated_at を自動で追加
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
