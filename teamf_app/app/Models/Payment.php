<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'amount_paid',
        'status',
        'transaction_id',
        'method',
        'paid_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * 定数定義
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    const METHOD_CREDIT_CARD = 'credit_card';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_CASH_ON_DELIVERY = 'cash_on_delivery';
    const METHOD_PAYPAL = 'paypal';

    /**
     * リレーション定義
     */

    // 1対1：支払いは1つの注文に対応
    public function order()
    {
        return $this->hasOne(Order::class);
    }

    /**
     * 便利メソッド
     */

    // 支払い完了かチェック
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    // 支払い待ちかチェック
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    // 支払い失敗かチェック
    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    // 返金済みかチェック
    public function isRefunded()
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    // 支払いを完了に変更
    public function markAsCompleted($transactionId = null)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now(),
            'transaction_id' => $transactionId ?: $this->transaction_id,
        ]);
    }

    // 支払いを失敗に変更
    public function markAsFailed()
    {
        $this->update([
            'status' => self::STATUS_FAILED,
        ]);
    }

    // 返金処理
    public function markAsRefunded()
    {
        $this->update([
            'status' => self::STATUS_REFUNDED,
        ]);
    }

    // フォーマットされた金額を取得
    public function getFormattedAmountAttribute()
    {
        return '¥' . number_format($this->amount_paid);
    }

    // 支払い方法の日本語名を取得
    public function getMethodNameAttribute()
    {
        $methods = [
            self::METHOD_CREDIT_CARD => 'クレジットカード',
            self::METHOD_BANK_TRANSFER => '銀行振込',
            self::METHOD_CASH_ON_DELIVERY => '代金引換',
            self::METHOD_PAYPAL => 'PayPal',
        ];

        return $methods[$this->method] ?? $this->method;
    }

    // ステータスの日本語名を取得
    public function getStatusNameAttribute()
    {
        $statuses = [
            self::STATUS_PENDING => '支払い待ち',
            self::STATUS_COMPLETED => '支払い完了',
            self::STATUS_FAILED => '支払い失敗',
            self::STATUS_REFUNDED => '返金済み',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * スコープ
     */

    // 完了済みの支払いのみ
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // 待機中の支払いのみ
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // 特定の支払い方法で絞り込み
    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    // 期間で絞り込み
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('paid_at', [$startDate, $endDate]);
    }
}
