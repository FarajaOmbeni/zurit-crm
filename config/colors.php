<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Colors Configuration
    |--------------------------------------------------------------------------
    |
    | Zurit CRM brand colors for various UI elements.
    | These can be accessed via config('colors.status.new_lead') etc.
    |
    */

    // Brand Colors
    'brand' => [
        'zurit-purple' => '#7639C2',
        'prosper' => '#FF5B5D',
        'black' => '#000000',
        'light-black' => '#2E2E2E',
        'gray' => '#6B6B6B',
        'light-gray' => '#F5F3F7',
    ],

    // Pipeline Status Colors (for Kanban view and status badges)
    'status' => [
        'new_lead' => '#7639C2',        // Zurit Purple
        'initial_outreach' => '#7639C2', // Zurit Purple
        'follow_ups' => '#FF5B5D',      // Prosper
        'negotiations' => '#FF5B5D',     // Prosper
        'won' => '#7639C2',             // Zurit Purple
        'lost' => '#6B6B6B',           // Gray
    ],

    // Task Priority Colors
    'priority' => [
        'low' => '#6B6B6B',      // Gray
        'medium' => '#FF5B5D',   // Prosper
        'high' => '#000000',     // Black
    ],

    // Task Status Colors
    'task_status' => [
        'pending' => '#6B6B6B',        // Gray
        'in_progress' => '#7639C2',     // Zurit Purple
        'completed' => '#7639C2',       // Zurit Purple
        'cancelled' => '#FF5B5D',       // Prosper
    ],

    // Activity Type Colors
    'activity_type' => [
        'call' => '#7639C2',      // Zurit Purple
        'email' => '#FF5B5D',     // Prosper
        'meeting' => '#7639C2',   // Zurit Purple
        'note' => '#6B6B6B',      // Gray
    ],

    // Theme Colors
    'theme' => [
        'primary' => '#7639C2',        // Zurit Purple
        'secondary' => '#FF5B5D',      // Prosper
        'success' => '#7639C2',        // Zurit Purple
        'danger' => '#FF5B5D',         // Prosper
        'warning' => '#FF5B5D',        // Prosper
        'info' => '#7639C2',           // Zurit Purple
        'dark' => '#000000',           // Black
        'light' => '#F5F3F7',          // Light Gray
    ],

    // Background Colors for Status Badges
    'status_bg' => [
        'new_lead' => '#F5F3F7',        // Light Gray
        'initial_outreach' => '#F5F3F7', // Light Gray
        'follow_ups' => '#F5F3F7',      // Light Gray
        'negotiations' => '#F5F3F7',     // Light Gray
        'won' => '#F5F3F7',             // Light Gray
        'lost' => '#F5F3F7',           // Light Gray
    ],

    // Text Colors for Status Badges
    'status_text' => [
        'new_lead' => '#7639C2',        // Zurit Purple
        'initial_outreach' => '#7639C2', // Zurit Purple
        'follow_ups' => '#FF5B5D',       // Prosper
        'negotiations' => '#FF5B5D',     // Prosper
        'won' => '#7639C2',             // Zurit Purple
        'lost' => '#6B6B6B',           // Gray
    ],
];
