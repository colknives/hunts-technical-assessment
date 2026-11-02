<?php

use App\Models\Author;
use App\Models\Post;
use Inertia\Testing\AssertableInertia as Assert;

test('lists only the 50 most recent posts with their authors', function () {
    $this->freezeTime();
    $author = Author::factory()->create(['name' => 'Ada Example']);
    Post::factory()->for($author)->count(50)->create(['created_at' => now()->subDay()]);
    $newest = Post::factory()->for($author)->create(['title' => 'Newest post']);

    $response = $this->get(route('assessment.section1'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('assessment-posts')
        ->has('posts', 50)
        ->where('posts.0.id', $newest->id)
        ->where('posts.0.title', 'Newest post')
        ->where('posts.0.author', 'Ada Example')
        ->has('measurement.query_count')
        ->has('measurement.database_ms')
        ->has('measurement.elapsed_ms')
        ->has('measurement.queries')
    );
});

test('renders an empty list without authors', function () {
    $response = $this->get(route('assessment.section1'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('assessment-posts')
        ->has('posts', 0)
    );
});

test('does not expose the exercise in production', function () {
    $this->app->instance('env', 'production');

    $this->get(route('assessment.section1'))->assertNotFound();
});
