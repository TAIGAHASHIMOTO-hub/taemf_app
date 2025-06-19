<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'children_category_id',
        'name',
        'description',
        'price',
        'stock',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    /**
     * リレーション定義
     */

    // 多対1：ドレスは1つの子カテゴリに属する
    public function childrenCategory()
    {
        return $this->belongsTo(ChildrenCategory::class);
    }

    // 間接的：ドレスは子カテゴリ経由で親カテゴリにアクセス
    public function parentCategory()
    {
        return $this->childrenCategory->parentCategory();
    }

    // 1対多：ドレスは複数の画像を持つ
    public function images()
    {
        return $this->hasMany(DressImage::class, 'dresses_id')->orderBy('sort_order');
    }

    // 1対多：ドレスは複数の注文項目で使用される
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    /**
     * 便利メソッド
     */

    // 在庫があるかチェック
    public function isInStock()
    {
        return $this->stock > 0;
    }

    // メイン画像を取得（sort_orderが最小のもの）
    public function getMainImageAttribute()
    {
        return $this->images()->first();
    }

    // フォーマットされた価格を取得
    public function getFormattedPriceAttribute()
    {
        return '¥' . number_format($this->price);
    }

    // カテゴリの階層パスを取得
    public function getCategoryPathAttribute()
    {
        return $this->childrenCategory->parentCategory->name . ' > ' . $this->childrenCategory->name;
    }

    // 在庫チェック後に数量を減らす
    public function decreaseStock($quantity)
    {
        if ($this->stock >= $quantity) {
            $this->decrement('stock', $quantity);
            return true;
        }
        return false;
    }

    // 在庫を増やす（返品・キャンセル時）
    public function increaseStock($quantity)
    {
        $this->increment('stock', $quantity);
    }

    // スコープ：在庫ありの商品のみ
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    // スコープ：価格帯で絞り込み
    public function scopePriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }

    // スコープ：カテゴリで絞り込み
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('children_category_id', $categoryId);
    }
}
