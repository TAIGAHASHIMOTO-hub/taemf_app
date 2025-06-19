<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * ユーザー登録
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date|before:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'phone' => $request->phone,
            'birthdate' => $request->birthdate,
            'is_admin' => false,
        ]);

        return response()->json([
            'message' => 'ユーザー登録が完了しました',
            'user' => $user
        ], 201);
    }

    /**
     * ログイン
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'メールアドレスまたはパスワードが正しくありません'
            ], 401);
        }

        $user = Auth::user();

        // Laravel Sanctumを使用する場合のトークン生成
        // $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'ログインに成功しました',
            'user' => $user->load('addresses'),
            // 'token' => $token // Sanctum使用時
        ]);
    }

    /**
     * ログアウト
     */
    public function logout(Request $request)
    {
        // Laravel Sanctumを使用する場合
        // $request->user()->currentAccessToken()->delete();

        Auth::logout();

        return response()->json([
            'message' => 'ログアウトしました'
        ]);
    }

    /**
     * パスワード変更
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => '現在のパスワードが正しくありません'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'パスワードが変更されました'
        ]);
    }

    /**
     * 現在のユーザー情報取得
     */
    public function me(Request $request)
    {
        $userId = $request->user()->id;
        $user = User::with('addresses')->findOrFail($userId);

        return response()->json($user);
    }

    /**
     * メールアドレス確認用（将来の機能拡張用）
     */
    public function sendVerificationEmail(Request $request)
    {
        // メール確認機能の実装
        // 実際のプロジェクトではメール送信ロジックを追加

        return response()->json([
            'message' => '確認メールを送信しました'
        ]);
    }

    /**
     * パスワードリセット要求（将来の機能拡張用）
     */
    public function requestPasswordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        // パスワードリセットメール送信ロジックを追加

        return response()->json([
            'message' => 'パスワードリセット用のメールを送信しました'
        ]);
    }
}
