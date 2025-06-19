<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentCategory;
use App\Models\ChildrenCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * 全カテゴリ取得（階層構造）
     */
    public function index()
    {
        $categories = ParentCategory::with(['childrenCategories' => function ($query) {
            $query->withCount('dresses');
        }])->withCount('dresses')->get();

        return response()->json($categories);
    }

    /**
     * 親カテゴリ一覧
     */
    public function parentCategories()
    {
        $parentCategories = ParentCategory::withCount(['dresses', 'childrenCategories'])
            ->orderBy('name')
            ->get();

        return response()->json($parentCategories);
    }

    /**
     * 子カテゴリ一覧
     */
    public function childrenCategories($parentId = null)
    {
        $query = ChildrenCategory::with('parentCategory')->withCount('dresses');

        if ($parentId) {
            $query->where('parent_category_id', $parentId);
        }

        $childrenCategories = $query->orderBy('name')->get();

        return response()->json($childrenCategories);
    }

    /**
     * 親カテゴリ作成（管理者のみ）
     */
    public function storeParent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:parent_categories,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = ParentCategory::create($request->only('name'));

        return response()->json([
            'message' => '親カテゴリが作成されました',
            'category' => $category
        ], 201);
    }

    /**
     * 子カテゴリ作成（管理者のみ）
     */
    public function storeChildren(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parent_category_id' => 'required|exists:parent_categories,id',
            'name' => 'required|string|max:255',
        ]);

        // 同じ親カテゴリ内での名前重複チェック
        $validator->after(function ($validator) use ($request) {
            $exists = ChildrenCategory::where('parent_category_id', $request->parent_category_id)
                ->where('name', $request->name)
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', 'この親カテゴリ内に同じ名前の子カテゴリが既に存在します');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = ChildrenCategory::create($request->only('parent_category_id', 'name'));

        return response()->json([
            'message' => '子カテゴリが作成されました',
            'category' => $category->load('parentCategory')
        ], 201);
    }

    /**
     * 親カテゴリ更新（管理者のみ）
     */
    public function updateParent(Request $request, $id)
    {
        $category = ParentCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:parent_categories,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($request->only('name'));

        return response()->json([
            'message' => '親カテゴリが更新されました',
            'category' => $category
        ]);
    }

    /**
     * 子カテゴリ更新（管理者のみ）
     */
    public function updateChildren(Request $request, $id)
    {
        $category = ChildrenCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'parent_category_id' => 'sometimes|exists:parent_categories,id',
            'name' => 'sometimes|string|max:255',
        ]);

        // 名前の重複チェック
        if ($request->has('name') || $request->has('parent_category_id')) {
            $parentId = $request->parent_category_id ?? $category->parent_category_id;
            $name = $request->name ?? $category->name;

            $validator->after(function ($validator) use ($parentId, $name, $id) {
                $exists = ChildrenCategory::where('parent_category_id', $parentId)
                    ->where('name', $name)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('name', 'この親カテゴリ内に同じ名前の子カテゴリが既に存在します');
                }
            });
        }

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($request->only('parent_category_id', 'name'));

        return response()->json([
            'message' => '子カテゴリが更新されました',
            'category' => $category->fresh('parentCategory')
        ]);
    }

    /**
     * 親カテゴリ削除（管理者のみ）
     */
    public function destroyParent($id)
    {
        $category = ParentCategory::findOrFail($id);

        // 子カテゴリまたは商品がある場合は削除不可
        if ($category->childrenCategories()->count() > 0) {
            return response()->json([
                'message' => '子カテゴリが存在するため削除できません'
            ], 400);
        }

        if ($category->dresses()->count() > 0) {
            return response()->json([
                'message' => '商品が存在するため削除できません'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'message' => '親カテゴリが削除されました'
        ]);
    }

    /**
     * 子カテゴリ削除（管理者のみ）
     */
    public function destroyChildren($id)
    {
        $category = ChildrenCategory::findOrFail($id);

        // 商品がある場合は削除不可
        if ($category->dresses()->count() > 0) {
            return response()->json([
                'message' => '商品が存在するため削除できません'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'message' => '子カテゴリが削除されました'
        ]);
    }

    /**
     * 特定親カテゴリの詳細と子カテゴリ一覧
     */
    public function showParent($id)
    {
        $category = ParentCategory::with(['childrenCategories' => function ($query) {
            $query->withCount('dresses');
        }])->withCount('dresses')->findOrFail($id);

        return response()->json($category);
    }

    /**
     * 特定子カテゴリの詳細
     */
    public function showChildren($id)
    {
        $category = ChildrenCategory::with('parentCategory')
            ->withCount('dresses')
            ->findOrFail($id);

        return response()->json($category);
    }

    /**
     * アクティブな子カテゴリのみ取得（商品があるもの）
     */
    public function activeChildrenCategories($parentId = null)
    {
        $query = ChildrenCategory::with('parentCategory')
            ->whereHas('dresses')
            ->withCount('dresses');

        if ($parentId) {
            $query->where('parent_category_id', $parentId);
        }

        $categories = $query->orderBy('name')->get();

        return response()->json($categories);
    }
}
