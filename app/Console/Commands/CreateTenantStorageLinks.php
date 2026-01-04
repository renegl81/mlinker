<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateTenantStorageLinks extends Command
{
    protected $signature = 'tenants:storage-link';
    protected $description = 'Create storage symlinks for all tenants';

    public function handle(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->info("Creando symlink para tenant: {$tenant->id}");

            // Ruta del storage del tenant
            $target = storage_path("app/public/tenant{$tenant->id}");

            // Ruta del enlace simbólico en public
            $link = public_path("storage/tenant{$tenant->id}");

            // Crear directorio de storage si no existe
            if (!File::exists($target)) {
                File::makeDirectory($target, 0755, true);
                $this->info("  → Directorio creado: {$target}");
            }

            // Eliminar enlace existente si existe
            if (File::exists($link)) {
                if (is_link($link)) {
                    File::delete($link);
                    $this->warn("  → Enlace simbólico existente eliminado");
                } else {
                    //$this->error("  → Existe un directorio real en {$link}. No se puede crear el enlace.");
                    continue;
                }
            }

            // Crear enlace simbólico
            if (File::link($target, $link)) {
                $this->info("  ✓ Enlace simbólico creado: {$link} → {$target}");
            } else {
                $this->error("  ✗ Error al crear enlace simbólico para {$tenant->id}");
            }
        }

        $this->info('¡Proceso completado!');
    }
}
