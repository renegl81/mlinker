<x-mail::message>
# Tu periodo de prueba termina pronto

Tu prueba gratuita del plan **{{ $plan->name }}** finaliza el **{{ $trialEndsAt->format('d/m/Y') }}**.

Para seguir disfrutando de todas las funcionalidades, asegurate de que tu metodo de pago este configurado correctamente.

<x-mail::button :url="config('app.url') . '/panel/billing/manage'">
Gestionar suscripcion
</x-mail::button>

Si tienes cualquier duda, estamos a tu disposicion.

Un saludo,<br>
El equipo de **MenuLinker**
</x-mail::message>
