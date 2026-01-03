import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
    role?: string;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    is_admin: boolean;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles: Role[];
}

export interface Role {
    id: number;
    name: string
}

export interface Country {
    id: number;
    name: string;
    code: string;
}

export interface Location {
    id: number;
    name: string;
    address: string;
    city?: string;
    country_id?: number;
    province: boolean;
    postal_code: string | null;
    phone: string;
    description: string;
    user: User;
    slug: string;
    country: Country;
    image_url: string | null;
    logo_url: string | null;
    languages: Record<string, string>;
    currency: string;
    time_zone: string;
    social_medias: Record<string, string>;
    tenant_id: number;
    created_at: string;
    updated_at: string;
    roles: Role[];
    latitude: string;
    longitude: string;
    lang: string;
}
export type BreadcrumbItemType = BreadcrumbItem;
