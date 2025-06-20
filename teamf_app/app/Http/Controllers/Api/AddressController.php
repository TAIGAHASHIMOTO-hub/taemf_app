<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * ユーザーの住所一覧取得
     */
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->orderBy('is_default', 'desc')->get();

        return response()->json($addresses);
    }

    /**
     * 住所詳細取得
     */
    public function show(Request $request, $id)
    {
        $address = Address::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($address);
    }

    /**
     * 住所登録
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'postal_code' => 'required|string|size:7',
            'prefecture' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address1' => 'required|string|max:255',
            'address2' => 'nullable|string|max:255',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $address = $request->user()->addresses()->create($request->all());

        // デフォルト住所に設定する場合
        if ($request->is_default) {
            $address->setAsDefault();
        }

        return response()->json([
            'message' => '住所が正常に登録されました',
            'address' => $address
        ], 201);
    }

    /**
     * 住所更新
     */
    public function update(Request $request, $id)
    {
        $address = Address::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'postal_code' => 'sometimes|string|size:7',
            'prefecture' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:255',
            'address1' => 'sometimes|string|max:255',
            'address2' => 'sometimes|nullable|string|max:255',
            'is_default' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $address->update($request->only([
            'postal_code',
            'prefecture',
            'city',
            'address1',
            'address2'
        ]));

        // デフォルト住所の変更
        if ($request->has('is_default') && $request->is_default) {
            $address->setAsDefault();
        }

        return response()->json([
            'message' => '住所が更新されました',
            'address' => $address->fresh()
        ]);
    }

    /**
     * 住所削除
     */
    public function destroy(Request $request, $id)
    {
        $address = Address::where('user_id', $request->user()->id)
            ->findOrFail($id);

        // 注文で使用されている住所は削除不可
        if ($address->orders()->count() > 0) {
            return response()->json([
                'message' => '注文で使用されているため削除できません'
            ], 400);
        }

        $address->delete();

        return response()->json([
            'message' => '住所が削除されました'
        ]);
    }

    /**
     * デフォルト住所に設定
     */
    public function setDefault(Request $request, $id)
    {
        $address = Address::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $address->setAsDefault();

        return response()->json([
            'message' => 'デフォルト住所が設定されました',
            'address' => $address->fresh()
        ]);
    }
}
