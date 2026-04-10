<x-filament-panels::page>
    <div style="display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 1.5rem;">
        @push('styles')
        <style>
            @media (min-width: 768px) { .fi-plans-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; } }
            @media (min-width: 1280px) { .fi-plans-grid { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; } }
            .fi-plan-card { display: flex; flex-direction: column; border-radius: 16px; padding: 1.5rem; border: 1px solid rgba(0,0,0,0.08); background: #fff; transition: box-shadow 0.2s; }
            .fi-plan-card:hover { box-shadow: 0 8px 30px -12px rgba(0,0,0,0.12); }
            .dark .fi-plan-card { background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.08); }
            .fi-plan-card.is-inactive { opacity: 0.6; }
            .fi-plan-badge { display: inline-flex; align-items: center; gap: 6px; border-radius: 999px; padding: 4px 10px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
            .fi-plan-badge-active { background: #ecfdf5; color: #047857; }
            .dark .fi-plan-badge-active { background: rgba(16,185,129,0.1); color: #6ee7b7; }
            .fi-plan-badge-inactive { background: #f3f4f6; color: #9ca3af; }
            .dark .fi-plan-badge-inactive { background: rgba(255,255,255,0.06); color: #9ca3af; }
            .fi-plan-badge-dot { width: 6px; height: 6px; border-radius: 50%; }
            .fi-plan-badge-active .fi-plan-badge-dot { background: #10b981; }
            .fi-plan-badge-inactive .fi-plan-badge-dot { background: #9ca3af; }
            .fi-plan-name { font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0; letter-spacing: -0.01em; }
            .dark .fi-plan-name { color: #f9fafb; }
            .fi-plan-price { font-size: 2.25rem; font-weight: 800; color: #111827; letter-spacing: -0.02em; line-height: 1; }
            .dark .fi-plan-price { color: #f9fafb; }
            .fi-plan-period { font-size: 0.875rem; color: #9ca3af; margin-left: 4px; }
            .fi-plan-desc { font-size: 0.875rem; color: #6b7280; line-height: 1.5; margin-top: 8px; }
            .dark .fi-plan-desc { color: #9ca3af; }
            .fi-plan-limits { margin-top: 1.25rem; background: #f9fafb; border-radius: 12px; padding: 1rem; }
            .dark .fi-plan-limits { background: rgba(255,255,255,0.04); }
            .fi-plan-section-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #9ca3af; margin-bottom: 10px; }
            .fi-plan-limits-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 16px; }
            .fi-plan-limit-value { font-size: 1.125rem; font-weight: 700; color: #111827; font-variant-numeric: tabular-nums; }
            .dark .fi-plan-limit-value { color: #f9fafb; }
            .fi-plan-limit-label { font-size: 0.75rem; color: #6b7280; margin-left: 4px; }
            .fi-plan-features { margin-top: 1rem; display: flex; flex-wrap: wrap; gap: 6px; }
            .fi-plan-feat { display: inline-flex; align-items: center; gap: 4px; border-radius: 6px; padding: 4px 8px; font-size: 11px; font-weight: 500; }
            .fi-plan-feat-on { background: #f0fdfa; color: #0f766e; border: 1px solid rgba(15,118,110,0.12); }
            .dark .fi-plan-feat-on { background: rgba(20,184,166,0.08); color: #5eead4; border-color: rgba(94,234,212,0.15); }
            .fi-plan-feat-off { background: #f9fafb; color: #d1d5db; text-decoration: line-through; border: 1px solid rgba(0,0,0,0.04); }
            .dark .fi-plan-feat-off { background: rgba(255,255,255,0.03); color: #6b7280; border-color: rgba(255,255,255,0.05); }
            .fi-plan-feat svg { width: 12px; height: 12px; flex-shrink: 0; }
            .fi-plan-meta { margin-top: 1rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; color: #6b7280; }
            .dark .fi-plan-meta { color: #9ca3af; }
            .fi-plan-trial { background: #eff6ff; color: #2563eb; padding: 2px 10px; border-radius: 999px; font-weight: 600; font-size: 11px; }
            .dark .fi-plan-trial { background: rgba(59,130,246,0.1); color: #93c5fd; }
            .fi-plan-footer { margin-top: auto; padding-top: 1.25rem; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(0,0,0,0.06); }
            .dark .fi-plan-footer { border-top-color: rgba(255,255,255,0.06); }
            .fi-plan-subs-count { font-size: 1.5rem; font-weight: 700; color: #111827; font-variant-numeric: tabular-nums; }
            .dark .fi-plan-subs-count { color: #f9fafb; }
            .fi-plan-subs-label { font-size: 0.75rem; color: #6b7280; margin-left: 4px; }
            .fi-plan-edit-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; font-size: 0.75rem; font-weight: 600; background: #111827; color: #fff; text-decoration: none; transition: background 0.15s; }
            .fi-plan-edit-btn:hover { background: #374151; }
            .dark .fi-plan-edit-btn { background: #f9fafb; color: #111827; }
            .dark .fi-plan-edit-btn:hover { background: #e5e7eb; }
            .fi-plan-edit-btn svg { width: 14px; height: 14px; }
            .fi-plan-toggle-btn { padding: 6px; border-radius: 8px; background: transparent; border: none; cursor: pointer; color: #9ca3af; transition: all 0.15s; }
            .fi-plan-toggle-btn:hover { background: #f3f4f6; color: #374151; }
            .dark .fi-plan-toggle-btn:hover { background: rgba(255,255,255,0.08); color: #d1d5db; }
            .fi-plan-toggle-btn svg { width: 16px; height: 16px; }
        </style>
        @endpush

        <div class="fi-plans-grid" style="display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 1.5rem;">
            @foreach ($this->getPlans() as $plan)
                <div class="fi-plan-card {{ $plan->is_active ? '' : 'is-inactive' }}">
                    {{-- Header --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                        <span class="fi-plan-badge {{ $plan->is_active ? 'fi-plan-badge-active' : 'fi-plan-badge-inactive' }}">
                            <span class="fi-plan-badge-dot"></span>
                            {{ $plan->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                        <button wire:click="toggleActive({{ $plan->id }})" class="fi-plan-toggle-btn" title="{{ $plan->is_active ? 'Desactivar' : 'Activar' }}">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9"/></svg>
                        </button>
                    </div>

                    {{-- Name + Price --}}
                    <h3 class="fi-plan-name">{{ $plan->name }}</h3>
                    <div style="margin-top: 0.75rem; display: flex; align-items: baseline;">
                        @if ($plan->price > 0)
                            <span class="fi-plan-price">€{{ number_format((float) $plan->price, 2) }}</span>
                            <span class="fi-plan-period">/{{ $plan->period }}</span>
                        @else
                            <span class="fi-plan-price">Gratis</span>
                        @endif
                    </div>
                    <p class="fi-plan-desc">{{ $plan->description }}</p>

                    {{-- Limits --}}
                    <div class="fi-plan-limits">
                        <p class="fi-plan-section-label">Límites</p>
                        <div class="fi-plan-limits-grid">
                            @php
                                $limits = [
                                    [$plan->max_locations, 'Locales'],
                                    [$plan->max_menus_per_location, 'Menús/local'],
                                    [$plan->max_products, 'Productos'],
                                    [$plan->max_images, 'Imágenes'],
                                ];
                            @endphp
                            @foreach ($limits as [$val, $label])
                                <div>
                                    <span class="fi-plan-limit-value">{{ $val ?: '∞' }}</span>
                                    <span class="fi-plan-limit-label">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Features --}}
                    <div style="margin-top: 1rem;">
                        <p class="fi-plan-section-label">Funcionalidades</p>
                        <div class="fi-plan-features">
                            @php
                                $features = [
                                    'has_analytics' => 'Analytics',
                                    'has_custom_qr' => 'QR custom',
                                    'has_multilang' => 'Multi-idioma',
                                    'has_catalog' => 'Catálogo',
                                    'has_team' => 'Equipo',
                                    'has_api_access' => 'API',
                                    'has_custom_domain' => 'Dominio',
                                ];
                            @endphp
                            @foreach ($features as $key => $label)
                                @if ($plan->$key)
                                    <span class="fi-plan-feat fi-plan-feat-on">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        {{ $label }}
                                    </span>
                                @else
                                    <span class="fi-plan-feat fi-plan-feat-off">{{ $label }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Meta --}}
                    <div class="fi-plan-meta">
                        <span>{{ $plan->show_branding ? '⚠ Con branding' : '✓ Sin branding' }}</span>
                        @if ($plan->trial_days > 0)
                            <span class="fi-plan-trial">{{ $plan->trial_days }}d trial</span>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="fi-plan-footer" style="margin-top: 1.5rem;">
                        <div>
                            <span class="fi-plan-subs-count">{{ $plan->subscriptions_count }}</span>
                            <span class="fi-plan-subs-label">subs</span>
                        </div>
                        <a href="{{ \App\Filament\Resources\Plans\PlanResource::getUrl('edit', ['record' => $plan]) }}" class="fi-plan-edit-btn">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            Editar
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
