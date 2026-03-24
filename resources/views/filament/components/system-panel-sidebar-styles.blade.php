{{-- Design system overrides for system panel per DESIGN.md --}}
<style>
    /* Typography: JetBrains Mono for headings */
    .fi-panel-system h1,
    .fi-panel-system h2,
    .fi-panel-system h3,
    .fi-panel-system h4,
    .fi-panel-system .fi-header-heading {
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        letter-spacing: -0.02em;
    }
    .fi-panel-system h1,
    .fi-panel-system .fi-header-heading {
        font-weight: 700;
        letter-spacing: -0.03em;
    }
    .fi-panel-system .fi-ta-cell,
    .fi-panel-system td {
        font-variant-numeric: tabular-nums;
    }
    .fi-panel-system .fi-icon-btn {
        min-width: 2.75rem;
        min-height: 2.75rem;
    }
    .fi-panel-system .fi-input {
        min-height: 2.75rem;
    }

    .fi-panel-system .fi-sidebar-header {
        height: 3rem;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    .fi-panel-system .fi-sidebar-header-ctn {
        padding: 0 0;
    }
    .fi-panel-system .fi-sidebar-nav {
        padding: 0.5rem 0.75rem 0.75rem;
        gap: 0.5rem;
    }
    .fi-panel-system .fi-sidebar-nav-groups {
        gap: 0.5rem;
    }
    .fi-panel-system .fi-sidebar-group {
        gap: 0.125rem;
    }
    .fi-panel-system .fi-sidebar-group-btn {
        padding: 0.375rem 0.5rem;
        gap: 0.5rem;
    }
    .fi-panel-system .fi-sidebar-group-items {
        gap: 0.125rem;
    }
    .fi-panel-system .fi-sidebar-item-btn {
        padding: 0.375rem 0.5rem;
        gap: 0.5rem;
    }
    .fi-panel-system .fi-sidebar-footer {
        margin: 0.5rem 0.75rem;
        gap: 0.375rem;
    }
    .fi-panel-system .fi-sidebar-sub-group-items {
        gap: 0.125rem;
    }
    .fi-panel-system .fi-sidebar-database-notifications-btn {
        padding: 0.375rem 0.5rem;
        gap: 0.5rem;
    }

    /* Sidebar collapsed: CSS tooltip on hover */
    .fi-panel-system .fi-sidebar[data-collapsed] .fi-sidebar-item-btn {
        position: relative;
        min-width: 2.75rem;
        min-height: 2.75rem;
    }
    .fi-panel-system .fi-sidebar[data-collapsed] .fi-sidebar-item-btn::after {
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
    .fi-panel-system .fi-sidebar[data-collapsed] .fi-sidebar-item-btn:hover::after {
        opacity: 1;
    }
    .fi-panel-system .fi-sidebar[data-collapsed] .fi-sidebar-item-btn:not([x-tooltip])::after {
        display: none;
    }

    /* Mobile: improve Filament table readability */
    @media (max-width: 639px) {
        .fi-panel-system .fi-ta-ctn {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .fi-panel-system .fi-ta-cell {
            font-size: 0.8125rem;
        }
        .fi-panel-system .fi-ta-row td {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        .fi-panel-system .fi-ta-search-field {
            width: 100%;
        }
    }
</style>
