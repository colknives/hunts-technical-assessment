import { Form, Head, Link } from '@inertiajs/react';
import { section1, section3, submit } from '@/routes/assessment';

type Props = {
    authors: { id: number; name: string }[];
    status: string | null;
};

export default function AssessmentSubmission({ authors, status }: Props) {
    const inputClass =
        'w-full rounded border border-stone-400 bg-white px-3 py-2 dark:bg-stone-900';

    return (
        <main className="min-h-screen bg-stone-50 p-6 text-stone-900 dark:bg-stone-950 dark:text-stone-100">
            <Head title="Section 3 - Refactoring" />
            <div className="mx-auto flex max-w-2xl flex-col gap-6">
                <nav className="flex flex-wrap gap-5 text-sm underline underline-offset-4">
                    <Link href={section1()}>Section 1: Code review</Link>
                    <Link href={section3()} aria-current="page">
                        Section 3: Refactoring
                    </Link>
                </nav>
                <header className="flex flex-col gap-2">
                    <p className="text-sm text-stone-500 dark:text-stone-400">
                        Assessment starter · Section 3
                    </p>
                    <h1 className="text-3xl font-semibold">
                        Refactor a post submission
                    </h1>
                    <p>
                        <code>PostSubmissionController::store()</code> currently
                        handles validation, business rules, an external API
                        call, and persistence. Refactor it while preserving the
                        behavior.
                    </p>
                </header>
                <aside className="rounded-lg border border-stone-300 p-4 text-sm dark:border-stone-700">
                    <p>
                        The publishing API is simulated locally. No credentials
                        or network calls are needed.
                    </p>
                    <p className="mt-2">
                        Use this example to discuss OOP/SOLID, dependency
                        management, alternative designs, and trade-offs. The
                        design decisions are yours to make.
                    </p>
                </aside>
                {status && (
                    <p
                        role="status"
                        className="rounded-lg bg-green-100 p-4 text-green-900 dark:bg-green-950 dark:text-green-100"
                    >
                        {status}
                    </p>
                )}
                {authors.length === 0 && (
                    <p>
                        Run{' '}
                        <code>
                            php artisan db:seed --class=AssessmentSeeder
                        </code>{' '}
                        to create sample authors.
                    </p>
                )}
                <Form
                    action={submit()}
                    resetOnSuccess
                    className="flex flex-col gap-5"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="flex flex-col gap-2">
                                <label htmlFor="author_id">Author</label>
                                <select
                                    id="author_id"
                                    name="author_id"
                                    required
                                    defaultValue=""
                                    className={inputClass}
                                    aria-describedby={
                                        errors.author_id
                                            ? 'author-error'
                                            : undefined
                                    }
                                    aria-invalid={!!errors.author_id}
                                >
                                    <option value="" disabled>
                                        Select an author
                                    </option>
                                    {authors.map((author) => (
                                        <option
                                            key={author.id}
                                            value={author.id}
                                        >
                                            {author.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.author_id && (
                                    <p
                                        id="author-error"
                                        className="text-sm text-red-600 dark:text-red-400"
                                    >
                                        {errors.author_id}
                                    </p>
                                )}
                            </div>
                            <div className="flex flex-col gap-2">
                                <label htmlFor="title">Title</label>
                                <input
                                    id="title"
                                    name="title"
                                    required
                                    maxLength={255}
                                    className={inputClass}
                                    aria-describedby={
                                        errors.title ? 'title-error' : undefined
                                    }
                                    aria-invalid={!!errors.title}
                                />
                                {errors.title && (
                                    <p
                                        id="title-error"
                                        className="text-sm text-red-600 dark:text-red-400"
                                    >
                                        {errors.title}
                                    </p>
                                )}
                            </div>
                            <div className="flex flex-col gap-2">
                                <label htmlFor="content">Content</label>
                                <textarea
                                    id="content"
                                    name="content"
                                    required
                                    maxLength={10000}
                                    rows={6}
                                    className={inputClass}
                                    aria-describedby={
                                        errors.content
                                            ? 'content-error'
                                            : undefined
                                    }
                                    aria-invalid={!!errors.content}
                                />
                                {errors.content && (
                                    <p
                                        id="content-error"
                                        className="text-sm text-red-600 dark:text-red-400"
                                    >
                                        {errors.content}
                                    </p>
                                )}
                            </div>
                            {errors.publishing && (
                                <p
                                    role="alert"
                                    className="text-red-600 dark:text-red-400"
                                >
                                    {errors.publishing}
                                </p>
                            )}
                            <button
                                type="submit"
                                disabled={processing || authors.length === 0}
                                className="w-fit rounded bg-stone-900 px-5 py-3 text-sm font-medium text-white disabled:opacity-50 dark:bg-stone-100 dark:text-stone-900"
                            >
                                {processing ? 'Submitting…' : 'Create post'}
                            </button>
                        </>
                    )}
                </Form>
            </div>
        </main>
    );
}
