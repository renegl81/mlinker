/**
 * Shared composable for home page templates.
 * Provides formatters for opening hours, social media and maps.
 */

export interface OpeningHour {
    id: number;
    weekday: number; // 0 = Sunday, 1 = Monday … 6 = Saturday
    opens_at: string | null;
    closes_at: string | null;
    is_closed: boolean;
}

export interface FormattedDay {
    label: string;
    hours: string;
    isClosed: boolean;
}

export interface SocialLink {
    platform: string;
    url: string;
    icon: string;
    label: string;
}

const WEEKDAY_LABELS_ES = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
const WEEKDAY_SHORT_ES = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

/**
 * Returns opening hours grouped and formatted per weekday (0=Sun … 6=Sat).
 * Days not present in the array are treated as closed.
 */
export function formatOpeningHours(hours: OpeningHour[]): FormattedDay[] {
    const result: FormattedDay[] = [];

    // ISO week order: Mon=1 … Sun=0. We want Mon→Sun for display.
    const displayOrder = [1, 2, 3, 4, 5, 6, 0];

    for (const weekday of displayOrder) {
        const oh = hours.find((h) => h.weekday === weekday);
        const label = WEEKDAY_LABELS_ES[weekday];

        if (!oh || oh.is_closed) {
            result.push({ label, hours: 'Cerrado', isClosed: true });
        } else {
            const opens = oh.opens_at?.slice(0, 5) ?? '';
            const closes = oh.closes_at?.slice(0, 5) ?? '';
            result.push({ label, hours: `${opens} – ${closes}`, isClosed: false });
        }
    }

    return result;
}

/**
 * Groups consecutive days with the same hours into ranges for compact display.
 * E.g. "Lun–Vie: 09:00–22:00"
 */
export function groupOpeningHours(hours: OpeningHour[]): { range: string; hours: string; isClosed: boolean }[] {
    const formatted = formatOpeningHours(hours);
    const groups: { range: string; hours: string; isClosed: boolean }[] = [];
    let i = 0;

    while (i < formatted.length) {
        const current = formatted[i];
        let j = i + 1;

        while (j < formatted.length && formatted[j].hours === current.hours) {
            j++;
        }

        const startShort = WEEKDAY_SHORT_ES[toWeekdayIndex(current.label)];
        const endShort = j > i + 1 ? WEEKDAY_SHORT_ES[toWeekdayIndex(formatted[j - 1].label)] : null;

        const range = endShort ? `${startShort}–${endShort}` : startShort;
        groups.push({ range, hours: current.hours, isClosed: current.isClosed });
        i = j;
    }

    return groups;
}

function toWeekdayIndex(label: string): number {
    return WEEKDAY_LABELS_ES.indexOf(label);
}

const SOCIAL_ICONS: Record<string, { icon: string; label: string }> = {
    instagram: {
        icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor" stroke="none"/></svg>`,
        label: 'Instagram',
    },
    facebook: {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>`,
        label: 'Facebook',
    },
    twitter: {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>`,
        label: 'X / Twitter',
    },
    tiktok: {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>`,
        label: 'TikTok',
    },
    youtube: {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>`,
        label: 'YouTube',
    },
    whatsapp: {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>`,
        label: 'WhatsApp',
    },
    tripadvisor: {
        icon: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 4.5c2.1 0 4.05.63 5.67 1.71L19.5 4.5l-1.62 1.62A7.462 7.462 0 0 1 19.5 11.25c0 4.142-3.358 7.5-7.5 7.5S4.5 15.392 4.5 11.25c0-1.994.78-3.806 2.055-5.148L4.5 4.5l1.83 1.71A7.455 7.455 0 0 1 12 4.5zm0 1.5a5.25 5.25 0 1 0 0 10.5A5.25 5.25 0 0 0 12 6zm0 2.25a3 3 0 1 1 0 6 3 3 0 0 1 0-6z"/></svg>`,
        label: 'TripAdvisor',
    },
};

/**
 * Parses the social_medias JSON field from a Location and returns structured links.
 * The field can be an array of objects {platform, url} or a simple {platform: url} dict.
 */
export function parseSocialLinks(socialMedias: unknown): SocialLink[] {
    if (!socialMedias) return [];

    const links: SocialLink[] = [];

    if (Array.isArray(socialMedias)) {
        for (const item of socialMedias) {
            if (item && typeof item === 'object' && 'platform' in item && 'url' in item) {
                const platform = String(item.platform).toLowerCase();
                const meta = SOCIAL_ICONS[platform];
                if (meta && item.url) {
                    links.push({ platform, url: String(item.url), ...meta });
                }
            }
        }
    } else if (typeof socialMedias === 'object') {
        for (const [key, value] of Object.entries(socialMedias as Record<string, unknown>)) {
            const platform = key.toLowerCase();
            const meta = SOCIAL_ICONS[platform];
            if (meta && value) {
                links.push({ platform, url: String(value), ...meta });
            }
        }
    }

    return links;
}

/**
 * Returns an embeddable Google Maps URL from lat/lng or address.
 */
export function mapsEmbedUrl(opts: {
    lat?: number | null;
    lng?: number | null;
    address?: string | null;
    name?: string | null;
}): string | null {
    if (opts.lat && opts.lng) {
        const q = opts.name ? encodeURIComponent(opts.name) : `${opts.lat},${opts.lng}`;
        return `https://maps.google.com/maps?q=${q}&ll=${opts.lat},${opts.lng}&z=15&output=embed`;
    }
    if (opts.address) {
        return `https://maps.google.com/maps?q=${encodeURIComponent(opts.address)}&output=embed`;
    }
    return null;
}

/**
 * Returns a Google Maps directions URL.
 */
export function mapsDirectionsUrl(opts: {
    lat?: number | null;
    lng?: number | null;
    address?: string | null;
}): string | null {
    if (opts.lat && opts.lng) {
        return `https://www.google.com/maps/dir/?api=1&destination=${opts.lat},${opts.lng}`;
    }
    if (opts.address) {
        return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(opts.address)}`;
    }
    return null;
}
