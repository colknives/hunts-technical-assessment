<?php

use App\Models\Author;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('renders the submission form with available authors', function () {
    $author = Author::factory()->create();

    $response = $this->get(route('assessment.section3'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('assessment-submission')
        ->has('authors', 1)
        ->where('authors.0.id', $author->id)
        ->where('authors.0.name', $author->name)
    );
});

test('normalizes and publishes a post and saves only intended fields', function () {
    Http::fake(['https://publisher.example.test/posts' => Http::response(['accepted' => true], 201)]);
    $author = Author::factory()->create(['name' => 'Ada Example']);

    $response = $this->post(route('assessment.submit'), [
        'author_id' => $author->id,
        'title' => '  My   first   post  ',
        'content' => '  Article body.  ',
        'id' => 99999,
    ]);

    $response->assertRedirectToRoute('assessment.section3')
        ->assertSessionHas('assessment_status', 'Post created using the simulated publishing API.');
    $this->assertDatabaseHas('posts', ['author_id' => $author->id, 'title' => 'My first post', 'content' => 'Article body.']);
    $this->assertDatabaseMissing('posts', ['id' => 99999]);
    $this->assertDatabaseCount('posts', 1);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://publisher.example.test/posts'
        && $request->method() === 'POST'
        && $request->data() === ['title' => 'My first post', 'content' => 'Article body.', 'author' => 'Ada Example']);
    Http::assertSentCount(1);
});

test('rejects missing fields before publishing or saving', function () {
    $response = $this->post(route('assessment.submit'), []);

    $response->assertSessionHasErrors([
        'author_id' => 'The author id field is required.',
        'title' => 'The title field is required.',
        'content' => 'The content field is required.',
    ]);
    $this->assertDatabaseCount('posts', 0);
    Http::assertNothingSent();
});

test('rejects invalid fields before publishing or saving', function (string $field, mixed $value, string $message) {
    $author = Author::factory()->create();
    $payload = ['author_id' => $author->id, 'title' => 'Valid title', 'content' => 'Valid content'];
    $payload[$field] = $value;

    $response = $this->post(route('assessment.submit'), $payload);

    $response->assertSessionHasErrors([$field => $message]);
    $this->assertDatabaseCount('posts', 0);
    Http::assertNothingSent();
})->with([
    'missing author' => ['author_id', 99999, 'The selected author id is invalid.'],
    'noninteger author' => ['author_id', 'invalid', 'The author id field must be an integer.'],
    'nonstring title' => ['title', ['invalid'], 'The title field must be a string.'],
    'long title' => ['title', str_repeat('a', 256), 'The title field must not be greater than 255 characters.'],
    'nonstring content' => ['content', ['invalid'], 'The content field must be a string.'],
    'long content' => ['content', str_repeat('a', 10001), 'The content field must not be greater than 10000 characters.'],
]);

test('does not save a post when publishing is rejected', function () {
    Http::fake(['https://publisher.example.test/posts' => Http::response([], 503)]);
    $author = Author::factory()->create();

    $response = $this->from(route('assessment.section3'))->post(route('assessment.submit'), [
        'author_id' => $author->id, 'title' => 'My post', 'content' => 'Article body.',
    ]);

    $response->assertRedirectToRoute('assessment.section3')
        ->assertSessionHasErrors(['publishing' => 'The publishing service rejected the post. Please try again.']);
    $this->assertDatabaseCount('posts', 0);
    Http::assertSentCount(1);
});

test('does not save a post when the publishing connection fails', function () {
    Http::fake(['https://publisher.example.test/posts' => Http::failedConnection()]);
    $author = Author::factory()->create();

    $response = $this->from(route('assessment.section3'))->post(route('assessment.submit'), [
        'author_id' => $author->id, 'title' => 'My post', 'content' => 'Article body.',
    ]);

    $response->assertRedirectToRoute('assessment.section3')
        ->assertSessionHasErrors(['publishing' => 'The publishing service is unavailable. Please try again.']);
    $this->assertDatabaseCount('posts', 0);
});

test('does not expose the submission form or allow submissions in production', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
    $this->app->instance('env', 'production');

    $this->get(route('assessment.section3'))->assertNotFound();
    $this->post(route('assessment.submit'), [])->assertNotFound();

    $this->assertDatabaseCount('posts', 0);
    Http::assertNothingSent();
});
