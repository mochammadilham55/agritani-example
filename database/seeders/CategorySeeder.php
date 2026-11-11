<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Sayuran' => 'Sayuran segar langsung dari kebun petani.',
            'Buah-buahan' => 'Buah segar musiman pilihan.',
            'Beras & Biji-bijian' => 'Beras, jagung, kacang, dan biji-bijian lainnya.',
            'Rempah & Bumbu' => 'Cabai, bawang, jahe, kunyit, dan rempah lain.',
            'Umbi-umbian' => 'Singkong, ubi, kentang, dan sejenisnya.',
        ];

        foreach ($items as $name => $description) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description],
            );
        }
    }
}
