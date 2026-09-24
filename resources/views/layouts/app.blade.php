<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>CGPA Tracker</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900">
        <main class="mx-auto max-w-6xl px-6 py-10">
            {{ $slot }}
        </main>
        @livewireScripts
    </body>
</html>