import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { login, register } from '@/routes';

export default function Welcome() {
    return (
        <>
            <Head title="Book Club" />
            <main className="flex min-h-svh flex-col items-center justify-center gap-6 p-6 text-center">
                <h1 className="text-4xl font-bold">Book Club</h1>
                <p className="max-w-md text-muted-foreground">
                    Form a club with friends, vote on what to read next, and
                    track your progress together.
                </p>
                <div className="flex gap-4">
                    <Button asChild>
                        <Link href={register()}>Get started</Link>
                    </Button>
                    <Button asChild variant="outline">
                        <Link href={login()}>Log in</Link>
                    </Button>
                </div>
            </main>
        </>
    );
}
