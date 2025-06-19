<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * リレーション定義
     */

    // 多対1：注文項目は1つの注文に属する
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // 多対1：注文項目は1つのドレス（商品）に対応
    public function dress()
    {
        return $this->belongsTo(Dress::class, 'product_id');
    }

    // エイリアス：productとしてもアクセス可能
    public function product()
    {
        return $this->dress();
    }

    /**
     * 便利メソッド
     */

    // 小計を計算（数量 × 単価）
    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->price;
    }

    // フォーマットされた小計
    public function getFormattedSubtotalAttribute()
    {
        return '¥' . number_format($this->subtotal);
    }

    // フォーマットされた単価
    public function getFormattedPriceAttribute()
    {
        return '¥' . number_format($this->price);
    }

    // 現在の商品価格との差額を計算
    public function getPriceDifferenceAttribute()
    {
        return $this->dress->price - $this->price;
    }

    // 注文時と現在の価格が違うかチェック
    public function hasPriceChanged()
    {
        return $this->price != $this->dress->price;
    }

    // 商品名を取得（ドレスが削除されている場合の対応）
    public function getProductNameAttribute()
    {
        return $this->dress ? $this->dress->name : '削除された商品';
    }

    // 商品の画像を取得
    public function getProductImageAttribute()
    {
        return $this->dress ? $this->dress->main_image : null;
    }

    // 在庫チェック（現在の在庫で注文可能かどうか）
    public function isStockAvailable()
    {
        if (!$this->dress) {
            return false;
        }

        return $this->dress->stock >= $this->quantity;
    }

    // この注文項目をキャンセル（在庫を戻す）
    public function cancel()
    {
        if ($this->dress && $this->order->canBeCancelled()) {
            $this->dress->increaseStock($this->quantity);
            $this->delete();
            return true;
        }

        return false;
    }

    // 数量を変更（在庫チェック付き）
    public function updateQuantity($newQuantity)
    {
        if (!$this->dress) {
            return false;
        }

        $quantityDiff = $newQuantity - $this->quantity;

        // 数量増加の場合は在庫チェック
        if ($quantityDiff > 0 && $this->dress->stock < $quantityDiff) {
            return false;
        }

        // 在庫調整
        if ($quantityDiff > 0) {
            $this->dress->decreaseStock($quantityDiff);
        } elseif ($quantityDiff < 0) {
            $this->dress->increaseStock(abs($quantityDiff));
        }

        // 数量更新
        $this->update(['quantity' => $newQuantity]);

        // 注文の合計金額も更新
        $this->order->update([
            'total_price' => $this->order->orderItems->sum('subtotal')
        ]);

        return true;
    }

    /**
     * スコープ
     */

    // 特定の注文の項目
    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    // 特定の商品の注文項目
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // 合計金額でソート
    public function scopeOrderBySubtotal($query, $direction = 'desc')
    {
        return $query->selectRaw('*, (quantity * price) as subtotal')
            ->orderBy('subtotal', $direction);
    }

    /**
     * イベント
     */

    protected static function boot()
    {
        parent::boot();

        // 注文項目作成時に在庫を減らす
        static::created(function ($orderItem) {
            if ($orderItem->dress) {
                $orderItem->dress->decreaseStock($orderItem->quantity);
            }
        });

        // 注文項目削除時に在庫を戻す
        static::deleted(function ($orderItem) {
            if ($orderItem->dress && $orderItem->order->canBeCancelled()) {
                $orderItem->dress->increaseStock($orderItem->quantity);
            }
        });
    }
}
