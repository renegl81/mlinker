import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface MenuCustomization {
    colors?: {
        accent?: string;
        bg?: string;
        ink?: string;
        ink_soft?: string;
        rule?: string;
    };
    fonts?: {
        display?: string;
        body?: string;
    };
    layout?: {
        show_allergens?: boolean;
        show_ingredients?: boolean;
        show_product_images?: boolean;
        show_section_descriptions?: boolean;
        image_position?: 'left' | 'right' | 'top' | 'hidden';
        price_alignment?: 'right' | 'inline';
    };
    spacing?: {
        density?: 'compact' | 'comfortable' | 'spacious';
    };
    header?: {
        logo_url?: string | null;
        show_restaurant_name?: boolean;
        tagline?: string | null;
        name_display_style?: 'default' | 'uppercase' | 'italic';
    };
    sections?: {
        divider_style?: 'line' | 'ornament' | 'none';
        title_alignment?: 'left' | 'center';
        numbering?: 'none' | 'roman' | 'arabic';
    };
}

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
    menus: Menu[];
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

export interface Template{
    id: number;
    name: string;
    description: string | null;
    preview_image_url: string | null;
    config: object;
    component_name: string;
    is_active: boolean;
    tenant_id: number | null;
    created_at: string;
    updated_at: string;
}
export interface Allergen {
    id: number;
    name: string;
    code: string | null;
}

export interface Ingredient {
    id: number;
    name: string;
}

export interface Section {
    id: number;
    name: string;
    description: string | null;
    sort_order?: number;
    menu_id?: number;
    products?: Product[];
}

export interface Menu {
    id: number;
    name: string;
    description: string | null;
    image_url: string | null;
    image_path: string | null;
    template_id: number | null;
    template: Template | null;
    location: Location;
    products?: Product[];
    sections?: Section[];
    location_id: number;
    tenant_id: number;
    show_prices: boolean;
    show_currency: boolean;
    show_calories: boolean;
    customization?: MenuCustomization | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface Product {
    id: number;
    name: string;
    description: string | null;
    price: number | string;
    calories: number | string | null;
    image_url: string | null;
    image_path?: string | null;
    tags?: string[] | null;
    allergens?: Allergen[];
    ingredients?: Ingredient[];
    tenant_id: string;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
