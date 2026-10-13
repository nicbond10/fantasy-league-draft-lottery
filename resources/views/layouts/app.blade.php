<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main id="main-content" class="bg-app px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
