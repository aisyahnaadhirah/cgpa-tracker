<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Log In | CGPA Tracker</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="flex min-h-screen items-center justify-center bg-slate-50 px-6">
        <main class="w-full max-w-md rounded-2xl bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-bold text-indigo-700">CGPA Tracker</h1>
            <p class="mt-2 text-slate-600">Log masuk ke akaun pelajar anda.</p>

            @if ($errors->any())
                <div role="alert" class="mt-6 rounded-lg bg-red-50 p-4 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium">E-mel</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        required
                        autofocus
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-3 font-semibold text-white hover:bg-indigo-700">
                    Log In
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-slate-600">
                Do not have account?
                <a href="{{ route('register') }}"
                    class="font-semibold text-indigo-700 hover:underline">
                    Register Account
                </a>
            </p>
        </main>
    </body>
</html>