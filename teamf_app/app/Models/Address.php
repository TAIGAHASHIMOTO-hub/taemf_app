<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'postal_code',
        'prefecture',
        'city',
        'address1',
        'address2',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * リレーション定義
     */

    // 多対1：住所は1人のユーザーに属する
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 1対多：住所は複数の注文で使用される
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * 便利メソッド
     */

    // フルアドレスを取得
    public function getFullAddressAttribute()
    {
        $address = $this->postal_code . ' ' . $this->prefecture . $this->city . $this->address1;

        if ($this->address2) {
            $address .= ' ' . $this->address2;
        }

        return $address;
    }

    // デフォルト住所に設定
    public function setAsDefault()
    {
        // 同ユーザーの他の住所のデフォルトを解除
        $this->user->addresses()->where('id', '!=', $this->id)->update(['is_default' => false]);

        // この住所をデフォルトに設定
        $this->update(['is_default' => true]);
    }
}
