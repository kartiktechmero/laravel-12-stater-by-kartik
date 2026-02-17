<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white  antialiased">
<flux:sidebar sticky collapsible class="bg-zinc-50  border-e border-zinc-200">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark"/>

    <flux:sidebar.header class="flex items-center justify-between">
        <flux:sidebar.brand :href="route('dashboard')" :logo="asset('assets/images/logo/logo.png')" :name="config('app.name')">
        </flux:sidebar.brand>
        <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2"/>
    </flux:sidebar.header>

    <flux:sidebar.nav>
        <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')">{{ __('Dashboard') }}</flux:sidebar.item>
        <flux:sidebar.group expandable icon="computer-desktop" :heading="__('Platform')">
            <flux:sidebar.item icon="square-3-stack-3d" :href="route('home')" :current="request()->routeIs('post.*')" wire:navigate>
                {{ __('Posts Manager') }}
            </flux:sidebar.item>

            <flux:sidebar.item icon="play-circle" :href="route('home')" :current="request()->routeIs('story.*')" wire:navigate>
                {{ __('Story Manager') }}
            </flux:sidebar.item>
        </flux:sidebar.group>

        <flux:sidebar.group expandable icon="cpu-chip" :heading="__('Automate')">
            <flux:sidebar.item icon="bolt" :href="route('home')" :current="request()->routeIs('universal-trigger.*')" wire:navigate>
                {{ __('Smart Triggers') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="queue-list" :href="route('home')" :current="request()->routeIs('flow.*')" wire:navigate>
                {{ __('Smart Flow') }}
            </flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:sidebar.spacer/>

    <flux:sidebar.nav>
        <flux:sidebar.item icon="question-mark-circle" :href="route('home')" :current="request()->routeIs('contact-us')" wire:navigate>{{ __('Support') }}</flux:sidebar.item>
        <flux:sidebar.item icon="credit-card" :href="route('home')" :current="request()->routeIs('plan')" wire:navigate>{{ __('Billing & Plan') }}</flux:sidebar.item>
    </flux:sidebar.nav>

    <flux:dropdown position="top" align="start" class="max-lg:hidden">
        <flux:sidebar.profile
            :avatar="'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name)"
            :name="auth()->user()->name"
        />
        <flux:menu class="w-[220px]">
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <img src="{{'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name)}}" alt="Profile" class="rounded w-8 size-full object-cover">

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator/>

            <flux:menu.radio.group>
                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
            </flux:menu.radio.group>
            <flux:menu.separator/>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:sidebar>
{{-- 2. MOBILE HEADER --}}
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left"/>
    <flux:spacer/>
    <flux:dropdown position="top" align="end">
        <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down"/>
        <flux:menu>
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <img src="{{'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name)}}" alt="Profile" class="rounded w-8 size-full object-cover">
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator/>

            <flux:menu.radio.group>
                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
            </flux:menu.radio.group>
            <flux:menu.separator/>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:header>

{{ $slot }}

@fluxScripts
@livewireScripts
<x-toaster-hub/>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('reloadPage', (data) => {
            let url = data.link ?? '/';
            if (data.query && typeof data.query === 'object') {
                const params = new URLSearchParams(data.query).toString();
                if (params.length > 0) url += '?' + params;
            }
            window.location.href = url;
        });
        Livewire.on('redirectToCurrent', () => {
            window.location.href = window.location.href;
        });
    });
</script>
</body>
</html>
