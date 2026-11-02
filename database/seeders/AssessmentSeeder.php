<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Post;
use Illuminate\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($authorNumber = 1; $authorNumber <= 10; $authorNumber++) {
            $author = Author::query()->firstOrCreate(['name' => 'Assessment Author '.$authorNumber]);

            for ($postNumber = 1; $postNumber <= 10; $postNumber++) {
                $sequence = ($authorNumber - 1) * 10 + $postNumber;

                Post::query()->firstOrCreate([
                    'author_id' => $author->id,
                    'title' => 'Assessment post '.$sequence,
                ], [
                    'content' => str_repeat('Sample article content for the assessment. ', 100),
                    'created_at' => now()->subMinutes(101 - $sequence),
                    'updated_at' => now()->subMinutes(101 - $sequence),
                ]);
            }
        }
    }
}
