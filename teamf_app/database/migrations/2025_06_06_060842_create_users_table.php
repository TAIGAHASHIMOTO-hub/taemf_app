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
            $table->id(); //ユーザid
            $table->string('name'); //ユーザーネーム
            $table->string('email')->unique();  //Eメール
            $table->string('password');  //パスワード
            $table->enum('gender', ['male', 'female', 'other'])->nullable();  //性別
            $table->string('phone')->nullable();  //電話番号
            $table->date('birthdate')->nullable();  //誕生日
            $table->boolean('is_admin')->default(false);  //管理者化どうか
            $table->rememberToken();  //ログインを保持
            $table->timestamps();
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
