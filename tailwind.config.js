const plugin = require("tailwindcss/plugin");

module.exports = {
    important: true,
    content: [
        "./app/**/*.php",
        "./views/**/*.php",
        "./spa/**/*.{js,jsx,ts,tsx}",
        "./blocks/**/*.php",
    ],
    theme: {
        extend: {
            height: {
                'ec-input': '42px',
                'ec-attribute': '48px'
            },
            boxShadow: {
                "settings-shadow": "0px 30px 34px -2px #0000001F",
            },
            keyframes: {
                spin: {
                    from: { transform: "rotate(0deg)" },
                    to: { transform: "rotate(360deg)" },
                },
            },
            animation: {
                spin: "spin 2s linear infinite",
            },
            backgroundImage: {
                "dashboard-total-sales":
                    "linear-gradient(137.15deg, #FFF9F9 0%, #FFEDF8 98.53%)",

            },
            fontFamily: {
                inter: ['"Inter"', "sans-serif"],
            },
            screens: {
                "ec-db-md": "874px",
                "ec-db-lg": "1920px",
            },
            colors: {
                "ec-title": "#121216",
            },
        },
    },
    plugins: [
    ],
};
