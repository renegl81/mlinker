<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Tenant;

use App\Actions\Section\CreateSection;
use App\Actions\Section\DeleteSection;
use App\Actions\Section\ReorderSections;
use App\Actions\Section\UpdateSection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Section\StoreSectionRequest;
use App\Http\Requests\Section\UpdateSectionRequest;
use App\Models\Menu;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(StoreSectionRequest $request, Menu $menu, CreateSection $createSection): RedirectResponse
    {
        $createSection->execute($menu, $request->validated());

        return redirect()
            ->route('tenant.menus.show', ['menu' => $menu->id])
            ->with('success', 'Sección creada correctamente.');
    }

    public function update(UpdateSectionRequest $request, Section $section, UpdateSection $updateSection): RedirectResponse
    {
        $updateSection->execute($section, $request->validated());

        return redirect()
            ->route('tenant.menus.show', ['menu' => $section->menu_id])
            ->with('success', 'Sección actualizada correctamente.');
    }

    public function destroy(Section $section, DeleteSection $deleteSection): RedirectResponse
    {
        $menuId = $section->menu_id;
        $deleteSection->execute($section);

        return redirect()
            ->route('tenant.menus.show', ['menu' => $menuId])
            ->with('success', 'Sección eliminada correctamente.');
    }

    public function reorder(Request $request, Menu $menu, ReorderSections $reorderSections): RedirectResponse
    {
        $request->validate([
            'section_ids' => ['required', 'array'],
            'section_ids.*' => ['integer'],
        ]);

        $reorderSections->execute($menu, $request->input('section_ids'));

        return redirect()
            ->route('tenant.menus.show', ['menu' => $menu->id])
            ->with('success', 'Secciones reordenadas correctamente.');
    }
}
