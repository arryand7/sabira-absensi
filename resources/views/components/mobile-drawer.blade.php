<div
    x-show="mobileNavigationOpen"
    x-cloak
    @keydown.escape.window="mobileNavigationOpen = false"
    @close-mobile-navigation.window="mobileNavigationOpen = false"
    class="sabira-mobile-navigation"
    aria-label="Navigasi mobile"
>
    <div x-show="mobileNavigationOpen" x-transition.opacity class="sabira-mobile-navigation-backdrop" @click="mobileNavigationOpen = false"></div>
    <div x-show="mobileNavigationOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" x-trap.noscroll="mobileNavigationOpen" class="sabira-mobile-navigation-panel">
        <x-sidebar mobile />
    </div>
</div>
