@php($element_bg = 'bg-zinc-100 dark:bg-zinc-950')
<div class="flex gap-4 flex-wrap text-zinc-400 text-sm">
    <p class="{{ $element_bg }} text-brand">&copy;{{ date('Y') }} Snappysnail di Marco Cazzaro</p>
    <p class="{{ $element_bg }}">Via Monte Grappa 119, San Martino di Lupari (PD) Italy</p>
    <p class="{{ $element_bg }}">P.IVA 03919560130</p>
    <div class="{{ $element_bg }} flex gap-4 items-center justify-center">
        <a aria-label="Linkedin profile page of Marco Cazzaro" href="https://www.linkedin.com/in/marco-cazzaro-2b6373100/" class="ssnail-link" target="_blank"><i class="fab fa-linkedin"></i></a>
        <a aria-label="GitHub profile page of Marco Cazzaro" href="https://github.com/MarcoCazzaro" class="ssnail-link" target="_blank"><i class="fab fa-github"></i></a>
    </div>
    <a class="{{ $element_bg }}" href="{{ url('/dear-googlebot') }}">Dear GoogleBot <i class="fas fa-robot"></i></a>
</div>