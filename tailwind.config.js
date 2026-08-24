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
                    50: '#F3F6FC',
                    100: '#E5ECFA',
                    200: '#C5D4F7',
                    300: '#91AFF3',
                    400: '#5082F1',
                    500: '#0D54F2',
                    600: '#0945CE',
                    700: '#0537A8',
                    800: '#032C87',
                    900: '#012169',
                    950: '#021746',
                },
            },
        },
    },

    plugins: [forms],
};
