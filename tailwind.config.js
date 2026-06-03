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
                primary: {
                    DEFAULT: '#2563EB',
                    light: '#DBEAFE',
                    dark: '#1D4ED8',
                },
                success: {
                    DEFAULT: '#22C55E',
                    light: '#DCFCE7',
                },
                danger: {
                    DEFAULT: '#EF4444',
                    light: '#FEE2E2',
                },
                warning: {
                    DEFAULT: '#F59E0B',
                    light: '#FEF3C7',
                },
                bg: '#F8FAFC',
                surface: '#FFFFFF',
                border: '#E5E7EB',
                'text-primary': '#0F172A',
                'text-secondary': '#64748B',
            },
        },
    },

    plugins: [forms],
};
