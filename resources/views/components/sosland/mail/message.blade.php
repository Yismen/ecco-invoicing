@props([
    'url', 'banner_path'
])
<x-sosland.mail.layout>

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-sosland.mail.subcopy>
{{ $subcopy }}
</x-sosland.mail.subcopy>
</x-slot:subcopy>
@endisset
</x-sosland.mail.layout>
