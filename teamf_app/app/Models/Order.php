<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'address_id',
        'payment_id',
        'status',
        'total_price',
        'payment_method',
        'ordered_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_price' => 'decimal:2',
        'ordered_at' => 'datetime',
    ];

    /**
     * 定数定義
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * リレーション定義
     */

    // 多対1：注文は1人のユーザーに属する
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 多対1：注文は1つの住所を使用
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    // 多対1：注文は1つの支払いに対応
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    // 1対多：注文は複数の注文項目を持つ
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // 多対多（間接的）：注文は注文項目経由で複数のドレスを持つ
    public function dresses()
    {
        return $this->hasManyThrough(Dress::class, OrderItem::class, 'order_id', 'id', 'id', 'product_id');
    }

    /**
     * 便利メソッド
     */

    // 注文番号を生成（例：ORD-20250610-000001）
    public function getOrderNumberAttribute()
    {
        return 'ORD-' . $this->ordered_at->format('Ymd') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    // フォーマットされた合計金額
    public function getFormattedTotalPriceAttribute()
    {
        return '¥' . number_format($this->total_price);
    }

    // ステータスの日本語名
    public function getStatusNameAttribute()
    {
        $statuses = [
            self::STATUS_PENDING => '注文確認中',
            self::STATUS_PROCESSING => '処理中',
            self::STATUS_SHIPPED => '発送済み',
            self::STATUS_DELIVERED => '配達完了',
            self::STATUS_CANCELLED => 'キャンセル',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    // 注文項目の総数量
    public function getTotalQuantityAttribute()
    {
        return $this->orderItems->sum('quantity');
    }

    // 注文をキャンセル
    public function cancel()
    {
        if ($this->canBeCancelled()) {
            $this->update(['status' => self::STATUS_CANCELLED]);

            // 在庫を戻す
            foreach ($this->orderItems as $item) {
                $item->dress->increaseStock($item->quantity);
            }

            return true;
        }

        return false;
    }

    // キャンセル可能かチェック
    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    // 発送可能かチェック
    public function canBeShipped()
    {
        return $this->status === self::STATUS_PROCESSING && $this->payment->isCompleted();
    }

    // 配達完了に変更
    public function markAsDelivered()
    {
        if ($this->status === self::STATUS_SHIPPED) {
            $this->update(['status' => self::STATUS_DELIVERED]);
            return true;
        }

        return false;
    }

    // 発送済みに変更
    public function markAsShipped()
    {
        if ($this->canBeShipped()) {
            $this->update(['status' => self::STATUS_SHIPPED]);
            return true;
        }

        return false;
    }

    // 処理中に変更
    public function markAsProcessing()
    {
        if ($this->status === self::STATUS_PENDING) {
            $this->update(['status' => self::STATUS_PROCESSING]);
            return true;
        }

        return false;
    }

    /**
     * スコープ
     */

    // 特定ステータスで絞り込み
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // 期間で絞り込み
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('ordered_at', [$startDate, $endDate]);
    }

    // 今日の注文
    public function scopeToday($query)
    {
        return $query->whereDate('ordered_at', today());
    }

    // 支払い完了済みの注文
    public function scopePaid($query)
    {
        return $query->whereHas('payment', function ($q) {
            $q->where('status', Payment::STATUS_COMPLETED);
        });
    }

    // ユーザーの注文
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
