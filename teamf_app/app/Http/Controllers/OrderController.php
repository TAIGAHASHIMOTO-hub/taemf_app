<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Dress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * 注文一覧取得
     */
    public function index(Request $request)
    {
        $query = $request->user()->orders()
            ->with(['orderItems.dress.images', 'address', 'payment']);

        // ステータスフィルター
        if ($request->status) {
            $query->byStatus($request->status);
        }

        // 期間フィルター
        if ($request->start_date && $request->end_date) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        $orders = $query->orderBy('ordered_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json($orders);
    }

    /**
     * 注文詳細取得
     */
    public function show(Request $request, $id)
    {
        $order = Order::with([
            'orderItems.dress.images',
            'address',
            'payment',
            'user'
        ])->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($order);
    }

    /**
     * 注文作成
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:credit_card,bank_transfer,cash_on_delivery,paypal',
            'items' => 'required|array|min:1',
            'items.*.dress_id' => 'required|exists:dresses,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        // 住所の所有者チェック
        $address = $request->user()->addresses()->findOrFail($request->address_id);

        DB::beginTransaction();
        try {
            $totalPrice = 0;
            $orderItems = [];

            // 各商品の在庫チェックと価格計算
            foreach ($request->items as $item) {
                $dress = Dress::findOrFail($item['dress_id']);

                if ($dress->stock < $item['quantity']) {
                    throw new \Exception("商品「{$dress->name}」の在庫が不足しています");
                }

                $subtotal = $dress->price * $item['quantity'];
                $totalPrice += $subtotal;

                $orderItems[] = [
                    'dress' => $dress,
                    'quantity' => $item['quantity'],
                    'price' => $dress->price,
                    'subtotal' => $subtotal
                ];
            }

            // 支払い情報作成
            $payment = Payment::create([
                'amount_paid' => $totalPrice,
                'status' => Payment::STATUS_PENDING,
                'method' => $request->payment_method,
            ]);

            // 注文作成
            $order = Order::create([
                'user_id' => $request->user()->id,
                'address_id' => $request->address_id,
                'payment_id' => $payment->id,
                'status' => Order::STATUS_PENDING,
                'total_price' => $totalPrice,
                'payment_method' => $request->payment_method,
                'ordered_at' => now(),
            ]);

            // 注文項目作成と在庫減少
            foreach ($orderItems as $item) {
                $order->orderItems()->create([
                    'product_id' => $item['dress']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => '注文が正常に作成されました',
                'order' => $order->load(['orderItems.dress', 'address', 'payment'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => '注文作成に失敗しました',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * 注文キャンセル
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($id);

        if (!$order->canBeCancelled()) {
            return response()->json([
                'message' => 'この注文はキャンセルできません'
            ], 400);
        }

        if ($order->cancel()) {
            return response()->json([
                'message' => '注文がキャンセルされました',
                'order' => $order->fresh()
            ]);
        }

        return response()->json([
            'message' => 'キャンセルに失敗しました'
        ], 400);
    }

    /**
     * 管理者用：注文一覧取得
     */
    public function adminIndex(Request $request)
    {
        $query = Order::with(['user', 'orderItems.dress', 'address', 'payment']);

        // 検索フィルター
        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // ステータスフィルター
        if ($request->status) {
            $query->byStatus($request->status);
        }

        // 期間フィルター
        if ($request->start_date && $request->end_date) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        $orders = $query->orderBy('ordered_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($orders);
    }

    /**
     * 管理者用：注文ステータス更新
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::findOrFail($id);

        switch ($request->status) {
            case Order::STATUS_PROCESSING:
                $success = $order->markAsProcessing();
                break;
            case Order::STATUS_SHIPPED:
                $success = $order->markAsShipped();
                break;
            case Order::STATUS_DELIVERED:
                $success = $order->markAsDelivered();
                break;
            case Order::STATUS_CANCELLED:
                $success = $order->cancel();
                break;
            default:
                $success = false;
        }

        if ($success) {
            return response()->json([
                'message' => 'ステータスが更新されました',
                'order' => $order->fresh()
            ]);
        }

        return response()->json([
            'message' => 'ステータス更新に失敗しました'
        ], 400);
    }
}
