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
                gallery: {
                    ivory: 'var(--color-gallery-ivory)',
                    cream: 'var(--color-gallery-cream)',
                    charcoal: 'var(--color-gallery-charcoal)',
                    accent: 'var(--color-gallery-accent)',
                    'accent-hover': 'var(--color-gallery-accent-hover)',
                    burgundy: 'var(--color-gallery-burgundy)',
                    'burgundy-dark': 'var(--color-gallery-burgundy-dark)',
                    muted: 'var(--color-gallery-muted)',
                    border: 'var(--color-gallery-border)',
                    surface: 'var(--color-gallery-surface)',
                    'frame-backing': 'var(--color-gallery-frame-backing)',
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['Playfair Display', ...defaultTheme.fontFamily.serif],
            },
        },
    },

    plugins: [forms],
};
