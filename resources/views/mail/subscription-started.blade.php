<x-mail::message>
# Bienvenido al plan {{ $plan->name }}

Gracias por confiar en {{ config('app.name') }}. Tu suscripcion al plan **{{ $plan->name }}** ya esta activa.

@if($plan->trial_days > 0)
Dispones de **{{ $plan->trial_days }} dias de prueba gratis** para explorar todas las funcionalidades.
@endif

<x-mail::button :url="config('app.url') . '/panel/billing/manage'">
Ver mi suscripcion
</x-mail::button>

Si tienes cualquier duda, no dudes en contactarnos.

Un saludo,<br>
El equipo de **{{ config('app.name') }}**
</x-mail::message>
