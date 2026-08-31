{{-- FIX: a literal <?xml ...?> here gets misparsed as a PHP open tag by the
     production PHP build (short_open_tag on there, off on the local/CI PHP
     CLI used to run the tests — which is why this passed locally and 500'd
     only in production: "syntax error, unexpected identifier 'version'").
     Echoing it through Blade instead means the compiled view never contains
     a literal "<?xml" sequence for PHP's tokenizer to trip over. --}}
{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        @isset($url['lastmod'])
        <lastmod>{{ $url['lastmod'] }}</lastmod>
        @endisset
        <priority>{{ $url['priority'] }}</priority>
    </url>
@endforeach
</urlset>
