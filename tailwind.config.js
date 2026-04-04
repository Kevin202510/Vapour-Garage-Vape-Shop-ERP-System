import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                // ✅ ORIGINAL (hyphen version)
                cream: { DEFAULT: '#f5f0e8', 2: '#ede7d9', 3: '#e3dccf' },

                // ✅ ADD ALIASES (no hyphen version)
                cream2: '#ede7d9',
                cream3: '#e3dccf',

                paper: '#faf7f2',

                ink: { DEFAULT: '#1a1714', 2: '#3d3730', 3: '#6b6256', 4: '#a09589' },

                // aliases
                ink2: '#3d3730',
                ink3: '#6b6256',
                ink4: '#a09589',

                terra: { DEFAULT: '#c45e3c', light: '#f2a882', dark: '#a84e30' },

                // aliases
                terralight: '#f2a882',
                terradark: '#a84e30',

                navy: { DEFAULT: '#1e2d4a', 2: '#2d4270', dark: '#162238' },

                // aliases
                navy2: '#2d4270',
                navydark: '#162238',

                sage: '#5a7a5c',

                gold: { DEFAULT: '#b8882a', 2: '#e0aa44' },

                // alias
                gold2: '#e0aa44',

                ok: '#3a7a4a',
                warn: '#b87a1e',
                danger: '#b83232',
            },

            fontFamily: {
                sans: ['Epilogue', ...defaultTheme.fontFamily.sans],
                serif: ['Playfair Display', ...defaultTheme.fontFamily.serif],
                mono: ['IBM Plex Mono', ...defaultTheme.fontFamily.mono],
            },

            boxShadow: {
                card: '0 4px 20px rgba(26,23,20,0.07)',
                lg: '0 8px 32px rgba(26,23,20,0.12)',
            },

            animation: {
                pulse: 'pulse 2s cubic-bezier(0.4,0,0.6,1) infinite',
            },
        },
    },

    plugins: [
        forms,

        function ({ addUtilities }) {
            addUtilities({
                '.scrollbar-none': {
                    '-ms-overflow-style': 'none',
                    'scrollbar-width': 'none',
                    '&::-webkit-scrollbar': { display: 'none' },
                },
            });
        },
    ],
};