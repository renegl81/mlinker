export interface FakeProduct {
    id: number;
    name: string;
    description: string;
    price: number;
    tags: string[] | null;
    allergens: string[];
}

export interface FakeSection {
    id: number;
    name: string;
    description: string;
    products: FakeProduct[];
}

export interface FakeMenu {
    name: string;
    description: string;
    show_prices: boolean;
    show_currency: boolean;
    currency: string;
    sections: FakeSection[];
    location_name: string;
}

export const fakeMenu: FakeMenu = {
    name: 'La Parrilla del Mar',
    description: 'Cocina de mercado con alma mediterránea',
    show_prices: true,
    show_currency: true,
    currency: '€',
    location_name: 'Restaurante del Mar',
    sections: [
        {
            id: 1,
            name: 'Entrantes',
            description: 'Para empezar el viaje',
            products: [
                {
                    id: 1,
                    name: 'Ensalada de temporada',
                    description: 'Lechugas, tomate cherry, nueces y vinagreta de miel',
                    price: 8,
                    tags: ['vg'],
                    allergens: ['nueces'],
                },
                {
                    id: 2,
                    name: 'Tartar de atún',
                    description: 'Atún rojo, aguacate, soja y sésamo tostado',
                    price: 14,
                    tags: null,
                    allergens: ['pescado', 'soja'],
                },
                {
                    id: 3,
                    name: 'Carpaccio de ternera',
                    description: 'Con rúcula, parmesano y aceite de trufa',
                    price: 12,
                    tags: null,
                    allergens: ['lácteos'],
                },
            ],
        },
        {
            id: 2,
            name: 'Principales',
            description: 'Nuestras recetas estrella',
            products: [
                {
                    id: 4,
                    name: 'Risotto de setas',
                    description: 'Cremoso con porcini, boletus y parmesano',
                    price: 16,
                    tags: ['vg'],
                    allergens: ['lácteos', 'gluten'],
                },
                {
                    id: 5,
                    name: 'Lomo de bacalao',
                    description: 'Con pil-pil, espárragos y emulsión de azafrán',
                    price: 22,
                    tags: null,
                    allergens: ['pescado'],
                },
                {
                    id: 6,
                    name: 'Solomillo a la pimienta',
                    description: 'Con patatas confitadas y jugo de carne reducido',
                    price: 26,
                    tags: null,
                    allergens: ['lácteos'],
                },
            ],
        },
        {
            id: 3,
            name: 'Postres',
            description: 'El final perfecto',
            products: [
                {
                    id: 7,
                    name: 'Tarta de queso',
                    description: 'Al horno con mermelada de frutos rojos',
                    price: 7,
                    tags: ['vg'],
                    allergens: ['lácteos', 'huevo'],
                },
                {
                    id: 8,
                    name: 'Coulant de chocolate',
                    description: 'Con helado de vainilla y caramelo salado',
                    price: 8,
                    tags: ['vg'],
                    allergens: ['gluten', 'lácteos', 'huevo'],
                },
            ],
        },
        {
            id: 4,
            name: 'Bebidas',
            description: '',
            products: [
                {
                    id: 9,
                    name: 'Vino de la casa',
                    description: 'Tinto, blanco o rosado. Botella',
                    price: 18,
                    tags: null,
                    allergens: ['sulfitos'],
                },
                {
                    id: 10,
                    name: 'Agua mineral',
                    description: 'Con o sin gas. 50 cl',
                    price: 2.5,
                    tags: ['vg'],
                    allergens: [],
                },
            ],
        },
    ],
};

export function formatFakePrice(price: number, currency: string = '€'): string {
    return price % 1 === 0 ? `${price} ${currency}` : `${price.toFixed(2)} ${currency}`;
}

/** Convierte número a romano simple (1-20 suficiente para previews) */
export function toRomanNumeral(n: number): string {
    const map: [number, string][] = [
        [10, 'X'], [9, 'IX'], [5, 'V'], [4, 'IV'], [1, 'I'],
    ];
    let result = '';
    for (const [val, sym] of map) {
        while (n >= val) { result += sym; n -= val; }
    }
    return result;
}
