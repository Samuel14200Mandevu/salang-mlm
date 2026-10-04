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
                    50: 'var(--color-primary-50)',
                    100: 'var(--color-primary-100)',
                    200: 'var(--color-primary-200)',
                    300: 'var(--color-primary-300)',
                    400: 'var(--color-primary-400)',
                    500: 'var(--color-primary-500)',
                    600: 'var(--color-primary-600)',
                    700: 'var(--color-primary-700)',
                    800: 'var(--color-primary-800)',
                    900: 'var(--color-primary-900)',
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
