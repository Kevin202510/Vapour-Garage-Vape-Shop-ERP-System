import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                // ── Brand ────────────────────────────────────────────────────
                purple: { DEFAULT: '#7c22d4', 2: '#9b3df0', 3: '#5b16a0', faint: 'rgba(124,34,212,0.08)' },
                green:  { DEFAULT: '#39ff14', dark: '#25cc0a', faint: 'rgba(57,255,20,0.10)' },

                // ── ERP Surface (light theme) ─────────────────────────────────
                // These tokens are used as Tailwind classes throughout all blade files.
                // Previously dark; now remapped to a clean light palette.
                cream: {
                    DEFAULT: '#f5f4fb',   // page background (was dark #1a1428)
                    2:       '#ffffff',   // card / input background (was #130f1e)
                    3:       '#e6e3f4',   // border (was rgba purple)
                },
                paper:   '#f5f4fb',       // body bg alias (was #0d0b14)
                navy: {
                    DEFAULT: '#ffffff',   // card background (was #130f1e)
                    2:       '#f5f4fb',   // surface variant (was #1a1428)
                    dark:    '#eeecf8',   // subtle surface (was #0d0b14)
                },
                ink: {
                    DEFAULT: '#1c1830',   // primary text (was #e8e4f0, flipped)
                    2:       '#4a4465',   // secondary text
                    3:       '#6b6785',   // tertiary text
                    4:       '#8b87a3',   // muted / placeholder
                },
                terra: { DEFAULT: '#7c22d4', light: '#b87ef5', dark: '#5b16a0' },

                // ── Semantic status (vivid for light backgrounds) ─────────────
                ok:     '#16a34a',
                warn:   '#d97706',
                danger: '#dc2626',
                gold:   { DEFAULT: '#d97706', 2: '#f59e0b' },
                sage:   { DEFAULT: '#16a34a', light: '#22c55e' },

                // ── Sidebar / topbar (intentionally dark) ────────────────────
                // Used only in erp.css custom classes, not as Tailwind utilities.
                sidebar: '#1e1a35',
            },
            fontFamily: {
                sans:    ['Exo 2', ...defaultTheme.fontFamily.sans],
                display: ['Russo One', ...defaultTheme.fontFamily.sans],
                mono:    ['Share Tech Mono', ...defaultTheme.fontFamily.mono],
                body:    ['Exo 2', ...defaultTheme.fontFamily.sans],
                serif:   ['Russo One', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                card:   '0 1px 4px rgba(28,24,48,0.08), 0 4px 16px rgba(124,34,212,0.06)',
                purple: '0 0 24px rgba(124,34,212,0.3)',
                green:  '0 0 16px rgba(57,255,20,0.2)',
                glow:   '0 0 40px rgba(124,34,212,0.5)',
                input:  '0 0 0 3px rgba(124,34,212,0.12)',
            },
            backgroundImage: {
                'brand-gradient': 'linear-gradient(135deg, #7c22d4, #9b3df0)',
                'logo-gradient':  'linear-gradient(90deg, #9b3df0, #39ff14)',
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
