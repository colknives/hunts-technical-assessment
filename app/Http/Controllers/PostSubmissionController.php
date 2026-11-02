<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Post;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostSubmissionController extends Controller
{
    public function create(Request $request): Response
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        return Inertia::render('assessment-submission', [
            'authors' => Author::query()->orderBy('name')->get(['id', 'name']),
            'status' => $request->session()->get('assessment_status'),
        ]);
    }

    /**
     * Section 3 starting point: validation, business rules, HTTP, and persistence
     * deliberately live together so they can be refactored during the assessment.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $validated = $request->validate([
            'author_id' => ['required', 'integer', 'exists:authors,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
        ]);

        $title = Str::squish($validated['title']);
        $content = trim($validated['content']);
        $author = Author::query()->findOrFail($request->integer('author_id'));

        try {
            $response = Http::connectTimeout(2)->timeout(5)
                ->post('https://publisher.example.test/posts', [
                    'title' => $title,
                    'content' => $content,
                    'author' => $author->name,
                ]);
        } catch (ConnectionException) {
            return back()->withInput()->withErrors([
                'publishing' => 'The publishing service is unavailable. Please try again.',
            ]);
        }

        if (! $response->successful()) {
            return back()->withInput()->withErrors([
                'publishing' => 'The publishing service rejected the post. Please try again.',
            ]);
        }

        $post = new Post;
        $post->author()->associate($author);
        $post->title = $title;
        $post->content = $content;
        $post->save();

        return to_route('assessment.section3')->with('assessment_status', 'Post created using the simulated publishing API.');
    }
}
