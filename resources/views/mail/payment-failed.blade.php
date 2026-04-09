<x-mail::message>
# Problema con tu pago

Hemos detectado un problema al procesar el pago de tu suscripcion en MenuLinker.

Por favor, revisa tu metodo de pago para evitar interrupciones en el servicio.

<x-mail::button :url="config('app.url') . '/panel/billing/manage'">
Actualizar metodo de pago
</x-mail::button>

Si crees que es un error o necesitas ayuda, contacta con nosotros.

Un saludo,<br>
El equipo de **MenuLinker**
</x-mail::message>
