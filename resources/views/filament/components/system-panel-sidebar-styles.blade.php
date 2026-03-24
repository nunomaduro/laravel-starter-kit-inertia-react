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

    /* Sidebar collapsed: touch targets (tooltips are native via Alpine x-tooltip) */
    .fi-panel-system .fi-sidebar[data-collapsed] .fi-sidebar-item-btn {
        min-width: 2.75rem;
        min-height: 2.75rem;
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
