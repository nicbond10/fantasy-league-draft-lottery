<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head', ['title' => __('Fantasy Draft Lottery')])
    </head>
    <body class="min-h-screen bg-app text-ink antialiased">
        <div class="relative isolate flex min-h-svh flex-col overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-80 bg-[radial-gradient(circle_at_top,rgb(174_40_63_/_0.18),transparent_65%)]" aria-hidden="true"></div>

            <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-5 py-6 sm:px-8">
                <a href="{{ route('home') }}" class="rounded-lg" aria-label="{{ __('Fantasy Draft Lottery home') }}">
                    <x-wordmark />
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center rounded-xl bg-brand px-4 text-sm font-semibold text-ink hover:bg-brand-hover">
                        {{ __('Dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center rounded-xl border border-line bg-surface px-4 text-sm font-semibold text-ink hover:border-brand hover:bg-brand/10">
                        {{ __('Sign in') }}
                    </a>
                @endauth
            </header>

            <main class="mx-auto grid w-full max-w-7xl flex-1 items-center gap-12 px-5 py-14 sm:px-8 lg:grid-cols-[minmax(0,1fr)_24rem] lg:py-20">
                <section class="max-w-3xl" aria-labelledby="homepage-heading">
                    <p class="eyebrow text-gold">{{ __('Your league. One definitive order.') }}</p>
                    <h1 id="homepage-heading" class="mt-5 text-4xl font-semibold tracking-[-0.035em] text-ink sm:text-6xl lg:text-7xl">
                        {{ __('Make draft night feel official.') }}
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-ink-secondary">
                        {{ __('Configure weighted lottery odds, reveal every pick with your league, and preserve the complete final draft order for good.') }}
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        @auth
                            <a href="{{ route('lotteries.index') }}" class="inline-flex min-h-12 items-center rounded-xl bg-gold px-6 text-sm font-semibold text-stone-950 hover:bg-gold-hover">
                                {{ __('View my lotteries') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex min-h-12 items-center rounded-xl bg-gold px-6 text-sm font-semibold text-stone-950 hover:bg-gold-hover">
                                {{ __('Sign in to continue') }}
                            </a>
                            <span class="text-sm text-ink-muted">{{ __('Accounts are available by invitation only.') }}</span>
                        @endauth
                    </div>
                </section>

                <aside class="app-card overflow-hidden" aria-label="{{ __('How it works') }}">
                    <div class="border-b border-line bg-raised px-5 py-4">
                        <p class="eyebrow">{{ __('Draft lottery') }}</p>
                        <h2 class="mt-1 text-lg font-semibold text-ink">{{ __('From odds to final order') }}</h2>
                    </div>
                    <ol class="divide-y divide-line">
                        @foreach ([
                            [__('Configure'), __('Set the initial team order and weighted odds.')],
                            [__('Run'), __('Generate one secure official result or try a simulation.')],
                            [__('Reveal'), __('Present every pick from the final position to #1.')],
                        ] as [$step, $description])
                            <li class="flex gap-4 px-5 py-5">
                                <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-brand/10 text-sm font-bold text-rose-200">{{ $loop->iteration }}</span>
                                <span>
                                    <strong class="block text-sm text-ink">{{ $step }}</strong>
                                    <span class="mt-1 block text-sm leading-6 text-ink-secondary">{{ $description }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </aside>
            </main>

            <footer class="mx-auto w-full max-w-7xl px-5 py-6 text-sm text-ink-muted sm:px-8">
                {{ __('Fantasy Draft Lottery · Built for keeper-league draft night.') }}
            </footer>
        </div>
    </body>
</html>
