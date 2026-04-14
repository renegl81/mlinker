<x-mail::message>
# Tu suscripcion ha sido cancelada

Hemos procesado la cancelacion de tu suscripcion al plan **{{ $plan->name }}**.

@if($endsAt)
Podras seguir accediendo a todas las funcionalidades de tu plan hasta el **{{ $endsAt->format('d/m/Y') }}**.
@endif

Si cambias de opinion, puedes reactivar tu suscripcion en cualquier momento desde el panel de gestion.

<x-mail::button :url="config('app.url') . '/panel/billing/manage'">
Gestionar suscripcion
</x-mail::button>

Lamentamos verte marchar. Si tienes alguna sugerencia sobre como mejorar, nos encantaria escucharte.

Un saludo,<br>
El equipo de **{{ config('app.name') }}**
</x-mail::message>
