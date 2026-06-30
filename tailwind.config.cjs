/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./node_modules/flowbite/**/*.js",
        "./node_modules/@tailwindcss/forms/**/*.js"
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                    950: '#1e1b4b',
                },
            },
            fontFamily: {
                sans: ['Nunito', 'system-ui', '-apple-system', 'sans-serif'],
            },
            borderRadius: {
                'sm': '8px',
                'md': '12px',
                'lg': '16px',
                'xl': '20px',
                '2xl': '24px',
            },
            boxShadow: {
                'card': '0 4px 16px rgba(0,0,0,0.08)',
                'hover': '0 8px 32px rgba(99,102,241,0.15)',
                'lg': '0 8px 32px rgba(0,0,0,0.12)',
                'xl': '0 12px 48px rgba(0,0,0,0.16)',
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease forwards',
                'fade-in-up': 'fadeInUp 0.6s ease forwards',
                'fade-in-down': 'fadeInDown 0.6s ease forwards',
                'fade-in-left': 'fadeInLeft 0.6s ease forwards',
                'fade-in-right': 'fadeInRight 0.6s ease forwards',
                'fade-in-scale': 'fadeInScale 0.6s ease forwards',
                'slide-up': 'slideUp 0.6s ease forwards',
                'slide-down': 'slideDown 0.6s ease forwards',
                'pulse-glow': 'pulseGlow 2s ease-in-out infinite',
                'float': 'float 3s ease-in-out infinite',
                'spin-slow': 'spin 2s linear infinite',
            },
            keyframes: {
                fadeIn: {
                    from: { opacity: '0' },
                    to: { opacity: '1' },
                },
                fadeInUp: {
                    from: { opacity: '0', transform: 'translateY(24px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                fadeInDown: {
                    from: { opacity: '0', transform: 'translateY(-24px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                fadeInLeft: {
                    from: { opacity: '0', transform: 'translateX(-24px)' },
                    to: { opacity: '1', transform: 'translateX(0)' },
                },
                fadeInRight: {
                    from: { opacity: '0', transform: 'translateX(24px)' },
                    to: { opacity: '1', transform: 'translateX(0)' },
                },
                fadeInScale: {
                    from: { opacity: '0', transform: 'scale(0.92)' },
                    to: { opacity: '1', transform: 'scale(1)' },
                },
                slideUp: {
                    from: { opacity: '0', transform: 'translateY(40px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                slideDown: {
                    from: { opacity: '0', transform: 'translateY(-40px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                pulseGlow: {
                    '0%, 100%': { boxShadow: '0 0 20px rgba(99,102,241,0.15)' },
                    '50%': { boxShadow: '0 0 40px rgba(99,102,241,0.3)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-8px)' },
                },
                spin: {
                    from: { transform: 'rotate(0deg)' },
                    to: { transform: 'rotate(360deg)' },
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        require('flowbite/plugin')
    ],
};