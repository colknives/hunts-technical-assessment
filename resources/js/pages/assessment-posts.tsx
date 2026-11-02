import { Head, Link } from '@inertiajs/react';
import { section1, section3 } from '@/routes/assessment';

type Props = {
    posts: { id: number; title: string; author: string | null }[];
    measurement: {
        query_count: number;
        database_ms: number;
        elapsed_ms: number;
        queries: string[];
    };
};

export default function AssessmentPosts({ posts, measurement }: Props) {
    return (
        <main className="min-h-screen bg-stone-50 p-6 text-stone-900 dark:bg-stone-950 dark:text-stone-100">
            <Head title="Section 1 - Code review" />
            <div className="mx-auto flex max-w-4xl flex-col gap-6">
                <nav className="flex flex-wrap gap-5 text-sm underline underline-offset-4">
                    <Link href={section1()} aria-current="page">
                        Section 1: Code review
                    </Link>
                    <Link href={section3()}>Section 3: Refactoring</Link>
                </nav>
                <header className="flex flex-col gap-2">
                    <p className="text-sm text-stone-500 dark:text-stone-400">
                        Assessment starter · Section 1
                    </p>
                    <h1 className="text-3xl font-semibold">
                        The 50 most recent posts
                    </h1>
                    <p>
                        Review and improve the query in{' '}
                        <code>PostController::index()</code>. This is the
                        unsolved starting point.
                    </p>
                </header>
                <div className="grid gap-4 sm:grid-cols-3">
                    {[
                        ['Database queries', measurement.query_count],
                        ['Database time (ms)', measurement.database_ms],
                        ['Fetch + mapping time (ms)', measurement.elapsed_ms],
                    ].map(([label, value]) => (
                        <div
                            key={label}
                            className="rounded-lg border border-stone-300 p-4 dark:border-stone-700"
                        >
                            <p className="text-sm text-stone-500 dark:text-stone-400">
                                {label}
                            </p>
                            <p className="mt-2 text-2xl font-semibold tabular-nums">
                                {value}
                            </p>
                        </div>
                    ))}
                </div>
                <p className="text-sm text-stone-500 dark:text-stone-400">
                    Measurements include only fetching and mapping these posts,
                    not the full page request. Timings vary between runs.
                </p>
                <Link
                    href={section1()}
                    className="w-fit rounded border border-stone-400 px-4 py-2 text-sm"
                >
                    Run again
                </Link>
                <details className="rounded-lg border border-stone-300 p-4 dark:border-stone-700">
                    <summary className="cursor-pointer font-medium">
                        Inspect executed SQL
                    </summary>
                    <ol className="mt-4 flex list-inside list-decimal flex-col gap-2 text-xs">
                        {measurement.queries.map((query, index) => (
                            <li
                                key={`${index}-${query}`}
                                className="overflow-x-auto"
                            >
                                <code>{query}</code>
                            </li>
                        ))}
                    </ol>
                </details>
                {posts.length === 0 ? (
                    <p className="rounded-lg border border-dashed border-stone-400 p-6">
                        No posts yet. Run{' '}
                        <code>
                            php artisan db:seed --class=AssessmentSeeder
                        </code>
                        .
                    </p>
                ) : (
                    <div className="overflow-x-auto rounded-lg border border-stone-300 dark:border-stone-700">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-stone-200/60 dark:bg-stone-800">
                                <tr>
                                    <th className="px-4 py-3">Post</th>
                                    <th className="px-4 py-3">Author</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-stone-200 dark:divide-stone-800">
                                {posts.map((post) => (
                                    <tr key={post.id}>
                                        <td className="px-4 py-3">
                                            {post.title}
                                        </td>
                                        <td className="px-4 py-3">
                                            {post.author ?? 'Unknown author'}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </main>
    );
}
