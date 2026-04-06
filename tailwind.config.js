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
                // ── Vapour Garage Main brand ──────────────────────────────
                black:   '#060608',
                dark:    '#0d0b14',
                dark2:   '#130f1e',
                dark3:   '#1a1428',
                purple:  { DEFAULT: '#7c22d4', 2: '#9b3df0', 3: '#5b16a0', faint: 'rgba(124,34,212,0.12)' },
                green:   { DEFAULT: '#39ff14', dark: '#25cc0a', faint: 'rgba(57,255,20,0.10)' },
                grey:    '#c8c4d4',
                muted:   '#7a7490',
                // ── Semantic ──────────────────────────────────────────────
                ok:      '#3a7a4a',
                warn:    '#b87a1e',
                danger:  '#b83232',
                gold:    { DEFAULT: '#c9943a', 2: '#e0b44a' },
                // ── ERP interior aliases (so old Blade pages still work) ──
                cream:   { DEFAULT: '#1a1428', 2: '#130f1e', 3: 'rgba(124,34,212,0.2)' },
                paper:   '#0d0b14',
                ink:     { DEFAULT: '#e8e4f0', 2: '#c8c4d4', 3: '#a09ab0', 4: '#7a7490' },
                terra:   { DEFAULT: '#7c22d4', light: '#b87ef5', dark: '#5b16a0' },
                navy:    { DEFAULT: '#130f1e', 2: '#1a1428', dark: '#0d0b14' },
            },
            fontFamily: {
                sans:    ['Exo 2', ...defaultTheme.fontFamily.sans],
                display: ['Russo One', ...defaultTheme.fontFamily.sans],
                mono:    ['Share Tech Mono', ...defaultTheme.fontFamily.mono],
                body:    ['Exo 2', ...defaultTheme.fontFamily.sans],
                serif:   ['Russo One', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                card:    '0 4px 24px rgba(0,0,0,0.5)',
                purple:  '0 0 30px rgba(124,34,212,0.4)',
                green:   '0 0 20px rgba(57,255,20,0.25)',
                glow:    '0 0 50px rgba(124,34,212,0.6)',
            },
            backgroundImage: {
                'purple-green': 'linear-gradient(135deg, #7c22d4, #39ff14)',
                'dark-gradient':'linear-gradient(180deg, #130f1e 0%, #0d0b14 100%)',
            },
        },
    },
    plugins: [
        forms,
        function({ addUtilities }) {
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
