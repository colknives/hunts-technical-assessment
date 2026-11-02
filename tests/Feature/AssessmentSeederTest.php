<?php

use App\Models\Author;
use App\Models\Post;
use Database\Seeders\AssessmentSeeder;

test('seeds 100 posts and 10 authors without duplicating data or changing existing posts', function () {
    $this->freezeTime();
    $existingPost = Post::factory()->create(['title' => 'Existing article']);

    $this->seed(AssessmentSeeder::class);
    $this->seed(AssessmentSeeder::class);

    $this->assertDatabaseCount('authors', 11);
    $this->assertDatabaseCount('posts', 101);
    $this->assertDatabaseHas('posts', ['id' => $existingPost->id, 'title' => 'Existing article']);
    expect(Post::query()->whereDoesntHave('author')->count())->toBe(0);
    expect(Author::query()->where('name', 'Assessment Author 1')->firstOrFail()->posts()->count())->toBe(10);
});
