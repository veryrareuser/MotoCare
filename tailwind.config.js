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
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    DEFAULT: '#D7FF00',
                    soft: '#EDF8D2',
                    accent: '#9FBD00',
                    dark: '#4E6700',
                },
                ink: '#1D1D1D',
                canvas: '#F3F4F6',
                line: '#E5E7EB',
                muted: '#6B7280',
                warm: '#FFF1D9',
            },
            boxShadow: {
                card: '0 1px 2px rgba(16, 24, 40, 0.04)',
            },
        },
    },

    plugins: [forms],
};
