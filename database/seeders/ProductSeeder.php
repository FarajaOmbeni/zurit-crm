<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Pension Scheme Consultation',
                'price' => 50000.00,
                'category' => 'Consultation',
                'description' => 'Comprehensive pension scheme consultation and advisory services',
                'is_active' => true,
            ],
            [
                'name' => 'Retirement Planning Package',
                'price' => 75000.00,
                'category' => 'Planning',
                'description' => 'Complete retirement planning and strategy development',
                'is_active' => true,
            ],
            [
                'name' => 'Pension Fund Management',
                'price' => 100000.00,
                'category' => 'Management',
                'description' => 'Ongoing pension fund management and administration',
                'is_active' => true,
            ],
            [
                'name' => 'Employee Benefits Package',
                'price' => 125000.00,
                'category' => 'Benefits',
                'description' => 'Comprehensive employee benefits package design and implementation',
                'is_active' => true,
            ],
            [
                'name' => 'Pension Scheme Audit',
                'price' => 60000.00,
                'category' => 'Audit',
                'description' => 'Pension scheme compliance audit and review',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
