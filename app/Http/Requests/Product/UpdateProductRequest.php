<?php

namespace App\Http\Requests\Product;

class UpdateProductRequest extends StoreProductRequest
{
    // Hereda todas las reglas y mensajes de StoreProductRequest
    // La imagen sigue siendo opcional para permitir actualizar sin cambiarla
}
