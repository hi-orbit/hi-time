<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class ProjectTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $user = User::where('role', 'user')->first();
        $contractor = User::where('role', 'contractor')->first();

        // Create sample projects
        $project1 = Project::create([
            'name' => 'Website Redesign',
            'description' => 'Complete redesign of the company website',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $project2 = Project::create([
            'name' => 'Mobile App Development',
            'description' => 'Development of iOS and Android mobile application',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $project3 = Project::create([
            'name' => 'Database Migration',
            'description' => 'Migrate legacy database to new system',
            'status' => 'completed',
            'created_by' => $admin->id,
        ]);

        // Create sample tasks for Website Redesign
        Task::create([
            'title' => 'Create wireframes',
            'description' => 'Design initial wireframes for all pages',
            'status' => 'done',
            'project_id' => $project1->id,
            'assigned_to' => $user->id,
            'created_by' => $admin->id,
            'order' => 1,
        ]);

        Task::create([
            'title' => 'Design homepage',
            'description' => 'Create visual design for the homepage',
            'status' => 'in_progress',
            'project_id' => $project1->id,
            'assigned_to' => $user->id,
            'created_by' => $admin->id,
            'order' => 2,
        ]);

        Task::create([
            'title' => 'Implement responsive design',
            'description' => 'Make the website mobile-friendly',
            'status' => 'backlog',
            'project_id' => $project1->id,
            'assigned_to' => $contractor->id,
            'created_by' => $admin->id,
            'order' => 3,
        ]);

        Task::create([
            'title' => 'Test on multiple browsers',
            'description' => 'Ensure compatibility across all major browsers',
            'status' => 'backlog',
            'project_id' => $project1->id,
            'assigned_to' => null,
            'created_by' => $admin->id,
            'order' => 4,
        ]);

        // Create sample tasks for Mobile App
        Task::create([
            'title' => 'Setup development environment',
            'description' => 'Configure React Native development environment',
            'status' => 'done',
            'project_id' => $project2->id,
            'assigned_to' => $contractor->id,
            'created_by' => $admin->id,
            'order' => 1,
        ]);

        Task::create([
            'title' => 'Create user authentication',
            'description' => 'Implement login and registration functionality',
            'status' => 'in_test',
            'project_id' => $project2->id,
            'assigned_to' => $contractor->id,
            'created_by' => $admin->id,
            'order' => 2,
        ]);

        Task::create([
            'title' => 'Build dashboard screen',
            'description' => 'Create the main dashboard interface',
            'status' => 'ready_to_release',
            'project_id' => $project2->id,
            'assigned_to' => $user->id,
            'created_by' => $admin->id,
            'order' => 3,
        ]);
    }
}
