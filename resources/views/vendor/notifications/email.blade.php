<x-mail::message>
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
# {{ (isset($level) && $level === 'error') ? __('Упс!') : __('Здравствуйте!') }}
@endif

{{-- Intro Lines --}}
@foreach (($introLines ?? []) as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level ?? null) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach (($outroLines ?? []) as $line)
{{ $line }}

@endforeach

{{-- Salutation (remove default "Regards") --}}
@if (! empty($salutation))
{{ $salutation }}
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
Если у вас возникли проблемы с нажатием кнопки "Войти в систему", скопируйте и вставьте приведенный ниже URL-адрес в свой веб-браузер: https://api.astrahovanie.ru/admin/login
</x-slot:subcopy>
@endisset
</x-mail::message>


