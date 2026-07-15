@props(['institute' => null])

@if($institute)
@php
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        'name' => $institute->name,
    ];

    if ($institute->short_name) {
        $data['alternateName'] = $institute->short_name;
    }

    if ($institute->description) {
        $data['description'] = \Illuminate\Support\Str::limit(strip_tags($institute->description), 300);
    }

    $data['url'] = route('institutes.show', $institute);

    if ($institute->logo_url) {
        $data['logo'] = $institute->logo_url;
    }

    if ($institute->full_address || $institute->district) {
        $address = ['@type' => 'PostalAddress'];

        if ($institute->full_address) {
            $address['streetAddress'] = $institute->full_address;
        }
        if ($institute->district) {
            $address['addressLocality'] = is_array($institute->district) ? ($institute->district['name'] ?? '') : $institute->district->name;
        }
        if ($institute->division) {
            $address['addressRegion'] = is_array($institute->division) ? ($institute->division['name'] ?? '') : $institute->division->name;
        }

        $address['addressCountry'] = 'BD';

        $data['address'] = $address;
    }

    if ($institute->latitude && $institute->longitude) {
        $data['geo'] = [
            '@type' => 'GeoCoordinates',
            'latitude' => $institute->latitude,
            'longitude' => $institute->longitude,
        ];
    }

    $contacts = $institute->contacts instanceof \Illuminate\Support\Collection ? $institute->contacts : collect($institute->contacts);
    $socialLinks = $institute->socialLinks instanceof \Illuminate\Support\Collection ? $institute->socialLinks : collect($institute->socialLinks);

    if ($contacts->isNotEmpty()) {
        $firstContact = $contacts->first();
        $data['telephone'] = is_array($firstContact) ? ($firstContact['phone'] ?? '') : ($firstContact->phone ?? '');
    }

    if ($contacts->isNotEmpty()) {
        $firstContact = $contacts->first();
        $email = is_array($firstContact) ? ($firstContact['email'] ?? '') : ($firstContact->email ?? '');
        if ($email) {
            $data['email'] = $email;
        }
    }

    if ($socialLinks->isNotEmpty()) {
        $data['sameAs'] = $socialLinks->map(fn($link) => is_array($link) ? ($link['url'] ?? '') : ($link->url ?? ''))->filter()->all();
    }
@endphp

<script type="application/ld+json">
{!! json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
