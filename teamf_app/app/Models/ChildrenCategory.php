<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChildrenCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_category_id',
        'name',
    ];

    /**
     * リレーション定義
     */

    // 多対1：子カテゴリは1つの親カテゴリに属する
    public function parentCategory()
    {
        return $this->belongsTo(ParentCategory::class);
    }

    // 1対多：子カテゴリは複数のドレスを持つ
    public function dresses()
    {
        return $this->hasMany(Dress::class);
    }

    /**
     * 便利メソッド
     */

    // このカテゴリの商品数を取得
    public function getDressCountAttribute()
    {
        return $this->dresses()->count();
    }

    // 在庫ありの商品数を取得
    public function getInStockDressCountAttribute()
    {
        return $this->dresses()->where('stock', '>', 0)->count();
    }

    // 親カテゴリ名と一緒に表示用の名前を取得
    public function getFullNameAttribute()
    {
        return $this->parentCategory->name . ' > ' . $this->name;
    }

    // 在庫ありの商品のみ取得
    public function inStockDresses()
    {
        return $this->dresses()->where('stock', '>', 0);
    }

    // 価格帯で商品を取得
    public function dressesByPriceRange($minPrice = null, $maxPrice = null)
    {
        $query = $this->dresses();

        if ($minPrice) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }
}
