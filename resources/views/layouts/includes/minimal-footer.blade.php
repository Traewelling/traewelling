@php
    use App\Http\Controllers\Backend\VersionController;
@endphp
<footer class="footer sm:footer-horizontal bg-primary text-neutral-content items-center p-4">
    <aside class="grid-flow-col items-center">
        <a href="/">
            <img src="/images/icons/logo.svg" class="h-12 w-12" alt="Träwelling Logo"/>
        </a>
        <a href="/">
            <h1 class="text-2xl text-bold w-full md:w-auto">#Träwelling</h1>
        </a>
        <p class="w-full md:w-auto">
            &copy; {{date('Y')}} Tr&auml;welling
            –
            <a href="{{route('changelog')}}" class="truncate">{{ VersionController::getVersion() }}</a>
        </p>
    </aside>
    <nav class="grid-flow-col gap-4 md:place-self-center md:justify-self-end">
        <a href="/legal/privacy-policy" class="link link-hover">
            {{ __('menu.privacy') }}
        </a>
        <a href="/legal" class="link link-hover">
            {{ __('menu.legal-notice') }}
        </a>
    </nav>
</footer>
