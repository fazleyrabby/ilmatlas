<script type="application/ld+json">
{!! json_encode([
    '@@context' => 'https://schema.org',
    '@@type' => 'WebSite',
    'name' => 'EduBase',
    'url' => url('/'),
    'potentialAction' => [
        '@@type' => 'SearchAction',
        'target' => [
            '@@type' => 'EntryPoint',
            'urlTemplate' => url('/search?q={search_term_string}'),
        ],
        'query-input' => 'required name=search_term_string',
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
