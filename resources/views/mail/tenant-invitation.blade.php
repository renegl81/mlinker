<x-mail::message>
# Has recibido una invitación

**{{ $inviterName }}** te ha invitado a gestionar **{{ $tenantName }}** como **{{ $role }}**.

Acepta la invitación para empezar a colaborar en la gestión del menú digital de {{ $tenantName }}.

<x-mail::button :url="$invitationUrl">
Aceptar invitación
</x-mail::button>

Si no esperabas esta invitación, puedes ignorar este correo.

Un saludo,<br>
El equipo de **MenuLinker**
</x-mail::message>
