<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * リレーション定義
     */

    // 1対多：親カテゴリは複数の子カテゴリを持つ
    public function childrenCategories()
    {
        return $this->hasMany(ChildrenCategory::class);
    }

    // 多対多（間接的）：親カテゴリは子カテゴリ経由で複数のドレスを持つ
    public function dresses()
    {
        return $this->hasManyThrough(Dress::class, ChildrenCategory::class);
    }

    /**
     * 便利メソッド
     */

    // このカテゴリの商品数を取得
    public function getDressCountAttribute()
    {
        return $this->dresses()->count();
    }

    // 子カテゴリと一緒に取得
    public function withChildren()
    {
        return $this->load('childrenCategories');
    }

    // アクティブな子カテゴリのみ取得（商品がある子カテゴリ）
    public function activeChildrenCategories()
    {
        return $this->childrenCategories()->whereHas('dresses');
    }
}
