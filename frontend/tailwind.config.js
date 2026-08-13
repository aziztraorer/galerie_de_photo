/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,ts,js,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          blue: '#2563eb',
          dark: '#0f172a',
          light: '#eff6ff'
        }
      }
    }
  },
  plugins: []
}
