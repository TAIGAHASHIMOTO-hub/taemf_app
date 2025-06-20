<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * ユーザー一覧取得（管理者のみ）
     */
    public function index(Request $request)
    {
        $users = User::with(['addresses'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(20);

        return response()->json($users);
    }

    /**
     * ユーザー詳細取得
     */
    public function show($id)
    {
        $user = User::with(['addresses', 'orders.orderItems.dress'])
            ->findOrFail($id);

        return response()->json($user);
    }

    /**
     * ユーザー登録
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date',
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
            'message' => 'ユーザーが正常に登録されました',
            'user' => $user
        ], 201);
    }

    /**
     * ユーザー情報更新
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8|confirmed',
            'gender' => 'sometimes|nullable|in:male,female,other',
            'phone' => 'sometimes|nullable|string|max:20',
            'birthdate' => 'sometimes|nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['name', 'email', 'gender', 'phone', 'birthdate']);
        
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'ユーザー情報が更新されました',
            'user' => $user
        ]);
    }

    /**
     * ユーザー削除
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // 注文がある場合は削除不可
        if ($user->orders()->count() > 0) {
            return response()->json([
                'message' => '注文履歴があるため削除できません'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'ユーザーが削除されました'
        ]);
    }

    /**
     * 現在のユーザー情報取得
     */
    public function profile(Request $request)
    {
        return response()->json($request->user()->load(['addresses', 'orders']));
    }

    /**
     * プロフィール更新
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'gender' => 'sometimes|nullable|in:male,female,other',
            'phone' => 'sometimes|nullable|string|max:20',
            'birthdate' => 'sometimes|nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['name', 'email', 'gender', 'phone', 'birthdate']));

        return response()->json([
            'message' => 'プロフィールが更新されました',
            'user' => $user
        ]);
    }
}