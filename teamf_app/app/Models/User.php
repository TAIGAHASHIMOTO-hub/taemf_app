<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'phone',
        'birthdate',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birthdate' => 'date',
        'is_admin' => 'boolean',
    ];

    /**
     * ER図のリレーション定義
     */

    // 1対多：ユーザーは複数の住所を持つ
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    // 1対多：ユーザーは複数の注文を持つ
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * 管理者かどうかを判定
     */
    public function isAdmin()
    {
        return $this->is_admin;
    }

    /**
     * デフォルトの住所を取得
     */
    public function defaultAddress()
    {
        return $this->addresses()->where('is_default', true)->first();
    }
}
