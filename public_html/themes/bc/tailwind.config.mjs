/** @type {import('tailwindcss').Config} */
export default {
    corePlugins: {
        preflight: false,
    },
    content: [
        '../../../bc-cms/themes/BC/**/*.blade.php',
        '../../../bc-cms/resources/views/**/*.blade.php',

        // Need to scan core modules
        '../../../bc-cms/modules/**/*.blade.php',
    ],
    prefix: 'b-',
    theme: {
        extend: {
            typography: (theme) => ({
                DEFAULT: {
                    css: {
                        'table': {
                            'borderCollapse': 'collapse',
                            'width': '100%',
                            'marginBottom': '1em',
                        },
                        'th, td': {
                            'border': '1px solid',
                            'borderColor': theme('colors.gray.300'),
                            'padding': '0.5em',
                        },
                        'th': {
                            'backgroundColor': theme('colors.gray.100'),
                        },
                    },
                },
            }),
        },
    },
    plugins: [
    ],
};
