<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $farmers = User::role('petani')->get();
        if ($farmers->isEmpty()) {
            return;
        }

        // [nama, kategori, harga, stok, satuan, berat(gram)]
        $products = [
            ['Bayam Hijau', 'Sayuran', 5000, 50, 'ikat', 250],
            ['Kangkung', 'Sayuran', 4000, 8, 'ikat', 250],
            ['Tomat Merah', 'Sayuran', 12000, 40, 'kg', 1000],
            ['Mangga Harum Manis', 'Buah-buahan', 25000, 30, 'kg', 1000],
            ['Pisang Cavendish', 'Buah-buahan', 18000, 5, 'sisir', 1200],
            ['Beras Pandan Wangi', 'Beras & Biji-bijian', 15000, 200, 'kg', 1000],
            ['Jagung Manis', 'Beras & Biji-bijian', 9000, 60, 'kg', 1000],
            ['Cabai Merah Keriting', 'Rempah & Bumbu', 45000, 25, 'kg', 1000],
            ['Jahe Merah', 'Rempah & Bumbu', 30000, 15, 'kg', 1000],
            ['Singkong', 'Umbi-umbian', 6000, 100, 'kg', 1000],
        ];

        foreach ($products as $i => [$name, $category, $price, $stock, $unit, $weight]) {
            Product::updateOrCreate(['slug' => Str::slug($name)], [
                'user_id' => $farmers[$i % $farmers->count()]->id,
                'category_id' => Category::where('name', $category)->value('id'),
                'name' => $name,
                'description' => "{$name} segar langsung dari petani lokal.",
                'price' => $price,
                'stock' => $stock,
                'unit' => $unit,
                'weight' => $weight,
                'is_active' => true,
            ]);
        }
    }
}
