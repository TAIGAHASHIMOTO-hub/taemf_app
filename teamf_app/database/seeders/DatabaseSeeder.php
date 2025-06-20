<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ParentCategory;
use App\Models\ChildrenCategory;
use App\Models\Dress;
use App\Models\DressImage;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 管理者ユーザー作成
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
        ]);

        // 一般ユーザー作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'gender' => 'female',
            'phone' => '090-1234-5678',
            'is_admin' => false,
        ]);

        // 親カテゴリ作成
        $formalCategory = ParentCategory::create(['name' => 'フォーマルドレス']);
        $casualCategory = ParentCategory::create(['name' => 'カジュアルドレス']);
        $weddingCategory = ParentCategory::create(['name' => 'ウェディングドレス']);

        // 子カテゴリ作成
        $eveningDress = ChildrenCategory::create([
            'parent_category_id' => $formalCategory->id,
            'name' => 'イブニングドレス'
        ]);

        $cocktailDress = ChildrenCategory::create([
            'parent_category_id' => $formalCategory->id,
            'name' => 'カクテルドレス'
        ]);

        $dailyDress = ChildrenCategory::create([
            'parent_category_id' => $casualCategory->id,
            'name' => 'デイリードレス'
        ]);

        $summerDress = ChildrenCategory::create([
            'parent_category_id' => $casualCategory->id,
            'name' => 'サマードレス'
        ]);

        $aLineDress = ChildrenCategory::create([
            'parent_category_id' => $weddingCategory->id,
            'name' => 'Aライン'
        ]);

        // ドレス作成
        $dresses = [
            [
                'children_category_id' => $eveningDress->id,
                'name' => 'エレガントブラックドレス',
                'description' => '上品なブラックのイブニングドレス。特別な夜にぴったりです。',
                'price' => 58000,
                'stock' => 5,
                'images' => [
                    'https://via.placeholder.com/400x600/000000/FFFFFF?text=Black+Dress+1',
                    'https://via.placeholder.com/400x600/000000/FFFFFF?text=Black+Dress+2'
                ]
            ],
            [
                'children_category_id' => $cocktailDress->id,
                'name' => 'シャンパンカラーカクテルドレス',
                'description' => 'パーティーに最適なシャンパンカラーのドレス。',
                'price' => 42000,
                'stock' => 8,
                'images' => [
                    'https://via.placeholder.com/400x600/F7E7CE/000000?text=Champagne+Dress+1',
                    'https://via.placeholder.com/400x600/F7E7CE/000000?text=Champagne+Dress+2'
                ]
            ],
            [
                'children_category_id' => $dailyDress->id,
                'name' => 'コットンマキシドレス',
                'description' => '着心地抜群のコットン素材のマキシドレス。',
                'price' => 12800,
                'stock' => 15,
                'images' => [
                    'https://via.placeholder.com/400x600/87CEEB/000000?text=Cotton+Maxi+1',
                    'https://via.placeholder.com/400x600/87CEEB/000000?text=Cotton+Maxi+2'
                ]
            ],
            [
                'children_category_id' => $summerDress->id,
                'name' => 'フローラルサマードレス',
                'description' => '夏にぴったりの花柄ドレス。軽やかで涼しげです。',
                'price' => 9800,
                'stock' => 20,
                'images' => [
                    'https://via.placeholder.com/400x600/FFB6C1/000000?text=Floral+Summer+1',
                    'https://via.placeholder.com/400x600/FFB6C1/000000?text=Floral+Summer+2'
                ]
            ],
            [
                'children_category_id' => $aLineDress->id,
                'name' => 'クラシックAラインウェディングドレス',
                'description' => '伝統的で美しいAラインのウェディングドレス。',
                'price' => 180000,
                'stock' => 3,
                'images' => [
                    'https://via.placeholder.com/400x600/FFFFFF/000000?text=Wedding+A-Line+1',
                    'https://via.placeholder.com/400x600/FFFFFF/000000?text=Wedding+A-Line+2'
                ]
            ],
            [
                'children_category_id' => $eveningDress->id,
                'name' => 'ロイヤルブルーイブニングドレス',
                'description' => '華やかなロイヤルブルーのロングドレス。',
                'price' => 68000,
                'stock' => 4,
                'images' => [
                    'https://via.placeholder.com/400x600/4169E1/FFFFFF?text=Royal+Blue+1',
                    'https://via.placeholder.com/400x600/4169E1/FFFFFF?text=Royal+Blue+2'
                ]
            ]
        ];

        foreach ($dresses as $dressData) {
            $images = $dressData['images'];
            unset($dressData['images']);

            $dress = Dress::create($dressData);

            // 画像を作成
            foreach ($images as $index => $imageUrl) {
                DressImage::create([
                    'dresses_id' => $dress->id,
                    'image_url' => $imageUrl,
                    'sort_order' => $index
                ]);
            }
        }

        echo "シーダー実行完了！\n";
        echo "管理者ユーザー: admin@example.com / password123\n";
        echo "一般ユーザー: test@example.com / password123\n";
        echo "商品数: " . Dress::count() . "件\n";
    }
}
