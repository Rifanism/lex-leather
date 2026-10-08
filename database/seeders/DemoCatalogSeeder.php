<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo catalog so the storefront has something to show. Prices are realistic
 * rupiah integers. Images fall back to the committed placeholder because the
 * repo ships no real product photos.
 */
class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Tas', 'Ransel Opus Kulit Sapi', 'genuine', 1_250_000, 12,
                'Ransel kerja dari kulit sapi vegetable-tanned setebal 1,8 mm. Satu kompartemen laptop 15 inci, dua kantong ritsleting dalam, dan tali bahu yang bisa diatur tinggi.'],
            ['Tas', 'Tas Selipar Kulit Troulli', 'genuine', 875_000, 7,
                'Selipar kulit dengan sol karet yang bisa diganti terpisah, jadi awet dipakai lama.'],
            ['Tas', 'Tote Saku Depan Retro', 'synthetic', 320_000, 25,
                'Tote besar untuk belanja harian, muat laptop dan satu botol air.'],
            ['Dompet', 'Dompet Panjang Bifold', 'genuine', 285_000, 30,
                'Bifold delapan slot kartu dan satu compartment uang tunai. Stitching tangan di tepi lipatan.'],
            ['Dompet', 'Dompet Koin Kancing Tembaga', 'genuine', 195_000, 18,
                'Dompet koin ringkas dengan kancing tembaga yang tidak mudah berkarat.'],
            ['Dompet', 'Dompet Kartu Anti Air', 'synthetic', 89_000, 40,
                'Kulit sintetis anti air dengan pelindung RFID untuk kartu kredit.'],
            ['Aksesori', 'Sabuk Kulit Kanvas 4cm', 'genuine', 240_000, 22,
                'Sabuk berbuckle kuningan, ukuran bisa diatur dari 85 sampai 110 cm.'],
            ['Aksesori', 'Dompet Koper Paspor', 'genuine', 165_000, 15,
                'Dompet yang muat paspor, tiket, dan kartu, tali elastis di tengahnya.'],
            ['Aksesori', 'Tas Kancing Saku', 'synthetic', 149_000, 0,
                'Kantong kecil berkancing untuk kunci dan earphone.'],
        ];

        foreach ($rows as [$categoryName, $name, $material, $price, $stock, $description]) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName],
            );

            $slug = Str::slug($name);

            Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'material' => $material,
                    'price' => $price,
                    'stock' => $stock,
                    'image' => Product::PLACEHOLDER_IMAGE,
                    'is_active' => true,
                ],
            );
        }
    }
}
