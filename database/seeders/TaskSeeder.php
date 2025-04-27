<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Support\Str;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();
        
        if ($projects->isEmpty()) {
            $this->command->info('No projects found. Please run the ProjectSeeder first.');
            return;
        }

        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        
        foreach ($projects as $project) {
            // Create 3-6 tasks per project
            $taskCount = rand(3, 6);
            
            for ($i = 1; $i <= $taskCount; $i++) {
                $isPriority = $i <= 2; // Make the first two tasks priority
                $status = $statuses[array_rand($statuses)];
                $dueDate = $project->end_date ? 
                    (clone $project->end_date)->subDays(rand(5, 30)) : 
                    now()->addDays(rand(1, 30));
                
                // Ensure due date is after start date
                if ($project->start_date && $dueDate < $project->start_date) {
                    $dueDate = (clone $project->start_date)->addDays(rand(1, 15));
                }
                
                // Generate task data
                $taskData = [
                    'uuid' => (string) Str::uuid(),
                    'title' => $this->generateTaskTitle($project, $i),
                    'description' => $this->generateTaskDescription($project, $i),
                    'project_id' => $project->id,
                    'status' => $status,
                    'is_priority' => $isPriority,
                    'due_date' => $dueDate,
                    'metadata' => json_encode([
                        'complexity' => rand(1, 5),
                        'estimated_hours' => rand(2, 40),
                        'assigned_to' => $this->getRandomAssignee(),
                    ]),
                ];
                
                Task::create($taskData);
            }
        }
    }
    
    /**
     * Generate a task title based on project and task number
     */
    private function generateTaskTitle($project, $taskNumber)
    {
        $taskTypes = [
            'Design' => ['Create wireframes', 'Design UI components', 'Finalize color scheme', 'Create mockups', 'Design user flow', 'Create style guide'],
            'Development' => ['Setup project structure', 'Implement authentication', 'Create database models', 'Develop API endpoints', 'Implement frontend components', 'Setup CI/CD pipeline'],
            'Testing' => ['Write unit tests', 'Perform integration testing', 'Conduct user acceptance testing', 'Test performance', 'Security audit', 'Cross-browser testing'],
            'Documentation' => ['Create user documentation', 'Write API documentation', 'Document code', 'Create training materials', 'Prepare release notes'],
            'Deployment' => ['Configure server', 'Setup database', 'Deploy to staging', 'Deploy to production', 'Monitor performance']
        ];
        
        $taskType = array_rand($taskTypes);
        $tasks = $taskTypes[$taskType];
        
        return $tasks[array_rand($tasks)] . ' for ' . $project->name;
    }
    
    /**
     * Generate a task description based on project and task number
     */
    private function generateTaskDescription($project, $taskNumber)
    {
        $descriptions = [
            "This task involves working on {$project->name} to ensure all requirements are met according to the project specifications.",
            "As part of the {$project->name} project, this task requires attention to detail and collaboration with the team.",
            "Complete this task for {$project->name} following the established guidelines and best practices.",
            "This is a crucial component of the {$project->name} project that needs to be completed on time.",
            "Work on this task to contribute to the successful delivery of the {$project->name} project.",
            "This task is part of the development process for {$project->name} and requires technical expertise."
        ];
        
        return $descriptions[array_rand($descriptions)];
    }
    
    /**
     * Get a random assignee name
     */
    private function getRandomAssignee()
    {
        $assignees = [
            'John Smith',
            'Jane Doe',
            'Robert Johnson',
            'Emily Williams',
            'Michael Brown',
            'Sarah Davis',
            'David Miller',
            'Lisa Wilson',
            'Unassigned'
        ];
        
        return $assignees[array_rand($assignees)];
    }
}
