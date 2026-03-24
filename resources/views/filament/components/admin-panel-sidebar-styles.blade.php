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

    /* Sidebar collapsed: add tooltips via title attr (CSS fallback) */
    .fi-panel-admin .fi-sidebar[data-collapsed] .fi-sidebar-item-btn {
        min-width: 2.75rem;
        min-height: 2.75rem;
    }
</style>
