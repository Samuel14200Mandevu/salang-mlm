import type { Config } from "tailwindcss";

const config: Config = {
  darkMode: ["class"],
  content: [
    "./src/pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/components/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      colors: {
        /** Salang institutional palette */
        brand: {
          primary: "#1E5DAD",
          "primary-hover": "#184F94",
          accent: "#F5A623",
          "accent-muted": "#E8940F",
        },
        page: {
          light: "#F4F5F7",
          dark: "#111827",
        },
        ink: {
          DEFAULT: "#1F2937",
          muted: "#6B7280",
          subtle: "#9CA3AF",
          inverse: "#E5E7EB",
        },
        /** Admin shell (aligned with brand primary, no purple) */
        canvas: "#111827",
        surface: "#1A2332",
        sidebar: "#0F1419",
        rail: "#0C1018",
        border: {
          DEFAULT: "#E5E7EB",
          dark: "#1F2937",
        },
        foreground: "#E5E7EB",
        muted: "#9CA3AF",
        primary: "#1E5DAD",
        success: "#0D9488",
        danger: "#DC2626",
        gold: "#F5A623",
        salang: "#5AB638",
      },
      fontFamily: {
        sans: ["var(--font-inter)", "ui-sans-serif", "sans-serif"],
        display: ["var(--font-jakarta)", "var(--font-inter)", "sans-serif"],
      },
      borderRadius: {
        DEFAULT: "0.5rem",
        lg: "0.625rem",
        xl: "0.75rem",
      },
      boxShadow: {
        /** Reserved for rare elevation; marketing avoids drop shadows */
        focus: "0 0 0 2px #1E5DAD33",
      },
      keyframes: {
        "fade-up": {
          from: { opacity: "0", transform: "translateY(10px)" },
          to: { opacity: "1", transform: "translateY(0)" },
        },
      },
      animation: {
        "fade-up": "fade-up 0.45s ease-out forwards",
      },
    },
  },
  plugins: [],
};

export default config;
