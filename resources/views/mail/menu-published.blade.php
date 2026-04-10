<x-mail::message>
# ¡Tu menú está publicado!

Hola {{ $user->name }},

Tu menú **{{ $menu->name }}** ya está visible para tus clientes. Compártelo para que puedan ver tu carta digital al instante.

<x-mail::button :url="$publicUrl">
Ver menú publicado
</x-mail::button>

---

**Comparte con tus clientes por WhatsApp:**

Copia y pega este mensaje en WhatsApp:

> Ahora puedes ver nuestra carta digital aquí: {{ $publicUrl }}

Recuerda que puedes actualizar precios, añadir productos o cambiar secciones en cualquier momento desde el panel y los cambios se publican al instante.

Un saludo,<br>
El equipo de **MenuLinker**
</x-mail::message>
