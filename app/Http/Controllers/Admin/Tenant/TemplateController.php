<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TemplateStoreRequest;
use App\Http\Requests\TemplateUpdateRequest;
use App\Jobs\CreateTemplate;
use App\Jobs\DeleteTemplate;
use App\Jobs\ListTemplates;
use App\Jobs\ShowTemplate;
use App\Jobs\UpdateTemplate;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = ListTemplates::dispatch();

        return Inertia::render('template.index', [
            'templates' => $templates,
        ]);
    }

    public function store(TemplateStoreRequest $request): RedirectResponse
    {
        CreateTemplate::dispatch($request);

        return redirect()->route('template.index');
    }

    public function show(Request $request, Template $template)
    {
        ShowTemplate::dispatch($template);

        return Inertia::render('template.show', [
            'template' => $template,
        ]);
    }

    public function update(TemplateUpdateRequest $request, Template $template): RedirectResponse
    {
        UpdateTemplate::dispatch($request, $template);

        return redirect()->route('template.show', ['template' => $template]);
    }

    public function destroy(Request $request, Template $template): RedirectResponse
    {
        DeleteTemplate::dispatch($template);

        return redirect()->route('template.index');
    }
}
