import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#9fe870',
                    active: '#cdffad',
                    neutral: '#c5edab',
                    pale: '#e2f6d5',
                },
                'on-primary': '#0e0f0c',
                ink: {
                    DEFAULT: '#0e0f0c',
                    deep: '#163300',
                },
                body: '#454745',
                mute: '#868685',
                canvas: {
                    DEFAULT: '#ffffff',
                    soft: '#e8ebe6',
                },
                positive: {
                    DEFAULT: '#2ead4b',
                    deep: '#054d28',
                },
                warning: {
                    DEFAULT: '#ffd11a',
                    deep: '#b86700',
                    content: '#4a3b1c',
                },
                negative: {
                    DEFAULT: '#d03238',
                    deep: '#a72027',
                    darkest: '#a7000d',
                    bg: '#320707',
                },
                'accent-orange': '#ffc091',
                'accent-cyan': '#38c8ff',
            },
        },
    },

    plugins: [forms],
};
