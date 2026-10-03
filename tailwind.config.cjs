/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './node_modules/flowbite/**/*.js',
        './node_modules/@tailwindcss/forms/**/*.js',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#f0f9eb',
                    100: '#dcf0ce',
                    200: '#bce1a2',
                    300: '#93cc6d',
                    400: '#6fb542',
                    500: '#5ab638',
                    600: '#3d8a2a',
                    700: '#2f6a20',
                    800: '#28541c',
                    900: '#22471a',
                },
                neutral: {
                    50: '#F8F9FA',
                    100: '#F4F5F7',
                    200: '#E5E7EB',
                    300: '#D1D5DB',
                    400: '#9CA3AF',
                    500: '#6B7280',
                    600: '#4B5563',
                    700: '#374151',
                    800: '#1F2937',
                    900: '#111827',
                },
            },
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
            },
            borderRadius: {
                sm: '6px',
                md: '8px',
                lg: '12px',
                xl: '12px',
            },
            boxShadow: {
                card: '0 1px 3px rgba(0,0,0,0.06)',
                hover: '0 2px 8px rgba(0,0,0,0.08)',
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease forwards',
                'fade-in-up': 'fadeInUp 0.6s ease forwards',
                'slide-up': 'slideUp 0.6s ease forwards',
            },
            keyframes: {
                fadeIn: {
                    from: { opacity: '0' },
                    to: { opacity: '1' },
                },
                fadeInUp: {
                    from: { opacity: '0', transform: 'translateY(16px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                slideUp: {
                    from: { opacity: '0', transform: 'translateY(24px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        require('flowbite/plugin'),
    ],
};
