<x-mail::message>
# {{ $heading }}

<br>

{{ $paragraph_1 }}.
{{ $paragraph_2 }}:

<x-mail::button :url="$url" color="primary">
    {{ $btnText }}
</x-mail::button>

{{ $regards }},<br><br>
{{ config('app.name') }}
</x-mail::message>
