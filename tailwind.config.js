import defaultTheme from 'tailwindcss/defaultTheme';

export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/Filament/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: { sans: ['Inter', ...defaultTheme.fontFamily.sans] },
            colors: {
                'itm-navy':  '#1B2A8A',
                'itm-blue':  '#2851A3',
                'itm-light': '#5B8DD9',
                'itm-teal':  '#1D9E75',
                'itm-bg':    '#EFF4FB',
            },
            boxShadow: {
                'itm':       '0 2px 16px rgba(27,42,138,0.10)',
                'itm-hover': '0 4px 24px rgba(27,42,138,0.18)',
            },
        },
    },
    plugins: [],
};

