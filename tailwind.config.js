import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
                body: ["Inter", ...defaultTheme.fontFamily.sans],
                heading: ["Zilla Slab", ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // Brand Colors
                "zurit-purple": "#7639C2",
                prosper: "#FF5B5D",
                "zurit-black": "#000000",
                "light-black": "#2E2E2E",
                "zurit-gray": "#6B6B6B",
                "light-gray": "#F5F3F7",

                // Pipeline Status Colors
                "status-new-lead": "#7639C2",
                "status-initial-outreach": "#7639C2",
                "status-follow-ups": "#FF5B5D",
                "status-negotiations": "#FF5B5D",
                "status-won": "#7639C2",
                "status-lost": "#6B6B6B",

                // Task Priority Colors
                "priority-low": "#6B6B6B",
                "priority-medium": "#FF5B5D",
                "priority-high": "#000000",

                // Task Status Colors
                "task-pending": "#6B6B6B",
                "task-in-progress": "#7639C2",
                "task-completed": "#7639C2",
                "task-cancelled": "#FF5B5D",

                // Activity Type Colors
                "activity-call": "#7639C2",
                "activity-email": "#FF5B5D",
                "activity-meeting": "#7639C2",
                "activity-note": "#6B6B6B",
            },
        },
    },

    plugins: [forms],
};
