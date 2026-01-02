<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Status;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Seeding default boards and statuses...');

        // Create default boards
        Board::create([
            'name' => 'Feature Requests',
            'slug' => 'feature-requests',
            'color' => 'blue',
            'description' => 'Suggest new features and improvements',
        ]);

        Board::create([
            'name' => 'Bugs',
            'slug' => 'bugs',
            'color' => 'red',
            'description' => 'Report bugs and issues',
        ]);

        // Create default statuses
        Status::create(['name' => 'In Review', 'color' => 'gray', 'show_on_roadmap' => false]);
        Status::create(['name' => 'Planning', 'color' => 'blue', 'show_on_roadmap' => true]);
        Status::create(['name' => 'Building', 'color' => 'purple', 'show_on_roadmap' => true]);
        Status::create(['name' => 'Testing', 'color' => 'pink', 'show_on_roadmap' => true]);
        Status::create(['name' => 'Completed', 'color' => 'emerald', 'show_on_roadmap' => true]);

        $this->command->info('✓ Default boards and statuses created successfully!');
        $this->command->newLine();
        $this->command->info('Next steps:');
        $this->command->info('  1. Register your first user at /register');
        $this->command->info('  2. The first registered user will automatically become an admin');
        $this->command->newLine();
    }
}
