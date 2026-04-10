<x-mail::message>
# ¡Hola, {{ $user->name }}!

Tu menú digital ya está listo y disponible para tus clientes. Escanea el QR o comparte el enlace directo para que puedan verlo.

<x-mail::button :url="$menuUrl">
Ver mi menú
</x-mail::button>

<x-mail::button :url="$qrDownloadUrl" color="white">
Descargar QR
</x-mail::button>

---

**3 consejos rápidos para aprovechar al máximo tu menú:**

- **Añade fotos a tus platos** — Los menús con imágenes generan hasta un 40% más de pedidos.
- **Comparte el enlace por WhatsApp** — Envíalo directamente a tus clientes o grupos de reservas.
- **Actualiza tu menú cuando quieras** — Accede al panel, edita precios o productos y los cambios se publican al instante.

---

Si tienes cualquier duda, responde a este correo y te ayudamos.

Un saludo,<br>
El equipo de **MenuLinker**
</x-mail::message>
