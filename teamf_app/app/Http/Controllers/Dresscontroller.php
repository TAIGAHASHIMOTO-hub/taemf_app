<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dress;
use App\Models\ChildrenCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DressController extends Controller
{
    /**
     * ドレス一覧取得
     */
    public function index(Request $request)
    {
        $query = Dress::with(['childrenCategory.parentCategory', 'images']);

        // 検索フィルター
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%");
        }

        // カテゴリフィルター
        if ($request->category_id) {
            $query->byCategory($request->category_id);
        }

        // 価格帯フィルター
        if ($request->min_price || $request->max_price) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        // 在庫フィルター
        if ($request->in_stock_only) {
            $query->inStock();
        }

        // ソート
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $dresses = $query->paginate($request->per_page ?? 20);

        return response()->json($dresses);
    }

    /**
     * ドレス詳細取得
     */
    public function show($id)
    {
        $dress = Dress::with([
            'childrenCategory.parentCategory',
            'images' => function ($query) {
                $query->orderBy('sort_order');
            }
        ])->findOrFail($id);

        return response()->json($dress);
    }

    /**
     * ドレス登録（管理者のみ）
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'children_category_id' => 'required|exists:children_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'string', // 画像URLの配列
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $dress = Dress::create($request->only([
            'children_category_id',
            'name',
            'description',
            'price',
            'stock'
        ]));

        // 画像登録
        if ($request->has('images')) {
            foreach ($request->images as $index => $imageUrl) {
                $dress->images()->create([
                    'image_url' => $imageUrl,
                    'sort_order' => $index
                ]);
            }
        }

        return response()->json([
            'message' => 'ドレスが正常に登録されました',
            'dress' => $dress->load('images')
        ], 201);
    }

    /**
     * ドレス更新（管理者のみ）
     */
    public function update(Request $request, $id)
    {
        $dress = Dress::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'children_category_id' => 'sometimes|exists:children_categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $dress->update($request->only([
            'children_category_id',
            'name',
            'description',
            'price',
            'stock'
        ]));

        return response()->json([
            'message' => 'ドレス情報が更新されました',
            'dress' => $dress->fresh(['childrenCategory', 'images'])
        ]);
    }

    /**
     * ドレス削除（管理者のみ）
     */
    public function destroy($id)
    {
        $dress = Dress::findOrFail($id);

        // 注文項目で使用されている場合は削除不可
        if ($dress->orderItems()->count() > 0) {
            return response()->json([
                'message' => '注文履歴があるため削除できません'
            ], 400);
        }

        $dress->delete();

        return response()->json([
            'message' => 'ドレスが削除されました'
        ]);
    }

    /**
     * カテゴリ別ドレス一覧
     */
    public function byCategory($categoryId)
    {
        $category = ChildrenCategory::findOrFail($categoryId);

        $dresses = $category->dresses()
            ->with('images')
            ->inStock()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'category' => $category->load('parentCategory'),
            'dresses' => $dresses
        ]);
    }

    /**
     * 人気商品取得
     */
    public function popular(Request $request)
    {
        $dresses = Dress::with(['childrenCategory', 'images'])
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->inStock()
            ->limit($request->limit ?? 10)
            ->get();

        return response()->json($dresses);
    }

    /**
     * 新着商品取得
     */
    public function latest(Request $request)
    {
        $dresses = Dress::with(['childrenCategory', 'images'])
            ->inStock()
            ->orderBy('created_at', 'desc')
            ->limit($request->limit ?? 10)
            ->get();

        return response()->json($dresses);
    }
}
