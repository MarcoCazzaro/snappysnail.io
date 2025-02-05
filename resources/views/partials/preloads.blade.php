<?php
/*
    ATTENZIONE - ATTENZIONE - ATTENZIONE - ATTENZIONE - ATTENZIONE - ATTENZIONE - ATTENZIONE - ATTENZIONE - ATTENZIONE
    Per il futuro me: se vuoi evitarti giornate di debug, fa' attenzione quando fai il prefetch delle pagine perché se lo fai con le pagine di registrazione ti parte la sessione e, ad esempio, l'utente non resta loggato subito dopo la registrazione.
    */
?>
<link rel="preload" href="{{ asset('img/snappysnail-logo.png') }}" as="image">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    rel="preload"
    href="https://fonts.googleapis.com/css?family=Quicksand&display=swap"
    as="style"
    onload="this.onload=null;this.rel='stylesheet'" />
<noscript>
    <link
        href="https://fonts.googleapis.com/css?family=Quicksand&display=swap"
        rel="stylesheet"
        type="text/css" />
</noscript>
@guest()
<?php
$all_prefetch_urls = [
    'route' => ['profile.show'],
    'url' => [
        '/',
        '/dear-googlebot',
        '/services',
        '/portfolio',
        '/curriculum',
        '/contact'
    ]
];
foreach ($all_prefetch_urls as $url_type => $prefetch_urls) {
?>
    @include('partials.prefetch-urls', compact('prefetch_urls', 'url_type'))
<?php
}
?>
@endguest