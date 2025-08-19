import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import flowbitePlugin from 'flowbite/plugin'; 

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './node_modules/flowbite/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                judul: ['Poppins', 'sans-serif'],
            },
            colors: {
                customblue: '#eaf2f8',
                siam: {
                    50: '#F7F7F6',
                    100: '#EEEFED',
                    200: '#D5D8D3',
                    300: '#BCC0B8',
                    400: '#8A9083',
                    500: '#58614E',
                    600: '#4F5746',
                    700: '#353A2F',
                    800: '#282C23',
                    900: '#1A1D17',
                },
            },
        },
    },

    plugins: [
        forms,
        flowbitePlugin
    ],
};
