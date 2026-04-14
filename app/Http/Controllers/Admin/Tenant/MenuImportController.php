<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Exports\MenuTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\MenuProductsImport;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MenuImportController extends Controller
{
    public function template(): BinaryFileResponse
    {
        $appName = str(config('app.name', 'MenuLinker'))->slug();

        return Excel::download(new MenuTemplateExport, "{$appName}-plantilla.xlsx");
    }

    public function import(Request $request, Menu $menu): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $plan = tenant()?->subscription?->plan;
        if (! $plan || $plan->slug === 'free') {
            return back()->with('error', 'La importación requiere un plan de pago.');
        }

        try {
            $import = new MenuProductsImport($menu->id, tenant()->id);
            Excel::import($import, $request->file('file'));

            $count = $import->getImportedCount();

            return back()->with('success', "Se importaron {$count} productos correctamente.");
        } catch (\Throwable $e) {
            Log::error('Menu import failed', [
                'menu_id' => $menu->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al importar: '.$e->getMessage());
        }
    }
}
