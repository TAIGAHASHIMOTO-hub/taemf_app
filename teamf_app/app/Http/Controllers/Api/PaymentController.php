<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * 支払い情報取得
     */
    public function show(Request $request, $id)
    {
        $payment = Payment::with('order.user')->findOrFail($id);

        // 自分の支払いまたは管理者のみアクセス可能
        if ($payment->order->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'アクセス権限がありません'
            ], 403);
        }

        return response()->json($payment);
    }

    /**
     * 支払い処理（クレジットカード）
     */
    public function processCardPayment(Request $request, $paymentId)
    {
        $validator = Validator::make($request->all(), [
            'card_number' => 'required|string|size:16',
            'expiry_month' => 'required|integer|between:1,12',
            'expiry_year' => 'required|integer|min:' . date('Y'),
            'cvv' => 'required|string|size:3',
            'cardholder_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::findOrFail($paymentId);

        // 支払い権限チェック
        if ($payment->order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'アクセス権限がありません'
            ], 403);
        }

        if ($payment->status !== Payment::STATUS_PENDING) {
            return response()->json([
                'message' => 'この支払いは既に処理済みです'
            ], 400);
        }

        try {
            // 実際のプロジェクトでは決済サービス（Stripe、PayPalなど）のAPIを呼び出し
            // ここではダミーの処理
            $transactionId = 'txn_' . uniqid();

            // 支払い成功の場合
            $payment->markAsCompleted($transactionId);

            // 注文ステータスも更新
            $payment->order->markAsProcessing();

            return response()->json([
                'message' => '支払いが完了しました',
                'payment' => $payment->fresh(),
                'transaction_id' => $transactionId
            ]);
        } catch (\Exception $e) {
            // 支払い失敗の場合
            $payment->markAsFailed();

            return response()->json([
                'message' => '支払い処理に失敗しました',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 銀行振込支払い
     */
    public function processBankTransfer(Request $request, $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        // 支払い権限チェック
        if ($payment->order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'アクセス権限がありません'
            ], 403);
        }

        if ($payment->method !== Payment::METHOD_BANK_TRANSFER) {
            return response()->json([
                'message' => '銀行振込以外の支払い方法です'
            ], 400);
        }

        // 銀行振込の場合は管理者による確認が必要なので、ステータスはpendingのまま
        return response()->json([
            'message' => '銀行振込の詳細をお送りしました。振込完了後、確認次第処理いたします。',
            'bank_info' => [
                'bank_name' => '○○銀行',
                'branch_name' => '△△支店',
                'account_type' => '普通',
                'account_number' => '1234567',
                'account_holder' => 'ドレスショップ株式会社',
                'amount' => $payment->amount_paid,
                'reference' => $payment->order->order_number
            ]
        ]);
    }

    /**
     * 代金引換支払い
     */
    public function processCashOnDelivery(Request $request, $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        // 支払い権限チェック
        if ($payment->order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'アクセス権限がありません'
            ], 403);
        }

        if ($payment->method !== Payment::METHOD_CASH_ON_DELIVERY) {
            return response()->json([
                'message' => '代金引換以外の支払い方法です'
            ], 400);
        }

        // 代金引換の場合は商品発送時に支払い完了
        $payment->order->markAsProcessing();

        return response()->json([
            'message' => '代金引換での注文を承りました。商品をお届け時にお支払いください。',
            'payment' => $payment->fresh()
        ]);
    }

    /**
     * 返金処理（管理者のみ）
     */
    public function refund(Request $request, $paymentId)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::findOrFail($paymentId);

        if (!$payment->isCompleted()) {
            return response()->json([
                'message' => '完了していない支払いは返金できません'
            ], 400);
        }

        try {
            // 実際のプロジェクトでは決済サービスの返金APIを呼び出し
            $payment->markAsRefunded();

            // 注文もキャンセル状態に
            $payment->order->cancel();

            return response()->json([
                'message' => '返金処理が完了しました',
                'payment' => $payment->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '返金処理に失敗しました',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 支払い確認（管理者用：銀行振込確認）
     */
    public function confirmPayment(Request $request, $paymentId)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::findOrFail($paymentId);

        if ($payment->status !== Payment::STATUS_PENDING) {
            return response()->json([
                'message' => 'この支払いは既に処理済みです'
            ], 400);
        }

        $payment->markAsCompleted($request->transaction_id);
        $payment->order->markAsProcessing();

        return response()->json([
            'message' => '支払いが確認されました',
            'payment' => $payment->fresh()
        ]);
    }

    /**
     * 支払い一覧（管理者用）
     */
    public function adminIndex(Request $request)
    {
        $query = Payment::with('order.user');

        // ステータスフィルター
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // 支払い方法フィルター
        if ($request->method) {
            $query->where('method', $request->method);
        }

        // 期間フィルター
        if ($request->start_date && $request->end_date) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        $payments = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($payments);
    }
}
