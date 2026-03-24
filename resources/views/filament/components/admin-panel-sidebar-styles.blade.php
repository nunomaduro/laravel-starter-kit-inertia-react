{{-- Design system overrides for admin panel per DESIGN.md --}}
<style>
    /* Typography: JetBrains Mono for headings, IBM Plex Sans for body */
    .fi-panel-admin h1,
    .fi-panel-admin h2,
    .fi-panel-admin h3,
    .fi-panel-admin h4,
    .fi-panel-admin .fi-header-heading {
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        letter-spacing: -0.02em;
    }
    .fi-panel-admin h1,
    .fi-panel-admin .fi-header-heading {
        font-weight: 700;
        letter-spacing: -0.03em;
    }

    /* Tabular numerals for data cells */
    .fi-panel-admin .fi-ta-cell,
    .fi-panel-admin td {
        font-variant-numeric: tabular-nums;
    }

    /* Touch targets: minimum 44px on interactive elements */
    .fi-panel-admin .fi-icon-btn {
        min-width: 2.75rem;
        min-height: 2.75rem;
    }
    .fi-panel-admin .fi-input {
        min-height: 2.75rem;
    }

    /* Compact sidebar: less spacing, more room for main content */
    .fi-panel-admin .fi-sidebar-header {
        height: 3rem;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    .fi-panel-admin .fi-sidebar-header-ctn {
        padding: 0 0;
    }
    .fi-panel-admin .fi-sidebar-nav {
        padding: 0.5rem 0.75rem 0.75rem;
        gap: 0.5rem;
    }
    .fi-panel-admin .fi-sidebar-nav-groups {
        gap: 0.5rem;
    }
    .fi-panel-admin .fi-sidebar-group {
        gap: 0.125rem;
    }
    .fi-panel-admin .fi-sidebar-group-btn {
        padding: 0.375rem 0.5rem;
        gap: 0.5rem;
    }
    .fi-panel-admin .fi-sidebar-group-items {
        gap: 0.125rem;
    }
    .fi-panel-admin .fi-sidebar-item-btn {
        padding: 0.375rem 0.5rem;
        gap: 0.5rem;
    }
    .fi-panel-admin .fi-sidebar-footer {
        margin: 0.5rem 0.75rem;
        gap: 0.375rem;
    }
    .fi-panel-admin .fi-sidebar-sub-group-items {
        gap: 0.125rem;
    }
    .fi-panel-admin .fi-sidebar-database-notifications-btn {
        padding: 0.375rem 0.5rem;
        gap: 0.5rem;
    }

    /* Sidebar collapsed: proper touch targets */
    .fi-panel-admin .fi-sidebar[data-collapsed] .fi-sidebar-item-btn {
        min-width: 2.75rem;
        min-height: 2.75rem;
    }

    /* Sidebar collapsed: CSS tooltip on hover */
    .fi-panel-admin .fi-sidebar[data-collapsed] .fi-sidebar-item-btn {
        position: relative;
    }
    .fi-panel-admin .fi-sidebar[data-collapsed] .fi-sidebar-item-btn::after {
        content: attr(x-tooltip);
        position: absolute;
        left: calc(100% + 0.5rem);
        top: 50%;
        transform: translateY(-50%);
        background: var(--gray-900, #111);
        color: var(--gray-100, #f5f5f5);
        padding: 0.25rem 0.625rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-family: 'IBM Plex Sans', sans-serif;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 150ms ease;
        z-index: 50;
    }
    .fi-panel-admin .fi-sidebar[data-collapsed] .fi-sidebar-item-btn:hover::after {
        opacity: 1;
    }
    /* Hide tooltip if no x-tooltip attr present */
    .fi-panel-admin .fi-sidebar[data-collapsed] .fi-sidebar-item-btn:not([x-tooltip])::after {
        display: none;
    }

    /* Mobile: improve Filament table readability */
    @media (max-width: 639px) {
        /* Prevent table from overflowing with horizontal scroll */
        .fi-panel-admin .fi-ta-ctn {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        /* Smaller text on mobile table cells */
        .fi-panel-admin .fi-ta-cell {
            font-size: 0.8125rem;
        }
        /* Tighter padding on mobile */
        .fi-panel-admin .fi-ta-row td {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        /* Ensure search input is full width on mobile */
        .fi-panel-admin .fi-ta-search-field {
            width: 100%;
        }
    }
</style>
