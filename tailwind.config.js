import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            // Brand palette. `parchment` is the page, `espresso` carries text and
            // dark buttons, `cognac` is the single accent. Warm neutrals instead
            // of gray/stone so the whole surface reads as leather-adjacent.
            // The default Tailwind palette is intentionally left in place.
            colors: {
                parchment: {
                    50: '#FBF8F3',
                    100: '#F5EFE4',
                    200: '#E9DFCD',
                    300: '#D9CBB2',
                },
                espresso: {
                    400: '#6B5643',
                    500: '#54402F',
                    600: '#4A3A2C',
                    700: '#3A2C21',
                    800: '#2A1F17',
                    900: '#1B1310',
                    950: '#120C09',
                },
                cognac: {
                    50: '#FBF3EC',
                    100: '#F5E5D6',
                    200: '#E8C9AC',
                    300: '#D8A87C',
                    400: '#C08552',
                    500: '#A9714B',
                    600: '#8E5A38',
                    700: '#74462B',
                },
                // Semantic tones tuned warm so they sit next to cognac without
                // the cold cast of rose/sky and emerald/slate.
                success: {
                    50: '#F1F4ED',
                    100: '#E1E8D6',
                    500: '#5A7340',
                    600: '#485C33',
                    700: '#3A4A2A',
                },
                warning: {
                    50: '#FCF5E4',
                    100: '#F8E9C6',
                    500: '#B08028',
                    600: '#8F6720',
                    700: '#73531A',
                },
                danger: {
                    50: '#FBEFEC',
                    100: '#F6DCD5',
                    500: '#B04A32',
                    600: '#963D28',
                    700: '#7A3222',
                },
                info: {
                    50: '#EEF3F6',
                    100: '#DCE7EE',
                    500: '#4A6E85',
                    600: '#3B5A6E',
                    700: '#2F4859',
                },
            },

            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                // Editorial serif for headings, prices and buttons. Body stays
                // Figtree — one added family is the whole budget.
                display: ['Fraunces', ...defaultTheme.fontFamily.serif],
            },

            boxShadow: {
                card: '0 1px 2px 0 rgb(28 19 16 / 0.04), 0 8px 24px -12px rgb(28 19 16 / 0.12)',
                'card-hover': '0 2px 4px 0 rgb(28 19 16 / 0.05), 0 20px 40px -16px rgb(28 19 16 / 0.20)',
                lift: '0 24px 48px -24px rgb(28 19 16 / 0.28)',
            },

            borderRadius: {
                '4xl': '2rem',
            },
        },
    },

    plugins: [forms],
};
