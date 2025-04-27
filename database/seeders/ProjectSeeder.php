<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Category;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->command->info('No categories found. Please run the CategorySeeder first.');
            return;
        }

        $projects = [
            [
                'name' => 'E-commerce Website',
                'description' => 'Build a full-featured e-commerce website with product catalog, cart, and payment integration',
                'is_active' => true,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addMonths(2),
                'metadata' => json_encode(['priority' => 'high', 'client' => 'ABC Corp', 'budget' => 15000]),
            ],
            [
                'name' => 'CRM System',
                'description' => 'Customer relationship management system with contact management and sales tracking',
                'is_active' => true,
                'start_date' => now()->subDays(15),
                'end_date' => now()->addMonths(3),
                'metadata' => json_encode(['priority' => 'medium', 'client' => 'XYZ Inc', 'budget' => 20000]),
            ],
            [
                'name' => 'Mobile Banking App',
                'description' => 'Develop a secure mobile banking application for iOS and Android',
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonths(4),
                'metadata' => json_encode(['priority' => 'high', 'client' => 'First Bank', 'budget' => 35000]),
            ],
            [
                'name' => 'Portfolio Website Redesign',
                'description' => 'Redesign the company portfolio website with modern UI/UX principles',
                'is_active' => true,
                'start_date' => now()->addDays(15),
                'end_date' => now()->addMonths(1),
                'metadata' => json_encode(['priority' => 'low', 'client' => 'Design Studio', 'budget' => 8000]),
            ],
            [
                'name' => 'Inventory Management System',
                'description' => 'Build an inventory tracking and management system for warehouse operations',
                'is_active' => false,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->subDays(15),
                'metadata' => json_encode(['priority' => 'medium', 'client' => 'Logistics Co', 'budget' => 12000]),
            ],
            [
                'name' => 'AI Chatbot Integration',
                'description' => 'Integrate an AI-powered chatbot for customer support on the company website',
                'is_active' => true,
                'start_date' => now()->addDays(30),
                'end_date' => now()->addMonths(2),
                'metadata' => json_encode(['priority' => 'medium', 'client' => 'Tech Solutions', 'budget' => 18000]),
            ],
            [
                'name' => 'HR Management Portal',
                'description' => 'Develop an HR portal for employee management, time tracking, and performance reviews',
                'is_active' => true,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addMonths(3),
                'metadata' => json_encode(['priority' => 'high', 'client' => 'Corporate Services', 'budget' => 25000]),
            ],
            [
                'name' => 'Data Visualization Dashboard',
                'description' => 'Create an interactive dashboard for visualizing business analytics and KPIs',
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonths(2),
                'metadata' => json_encode(['priority' => 'medium', 'client' => 'Analytics Inc', 'budget' => 15000]),
            ],
        ];

        foreach ($projects as $projectData) {
            // Assign a random category
            $category = $categories->random();
            
            Project::create([
                'uuid' => (string) Str::uuid(),
                'name' => $projectData['name'],
                'description' => $projectData['description'],
                'category_id' => $category->id,
                'is_active' => $projectData['is_active'],
                'start_date' => $projectData['start_date'],
                'end_date' => $projectData['end_date'],
                'metadata' => $projectData['metadata'],
            ]);
        }
    }
}
