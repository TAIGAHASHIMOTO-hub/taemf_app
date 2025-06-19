<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DressImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dresses_id',
        'image_url',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * リレーション定義
     */

    // 多対1：画像は1つのドレスに属する
    public function dress()
    {
        return $this->belongsTo(Dress::class, 'dresses_id');
    }

    /**
     * 便利メソッド
     */

    // フルURLを取得（相対パスの場合）
    public function getFullUrlAttribute()
    {
        // 既に完全URLの場合はそのまま返す
        if (filter_var($this->image_url, FILTER_VALIDATE_URL)) {
            return $this->image_url;
        }

        // 相対パスの場合はベースURLを付加
        return asset('storage/' . $this->image_url);
    }

    // ALTテキストを生成
    public function getAltTextAttribute()
    {
        return $this->dress->name . ' - 画像' . ($this->sort_order + 1);
    }

    // メイン画像かどうか判定
    public function isMainImage()
    {
        return $this->sort_order === 0;
    }

    // 次の画像を取得
    public function getNextImage()
    {
        return $this->dress->images()
            ->where('sort_order', '>', $this->sort_order)
            ->orderBy('sort_order')
            ->first();
    }

    // 前の画像を取得
    public function getPreviousImage()
    {
        return $this->dress->images()
            ->where('sort_order', '<', $this->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
    }

    /**
     * スコープ
     */

    // メイン画像のみ取得
    public function scopeMainOnly($query)
    {
        return $query->where('sort_order', 0);
    }

    // 表示順でソート
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * イベント
     */

    // 画像削除時の処理
    protected static function boot()
    {
        parent::boot();

        // 画像削除時に、後続の画像のsort_orderを調整
        static::deleted(function ($image) {
            $image->dress->images()
                ->where('sort_order', '>', $image->sort_order)
                ->decrement('sort_order');
        });
    }
}
