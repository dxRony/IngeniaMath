<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            MasteryLevelSeeder::class,
            TopicSeeder::class,
            SubtopicComplexitySeeder::class,
            ExerciseLevelSeeder::class,
            ExerciseTypeSeeder::class,
            ExerciseStatusSeeder::class,
            PracticeStatusSeeder::class,
            SessionModeSeeder::class,
            DifficultyLevelSeeder::class,
            RecommendationReasonSeeder::class,
            ResourceTypeSeeder::class,
            ResourceStatusSeeder::class,
            SubtopicSeeder::class,
        ]);
    }
}
