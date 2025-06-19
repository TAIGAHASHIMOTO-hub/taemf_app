<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DressController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned the "api" middleware group. Make something great!
|
*/

// 認証不要のルート
Route::prefix('v1')->group(function () {

    // 認証関連
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/password/reset', [AuthController::class, 'requestPasswordReset']);

    // 商品関連（公開）
    Route::prefix('dresses')->group(function () {
        Route::get('/', [DressController::class, 'index']); // 商品一覧
        Route::get('/popular', [DressController::class, 'popular']); // 人気商品
        Route::get('/latest', [DressController::class, 'latest']); // 新着商品
        Route::get('/{id}', [DressController::class, 'show']); // 商品詳細
        Route::get('/category/{categoryId}', [DressController::class, 'byCategory']); // カテゴリ別商品
    });

    // カテゴリ関連（公開）
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']); // 全カテゴリ（階層構造）
        Route::get('/parent', [CategoryController::class, 'parentCategories']); // 親カテゴリ一覧
        Route::get('/children/{parentId?}', [CategoryController::class, 'childrenCategories']); // 子カテゴリ一覧
        Route::get('/active/{parentId?}', [CategoryController::class, 'activeChildrenCategories']); // アクティブな子カテゴリ
        Route::get('/parent/{id}', [CategoryController::class, 'showParent']); // 親カテゴリ詳細
        Route::get('/children/detail/{id}', [CategoryController::class, 'showChildren']); // 子カテゴリ詳細
    });
});

// 認証が必要なルート
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // 認証関連
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/password/change', [AuthController::class, 'changePassword']);
    Route::post('/email/verification', [AuthController::class, 'sendVerificationEmail']);

    // ユーザー関連
    Route::prefix('users')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']); // プロフィール取得
        Route::put('/profile', [UserController::class, 'updateProfile']); // プロフィール更新
    });

    // 住所関連
    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']); // 住所一覧
        Route::post('/', [AddressController::class, 'store']); // 住所登録
        Route::get('/{id}', [AddressController::class, 'show']); // 住所詳細
        Route::put('/{id}', [AddressController::class, 'update']); // 住所更新
        Route::delete('/{id}', [AddressController::class, 'destroy']); // 住所削除
        Route::post('/{id}/default', [AddressController::class, 'setDefault']); // デフォルト設定
    });

    // 注文関連
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']); // 注文一覧
        Route::post('/', [OrderController::class, 'store']); // 注文作成
        Route::get('/{id}', [OrderController::class, 'show']); // 注文詳細
        Route::post('/{id}/cancel', [OrderController::class, 'cancel']); // 注文キャンセル
    });

    // 支払い関連
    Route::prefix('payments')->group(function () {
        Route::get('/{id}', [PaymentController::class, 'show']); // 支払い詳細
        Route::post('/{id}/card', [PaymentController::class, 'processCardPayment']); // カード支払い
        Route::post('/{id}/bank-transfer', [PaymentController::class, 'processBankTransfer']); // 銀行振込
        Route::post('/{id}/cash-on-delivery', [PaymentController::class, 'processCashOnDelivery']); // 代金引換
    });
});

// 管理者専用ルート
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'admin'])->group(function () {

    // ユーザー管理
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']); // ユーザー一覧
        Route::post('/', [UserController::class, 'store']); // ユーザー作成
        Route::get('/{id}', [UserController::class, 'show']); // ユーザー詳細
        Route::put('/{id}', [UserController::class, 'update']); // ユーザー更新
        Route::delete('/{id}', [UserController::class, 'destroy']); // ユーザー削除
    });

    // 商品管理
    Route::prefix('dresses')->group(function () {
        Route::post('/', [DressController::class, 'store']); // 商品作成
        Route::put('/{id}', [DressController::class, 'update']); // 商品更新
        Route::delete('/{id}', [DressController::class, 'destroy']); // 商品削除
    });

    // カテゴリ管理
    Route::prefix('categories')->group(function () {
        Route::post('/parent', [CategoryController::class, 'storeParent']); // 親カテゴリ作成
        Route::put('/parent/{id}', [CategoryController::class, 'updateParent']); // 親カテゴリ更新
        Route::delete('/parent/{id}', [CategoryController::class, 'destroyParent']); // 親カテゴリ削除

        Route::post('/children', [CategoryController::class, 'storeChildren']); // 子カテゴリ作成
        Route::put('/children/{id}', [CategoryController::class, 'updateChildren']); // 子カテゴリ更新
        Route::delete('/children/{id}', [CategoryController::class, 'destroyChildren']); // 子カテゴリ削除
    });

    // 注文管理
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'adminIndex']); // 注文一覧（管理者用）
        Route::put('/{id}/status', [OrderController::class, 'updateStatus']); // 注文ステータス更新
    });

    // 支払い管理
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'adminIndex']); // 支払い一覧（管理者用）
        Route::post('/{id}/confirm', [PaymentController::class, 'confirmPayment']); // 支払い確認
        Route::post('/{id}/refund', [PaymentController::class, 'refund']); // 返金処理
    });
});

// デフォルトルート（Laravel標準）
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ヘルスチェック用
Route::get('/v1/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});
