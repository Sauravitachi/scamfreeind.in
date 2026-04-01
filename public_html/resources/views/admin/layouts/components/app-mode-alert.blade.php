@use(App\Enums\AppMode)

@php $appMode = config('app.mode', AppMode::PRODUCTION) @endphp

@if ($appMode !== AppMode::PRODUCTION)
    <x-admin.alert style="background-color: {{ $appMode->color() }};" message="App mode : <strong>{{ $appMode->label() }}</strong>" icon='ti ti-assembly' important />
@endif