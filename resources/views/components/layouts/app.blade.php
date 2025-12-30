<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main class="w-full max-w-none mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
