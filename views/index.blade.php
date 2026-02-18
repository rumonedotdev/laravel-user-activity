<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Activity</title>

    @php
        $packageDist = '../vendor/haunv/laravel-user-activity/dist';
        $manifestFile = file_get_contents($packageDist.'/.vite/manifest.json');
        $manifest = json_decode($manifestFile, true);
    @endphp

    {{-- Load CSS if it exists --}}
    @if(isset($manifest['views/ts/main.ts']['css']))
        @foreach($manifest['views/ts/main.ts']['css'] as $css)

            @dump(asset($packageDist .'/'. $css))

            <link rel="stylesheet" href="{{ asset($packageDist .'/'. $css) }}">
        @endforeach
    @endif

</head>
<body>
<div id="app"></div>
  {{-- Load JS --}}
    <script type="module"
        src="{{ asset($packageDist.'/'.$manifest['views/ts/main.ts']['file']) }}">
    </script>
</body>
</html>
