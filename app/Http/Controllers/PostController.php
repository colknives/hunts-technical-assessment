<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        abort_unless(app()->environment('local', 'testing'), 404);

        $connection = DB::connection();
        $wasLogging = $connection->logging();
        $queryOffset = count($connection->getQueryLog());
        $connection->enableQueryLog();
        $startedAt = hrtime(true);

        try {
            $posts = Post::query()
                ->select(['id', 'title', 'author_id'])
                ->with('author')
                ->latest()
                ->take(50)
                ->get();

            $rows = $posts->map(fn (Post $post): array => [
                'id' => $post->id,
                'title' => $post->title,
                'author' => $post->author->name,
            ]);

            $elapsedMilliseconds = (hrtime(true) - $startedAt) / 1_000_000;
            $queries = array_slice($connection->getQueryLog(), $queryOffset);
        } finally {
            if (! $wasLogging) {
                $connection->disableQueryLog();
                $connection->flushQueryLog();
            }
        }

        return Inertia::render('assessment-posts', [
            'posts' => $rows,
            'measurement' => [
                'query_count' => count($queries),
                'database_ms' => round(array_sum(array_column($queries, 'time')), 3),
                'elapsed_ms' => round($elapsedMilliseconds, 3),
                'queries' => array_column($queries, 'query'),
            ],
        ]);
    }
}
