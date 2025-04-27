<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Web Development',
                'description' => 'Projects related to web development including frontend and backend',
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'code', 'color' => 'blue', 'tags' => ['web', 'development', 'programming']]),
            ],
            [
                'name' => 'Mobile Development',
                'description' => 'Projects related to mobile app development for iOS and Android',
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'smartphone', 'color' => 'green', 'tags' => ['mobile', 'app', 'android', 'ios']]),
            ],
            [
                'name' => 'UI/UX Design',
                'description' => 'Design projects including user interface and user experience design',
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'brush', 'color' => 'purple', 'tags' => ['design', 'ui', 'ux', 'creative']]),
            ],
            [
                'name' => 'DevOps',
                'description' => 'Projects related to development operations, CI/CD, and infrastructure',
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'settings', 'color' => 'orange', 'tags' => ['devops', 'infrastructure', 'automation']]),
            ],
            [
                'name' => 'Data Science',
                'description' => 'Projects involving data analysis, machine learning, and AI',
                'is_active' => true,
                'metadata' => json_encode(['icon' => 'analytics', 'color' => 'red', 'tags' => ['data', 'analytics', 'ml', 'ai']]),
            ],
            [
                'name' => 'Quality Assurance',
                'description' => 'Projects focused on testing and quality assurance',
                'is_active' => false,
                'metadata' => json_encode(['icon' => 'check_circle', 'color' => 'teal', 'tags' => ['qa', 'testing', 'quality']]),
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create([
                'uuid' => (string) Str::uuid(),
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'is_active' => $categoryData['is_active'],
                'metadata' => $categoryData['metadata'],
            ]);
        }
    }
}
