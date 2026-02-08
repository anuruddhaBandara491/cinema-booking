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
            colors: {
                primary: {
                    50: '#fbeaea',
                    100: '#f6cfcf',
                    200: '#eea1a1',
                    300: '#e57373',
                    400: '#db4545',
                    500: '#c91f1f',
                    600: '#a31515',
                    700: '#7d0f0f',
                    800: '#580a0a',
                    900: '#330505',
                    950: '#1a0303',
                },
                canvas: {
                    DEFAULT: '#0b0b0b',
                    muted: '#111111',
                    dark: '#090909',
                },
                card: {
                    DEFAULT: '#1a1a1a',
                    surface: '#141414',
                },
                accent: {
                    DEFAULT: '#e11d48',
                    glow: '#ff4d6d',
                },
            },
            boxShadow: {
                glow: '0 0 30px rgba(255, 77, 109, 0.35)',
                'glow-soft': '0 0 18px rgba(255, 77, 109, 0.25)',
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            spacing: {
                18: '4.5rem',
                22: '5.5rem',
                26: '6.5rem',
                30: '7.5rem',
            },
            letterSpacing: {
                cinema: '0.3em',
                'cinema-wide': '0.2em',
            },
            lineHeight: {
                snug: '1.35',
                relaxed: '1.7',
            },
        },
    },

    plugins: [forms],
};
