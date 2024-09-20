/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/**/*.{html,js}"],
  theme: {
    extend: {
      colors: {
        "aktiv-black": '#09093C',
        "aktiv-grey": '#757C98',
        "aktiv-blue": '#0268FB',
        "aktiv-green": '#1F8A70',
        "aktiv-orange": '#FC9B4C',
        "aktiv-red": '#EB5757',

      },
      fontFamily: {
        "Neue-Plak-bold": "Neue Plak bold"
      }
    },
  },
  plugins: [],
}