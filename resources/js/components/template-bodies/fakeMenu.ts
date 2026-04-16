/**
 * Menú ficticio compartido para previews de plantillas.
 * Mismo dataset para todas las plantillas para que el usuario compare estilos, no contenidos.
 *
 * BodyMenu es el tipo mínimo que los Body components necesitan.
 * Es compatible con el tipo Menu real del proyecto y con FakeMenu.
 */

// Tipo mínimo que los Body components necesitan — compatible con Menu real y FakeMenu
export interface BodyMenuLocation {
    name?: string | null;
    address?: string | null;
    phone?: string | null;
    currency?: string | null;
    order_email?: string | null;
    order_whatsapp?: string | null;
}

export interface BodyMenuAllergen {
    id: number;
    code: string;
    name: string;
}

export interface BodyMenuIngredient {
    id: number;
    name: string;
}

export interface BodyMenuProduct {
    id: number;
    name: string;
    description?: string | null;
    price?: number | string | null;
    calories?: number | string | null;
    tags?: string[] | null;
    allergens?: BodyMenuAllergen[];
    ingredients?: BodyMenuIngredient[];
    image_path?: string | null;
    image_url?: string | null;
}

export interface BodyMenuSection {
    id: number;
    name: string;
    description?: string | null;
    products?: BodyMenuProduct[];
}

export interface BodyMenu {
    id: number;
    name: string;
    description?: string | null;
    show_prices: boolean;
    show_currency: boolean;
    show_calories?: boolean;
    image_path?: string | null;
    image_url?: string | null;
    sections?: BodyMenuSection[];
    location?: BodyMenuLocation | null;
}

export interface FakeAllergen {
    id: number;
    code: string;
    name: string;
}

export interface FakeIngredient {
    id: number;
    name: string;
}

export interface FakeProduct {
    id: number;
    name: string;
    description: string | null;
    price: number | null;
    calories: number | null;
    tags: string[];
    allergens: FakeAllergen[];
    ingredients: FakeIngredient[];
    image_path: string | null;
    image_url: string | null;
}

export interface FakeSection {
    id: number;
    name: string;
    description: string | null;
    products: FakeProduct[];
}

export interface FakeMenuLocation {
    id: number;
    name: string;
    address: string | null;
    phone: string | null;
    currency: string;
    order_email: string | null;
    order_whatsapp: string | null;
}

export interface FakeMenu {
    id: number;
    name: string;
    description: string | null;
    show_prices: boolean;
    show_currency: boolean;
    show_calories: boolean;
    image_path: string | null;
    sections: FakeSection[];
    location: FakeMenuLocation | null;
}

export const fakeMenu: FakeMenu = {
    id: 1,
    name: 'Carta de Temporada',
    description: 'Cocina de proximidad con los mejores productos del mercado',
    show_prices: true,
    show_currency: true,
    show_calories: false,
    image_path: null,
    location: {
        id: 1,
        name: 'La Parrilla del Mar',
        address: 'Calle del Puerto, 12',
        phone: '+34 912 345 678',
        currency: '€',
        order_email: null,
        order_whatsapp: null,
    },
    sections: [
        {
            id: 1,
            name: 'Entrantes',
            description: 'Para comenzar',
            products: [
                {
                    id: 1,
                    name: 'Ensalada de temporada',
                    description: 'Brotes tiernos, queso de cabra y vinagreta de miel',
                    price: 8,
                    calories: null,
                    tags: ['vegetarian'],
                    allergens: [{ id: 1, code: 'milk', name: 'Lácteos' }],
                    ingredients: [{ id: 1, name: 'Brotes' }, { id: 2, name: 'Queso de cabra' }],
                    image_path: null,
                    image_url: null,
                },
                {
                    id: 2,
                    name: 'Tartar de atún',
                    description: 'Con aguacate, sésamo tostado y salsa ponzu',
                    price: 14,
                    calories: null,
                    tags: ['gluten_free'],
                    allergens: [{ id: 2, code: 'fish', name: 'Pescado' }, { id: 3, code: 'sesame', name: 'Sésamo' }],
                    ingredients: [{ id: 3, name: 'Atún' }, { id: 4, name: 'Aguacate' }, { id: 5, name: 'Sésamo' }],
                    image_path: null,
                    image_url: null,
                },
                {
                    id: 3,
                    name: 'Carpaccio de ternera',
                    description: 'Con rúcula, parmesano y aceite de trufa blanca',
                    price: 12,
                    calories: null,
                    tags: [],
                    allergens: [{ id: 1, code: 'milk', name: 'Lácteos' }],
                    ingredients: [{ id: 6, name: 'Ternera' }, { id: 7, name: 'Rúcula' }, { id: 8, name: 'Parmesano' }],
                    image_path: null,
                    image_url: null,
                },
            ],
        },
        {
            id: 2,
            name: 'Principales',
            description: 'Nuestras especialidades',
            products: [
                {
                    id: 4,
                    name: 'Risotto de setas',
                    description: 'Arroz carnaroli, boletus edulis y aceite de albahaca',
                    price: 16,
                    calories: null,
                    tags: ['vegetarian'],
                    allergens: [{ id: 1, code: 'milk', name: 'Lácteos' }],
                    ingredients: [{ id: 9, name: 'Arroz carnaroli' }, { id: 10, name: 'Boletus' }],
                    image_path: null,
                    image_url: null,
                },
                {
                    id: 5,
                    name: 'Lomo de bacalao',
                    description: 'A la vizcaína con pil-pil ligero y espárragos trigueros',
                    price: 22,
                    calories: null,
                    tags: ['gluten_free'],
                    allergens: [{ id: 2, code: 'fish', name: 'Pescado' }],
                    ingredients: [{ id: 11, name: 'Bacalao' }, { id: 12, name: 'Pimiento choricero' }],
                    image_path: null,
                    image_url: null,
                },
                {
                    id: 6,
                    name: 'Solomillo a la pimienta',
                    description: 'Con patatas Hasselback y reducción de oporto',
                    price: 26,
                    calories: null,
                    tags: [],
                    allergens: [{ id: 4, code: 'milk', name: 'Lácteos' }],
                    ingredients: [{ id: 13, name: 'Solomillo' }, { id: 14, name: 'Patatas' }],
                    image_path: null,
                    image_url: null,
                },
            ],
        },
        {
            id: 3,
            name: 'Postres',
            description: null,
            products: [
                {
                    id: 7,
                    name: 'Tarta de queso',
                    description: 'Al estilo vasco, con mermelada de frutos rojos',
                    price: 7,
                    calories: null,
                    tags: ['vegetarian'],
                    allergens: [{ id: 1, code: 'milk', name: 'Lácteos' }, { id: 5, code: 'gluten', name: 'Gluten' }],
                    ingredients: [],
                    image_path: null,
                    image_url: null,
                },
                {
                    id: 8,
                    name: 'Coulant de chocolate',
                    description: 'Con helado de vainilla y crumble de avellanas',
                    price: 8,
                    calories: null,
                    tags: ['vegetarian'],
                    allergens: [{ id: 5, code: 'gluten', name: 'Gluten' }, { id: 6, code: 'nuts', name: 'Frutos secos' }],
                    ingredients: [],
                    image_path: null,
                    image_url: null,
                },
            ],
        },
    ],
};
