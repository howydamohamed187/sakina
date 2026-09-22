<script>
    document.documentElement.setAttribute("lang", @json(app()->getLocale()));
    document.documentElement.setAttribute("dir", @json(\App\Support\Locales::isRtl() ? "rtl" : "ltr"));
</script>
<link rel="stylesheet" href="{{ asset('css/sakina-panel.css') }}?v={{ filemtime(public_path('css/sakina-panel.css')) }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="{{ \App\Support\Fonts::googleCssUrl(\App\Support\StoredSettings::fontFamily()) }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<style>
    :root {
        --sakina-font-family: '{{ \App\Support\StoredSettings::fontFamily() }}', ui-sans-serif, system-ui, sans-serif;
        --sakina-font-size: {{ \App\Support\StoredSettings::fontSizePx() }};
    }
</style>
