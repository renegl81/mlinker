# Stripe Setup — MenuLinker

Guia para configurar Stripe en modo test y conectarlo con los planes de MenuLinker.

## 1. Variables de entorno

Añade las siguientes variables en tu `.env`:

```env
STRIPE_KEY=pk_test_...          # Publishable key (dashboard Stripe > Developers > API keys)
STRIPE_SECRET=sk_test_...       # Secret key
STRIPE_WEBHOOK_SECRET=whsec_... # Webhook signing secret (ver seccion 4)
CASHIER_CURRENCY=eur
CASHIER_CURRENCY_LOCALE=es_ES.UTF-8
```

## 2. Crear productos y precios en Stripe (modo test)

1. Entra en [https://dashboard.stripe.com/test](https://dashboard.stripe.com/test)
2. Ve a **Catalog > Products**
3. Crea un producto por cada plan de pago:

| Plan       | Nombre en Stripe   | Precio     | Facturacion |
|------------|--------------------|------------|-------------|
| Pro        | MenuLinker Pro     | 14,99 EUR  | Mensual     |
| Business   | MenuLinker Business| 34,99 EUR  | Mensual     |
| Enterprise | MenuLinker Enterprise | 99,99 EUR | Mensual  |

4. Al crear el precio, Stripe genera un `Price ID` con formato `price_xxxxxxxxxxxxxxxx`.

## 3. Mapear stripe_price_id en PlanSeeder

Edita `database/seeders/PlanSeeder.php` y rellena el campo `stripe_price_id` de cada plan con el Price ID de Stripe:

```php
[
    'slug' => 'pro',
    'stripe_price_id' => 'price_xxxxxxxxxxxxxxxx', // <-- tu Price ID real de Stripe test
    // ...
],
```

Vuelve a ejecutar el seeder en desarrollo:

```bash
./vendor/bin/sail artisan db:seed --class=PlanSeeder
```

## 4. Configurar webhooks

### Desarrollo local (con Stripe CLI)

```bash
stripe listen --forward-to http://localhost/stripe/webhook
```

Stripe CLI imprimira el `whsec_...` para usar en `.env` como `STRIPE_WEBHOOK_SECRET`.

### Produccion

1. En el dashboard: **Developers > Webhooks > Add endpoint**
2. URL: `https://tu-dominio.com/stripe/webhook`
3. Eventos a escuchar:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_failed`
   - `invoice.payment_succeeded`
4. Copia el **Signing secret** al `.env` como `STRIPE_WEBHOOK_SECRET`

La ruta webhook esta excluida de CSRF automaticamente (ver `bootstrap/app.php`).

## 5. Flujo de checkout

1. Usuario va a `/panel/billing/plans`
2. Selecciona un plan y hace click en el CTA
3. Se hace POST a `/panel/billing/checkout` con `plan_slug`
4. `BillingController@checkout` llama a `StartCheckout::execute()`
5. Cashier crea una sesion en Stripe Checkout y redirige al usuario
6. Al completar, Stripe redirige a `/panel/billing/success`
7. Stripe envia webhook `customer.subscription.updated` → `StripeWebhookController` sincroniza el `plan_id` local

## 6. Stripe CLI — comandos utiles

```bash
# Escuchar webhooks en local
stripe listen --forward-to http://localhost/stripe/webhook

# Simular evento de suscripcion creada
stripe trigger customer.subscription.created

# Simular fallo de pago
stripe trigger invoice.payment_failed

# Ver logs de webhooks
stripe logs tail
```

## 7. Tenant como customer de Stripe

El modelo `Tenant` usa el trait `Billable` de Cashier. La primera vez que un tenant inicia checkout, Cashier crea automaticamente un Customer en Stripe y guarda el `stripe_id` en la columna correspondiente del tenant.

La columna `stripe_id` esta en el JSON `data` del tenant (Stancl Tenancy single-DB usa una columna JSONB `data` para columnas extra). Si necesitas añadirla como columna real, agrega `stripe_id` al array `getCustomColumns()` en `App\Models\Tenant`.
