export interface DataTableTranslations {
    // Pagination
    totalResults: (count: number) => string;
    showingRange: (from: number, to: number, total: number) => string;
    rowsPerPage: string;
    pageOf: (current: number, last: number) => string;

    // Columns
    columns: string;
    reorder: string;
    done: string;

    // Export
    export: string;
    exportFormat: string;

    // Filters
    filter: string;
    search: string;
    operators: string;
    clearAllFilters: string;
    noResults: string;
    pressEnterToFilter: string;

    // Filter operators
    opContains: string;
    opExact: string;
    opEquals: string;
    opNotEquals: string;
    opGreaterThan: string;
    opGreaterOrEqual: string;
    opLessThan: string;
    opLessOrEqual: string;
    opBetween: string;
    opIs: string;
    opIsNot: string;
    opOnDate: string;
    opBefore: string;
    opAfter: string;

    // Boolean
    yes: string;
    no: string;

    // Bulk actions
    selected: (count: number) => string;

    // Select all
    selectAll: string;
    selectRow: string;

    // Quick views
    view: string;
    quickViews: string;
    savedViews: string;
    saveFilters: string;
    manageViews: string;
    viewName: string;
    viewNamePlaceholder: string;
    viewWillBeSaved: string;
    viewSaveError: string;
    viewDeleteError: string;
    viewLoadError: string;
    myViews: string;
    teamViews: string;
    systemViews: string;
    shareWithTeam: string;
    sharedBadge: string;
    systemBadge: string;
    filtersLabel: string;
    none: string;
    sortLabel: string;
    columnsCount: (visible: number, total: number) => string;
    cancel: string;
    save: string;

    // Number format
    min: string;
    max: string;
    value: string;

    // Empty state
    noData: string;

    // Row actions
    actions: string;

    // Confirmation dialog
    confirmTitle: string;
    confirmDescription: string;
    confirmAction: string;
    confirmCancel: string;

    // Server-side selection
    selectAllMatching: (count: number) => string;
    clearSelection: string;

    // Inline editing
    editSave: string;
    editCancel: string;
    editSaving: string;

    // Loading
    loading: string;

    // Print
    print: string;

    // Detail row
    expand: string;
    collapse: string;

    // Soft deletes
    showTrashed: string;
    hideTrashed: string;

    // Summary
    summarySum: string;
    summaryAvg: string;
    summaryMin: string;
    summaryMax: string;
    summaryCount: string;

    // Polling
    autoRefresh: string;

    // Toggle
    toggleOn: string;
    toggleOff: string;

    // Density
    density: string;
    densityCompact: string;
    densityComfortable: string;
    densitySpacious: string;

    // Copy
    copied: string;
    copyToClipboard: string;

    // Context menu
    sortAscending: string;
    sortDescending: string;
    hideColumn: string;
    pinLeft: string;
    pinRight: string;
    unpin: string;

    // Row grouping
    groupBy: string;
    ungrouped: string;

    // Row reorder
    reorderRows: string;

    // Batch edit
    batchEdit: string;
    batchEditApply: string;
    batchEditColumn: string;
    batchEditValue: string;

    // Search highlight
    matches: (count: number) => string;

    // Import
    importData: string;
    importFile: string;
    importUploading: string;
    importSuccess: string;
    importError: string;

    // Undo/Redo
    undo: string;
    redo: string;
    editUndone: string;
    editRedone: string;

    // Column pinning UI
    pinColumn: string;

    // Keyboard shortcuts
    keyboardShortcuts: string;
    shortcutNavigation: string;
    shortcutSelect: string;
    shortcutExpand: string;
    shortcutEscape: string;
    shortcutSearch: string;
    shortcutHelp: string;
    close: string;

    // Export progress
    exporting: string;
    exportReady: string;
    exportDownload: string;

    // Empty state
    emptyTitle: string;
    emptyDescription: string;

    // Inline row creation
    addRow: string;

    // Filter chips
    activeFilters: string;

    // Summary range
    summaryRange: string;

    // Replicate / Force-delete / Restore
    replicate: string;
    forceDelete: string;
    restore: string;

    // Date grouping
    dateGroupDay: string;
    dateGroupWeek: string;
    dateGroupMonth: string;
    dateGroupYear: string;

    // Accessibility
    skipToTable: string;

    // Status bar
    statusBarSum: string;
    statusBarAvg: string;
    statusBarCount: string;
    statusBarMin: string;
    statusBarMax: string;

    // Clipboard paste
    pasteSuccess: string;
    pasteError: string;

    // Drag to fill
    dragToFill: string;

    // PDF export
    exportPdf: string;

    // Pinned rows
    pinnedRow: string;

    // Saved filters (server-persisted)
    savedFilters: string;
    saveCurrentFilters: string;
    filterName: string;
    filterNamePlaceholder: string;

    // Header filters
    headerFilterPlaceholder: string;
    headerFilterToggle: string;
    clearHeaderFilter: string;

    // Tree data
    expandAll: string;
    collapseAll: string;
    treeChildren: (count: number) => string;

    // Infinite scroll
    loadMore: string;
    loadingMore: string;
    noMoreData: string;

    // Column auto-size
    autoSizeColumn: string;
    autoSizeAllColumns: string;

    // Cell range selection
    cellsSelected: (count: number) => string;

    // Sparklines
    sparklineLabel: string;

    // Overflow menu
    more: string;

    // AI assistant
    aiAssistant: string;
    aiPlaceholder: string;
    aiQuerying: string;
    aiInsights: string;
    aiInsightsLoading: string;
    aiSuggestions: string;
    aiSuggestionsLoading: string;
    aiColumnSummary: string;
    aiColumnSummaryLoading: string;
    aiEnrich: string;
    aiEnrichPrompt: string;
    aiEnrichColumnName: string;
    aiEnrichLoading: string;
    aiEnrichSuccess: (count: number) => string;
    aiError: string;
    aiApply: string;
    aiAnomaly: string;
    aiTrend: string;
    aiPattern: string;
    aiRecommendation: string;
    aiVisualize: string;
    aiVisualizePrompt: string;
    aiVisualizeLoading: string;
    aiVisualizeGenerate: string;
    aiNoInsights: string;
    aiNoSuggestions: string;
    aiEnrichDescription: (count: number) => string;
    aiSelectColumn: string;
    aiRefresh: string;

    // Pivot
    pivotMode: string;
    pivotRowFields: string;
    pivotColumnFields: string;
    pivotValueField: string;
    pivotAggregation: string;

    // Window scroller
    scrollToTop: string;

    // API ref
    goToRow: string;

    // Analytics
    analyticsTitle: string;
    analyticsChange: string;
    analyticsNoChange: string;

    // Layout switcher
    layoutTable: string;
    layoutGrid: string;
    layoutCards: string;
    layoutKanban: string;
    switchLayout: string;

    // Column statistics
    columnStats: string;
    statsCount: string;
    statsNulls: string;
    statsUnique: string;
    statsMedian: string;
    statsDistribution: string;

    // Conditional formatting
    conditionalFormatting: string;
    addRule: string;
    removeRule: string;
    formatColumn: string;
    formatOperator: string;
    formatValue: string;
    formatBackground: string;
    formatTextColor: string;
    formatBold: string;
    noRules: string;

    // Faceted filters
    facetedAll: string;
    facetedClear: string;

    // Presence
    presenceViewing: string;
    presenceEditing: string;
    presenceUsers: (count: number) => string;

    // Spreadsheet mode
    spreadsheetMode: string;
    tabToNext: string;
    enterToConfirm: string;
    escapeToCancel: string;

    // Kanban
    kanbanNoColumn: string;
    kanbanMoveCard: string;
    kanbanEmpty: string;

    // Master/Detail
    masterDetailExpand: string;
    masterDetailCollapse: string;
    masterDetailLoading: string;

    // Integrated Charts
    chartColumn: string;
    chartType: string;
    chartBar: string;
    chartLine: string;
    chartPie: string;
    chartDoughnut: string;
    chartClose: string;
    chartTitle: string;
    chartNoData: string;

    // Find & Replace
    findReplace: string;
    findPlaceholder: string;
    replacePlaceholder: string;
    findNext: string;
    findPrevious: string;
    replaceOne: string;
    replaceAll: string;
    findMatchesCount: (current: number, total: number) => string;
    findNoMatches: string;
    findCaseSensitive: string;
    replaceSuccess: (count: number) => string;
}

export const defaultTranslations: DataTableTranslations = {
    // Pagination
    totalResults: (count) => `${count} result${count !== 1 ? "s" : ""}`,
    showingRange: (from, to, total) => `Showing ${from}\u2013${to} of ${total}`,
    rowsPerPage: "Rows per page",
    pageOf: (current, last) => `Page ${current} / ${last}`,

    // Columns
    columns: "Columns",
    reorder: "Reorder",
    done: "Done",

    // Export
    export: "Export",
    exportFormat: "Export format",

    // Filters
    filter: "Filter",
    search: "Search...",
    operators: "Operators",
    clearAllFilters: "Clear all filters",
    noResults: "No results.",
    pressEnterToFilter: "Press Enter to filter",

    // Filter operators
    opContains: "contains",
    opExact: "is exactly",
    opEquals: "=",
    opNotEquals: "≠",
    opGreaterThan: ">",
    opGreaterOrEqual: "≥",
    opLessThan: "<",
    opLessOrEqual: "≤",
    opBetween: "between",
    opIs: "is",
    opIsNot: "is not",
    opOnDate: "on",
    opBefore: "before",
    opAfter: "after",

    // Boolean
    yes: "Yes",
    no: "No",

    // Bulk actions
    selected: (count) => `${count} selected`,

    // Select all
    selectAll: "Select all",
    selectRow: "Select row",

    // Quick views
    view: "View",
    quickViews: "Quick views",
    savedViews: "Saved views",
    saveFilters: "Save filters",
    manageViews: "Manage views",
    viewName: "View name",
    viewNamePlaceholder: "e.g. Recent items without photos",
    viewWillBeSaved: "Save the current view configuration for quick access.",
    viewSaveError: "Failed to save view. Please try again.",
    viewDeleteError: "Failed to delete view. Please try again.",
    viewLoadError: "Failed to load saved views.",
    myViews: "My Views",
    teamViews: "Team Views",
    systemViews: "System Views",
    shareWithTeam: "Share with team",
    sharedBadge: "Shared",
    systemBadge: "System",
    filtersLabel: "Filters:",
    none: "None",
    sortLabel: "Sort:",
    columnsCount: (visible, total) => `${visible}/${total} visible`,
    cancel: "Cancel",
    save: "Save",

    // Number format
    min: "Min",
    max: "Max",
    value: "Value",

    // Empty state
    noData: "No results.",

    // Row actions
    actions: "Actions",

    // Confirmation dialog
    confirmTitle: "Are you sure?",
    confirmDescription: "This action cannot be undone.",
    confirmAction: "Confirm",
    confirmCancel: "Cancel",

    // Server-side selection
    selectAllMatching: (count) => `Select all ${count} matching items`,
    clearSelection: "Clear selection",

    // Inline editing
    editSave: "Save",
    editCancel: "Cancel",
    editSaving: "Saving...",

    // Loading
    loading: "Loading...",

    // Print
    print: "Print",

    // Detail row
    expand: "Expand",
    collapse: "Collapse",

    // Soft deletes
    showTrashed: "Show deleted",
    hideTrashed: "Hide deleted",

    // Summary
    summarySum: "Sum",
    summaryAvg: "Average",
    summaryMin: "Min",
    summaryMax: "Max",
    summaryCount: "Count",

    // Polling
    autoRefresh: "Auto-refresh",

    // Toggle
    toggleOn: "On",
    toggleOff: "Off",

    // Density
    density: "Density",
    densityCompact: "Compact",
    densityComfortable: "Comfortable",
    densitySpacious: "Spacious",

    // Copy
    copied: "Copied!",
    copyToClipboard: "Copy to clipboard",

    // Context menu
    sortAscending: "Sort ascending",
    sortDescending: "Sort descending",
    hideColumn: "Hide column",
    pinLeft: "Pin to left",
    pinRight: "Pin to right",
    unpin: "Unpin",

    // Row grouping
    groupBy: "Group",
    ungrouped: "Ungrouped",

    // Row reorder
    reorderRows: "Reorder rows",

    // Batch edit
    batchEdit: "Batch edit",
    batchEditApply: "Apply to selected",
    batchEditColumn: "Column",
    batchEditValue: "Value",

    // Search highlight
    matches: (count) => `${count} match${count !== 1 ? "es" : ""}`,

    // Import
    importData: "Import",
    importFile: "Select file",
    importUploading: "Uploading...",
    importSuccess: "Import successful",
    importError: "Import failed",

    // Undo/Redo
    undo: "Undo",
    redo: "Redo",
    editUndone: "Edit undone",
    editRedone: "Edit redone",

    // Column pinning UI
    pinColumn: "Pin column",

    // Keyboard shortcuts
    keyboardShortcuts: "Keyboard shortcuts",
    shortcutNavigation: "Navigate rows",
    shortcutSelect: "Select / deselect row",
    shortcutExpand: "Expand / collapse row",
    shortcutEscape: "Clear selection / close",
    shortcutSearch: "Focus search",
    shortcutHelp: "Show shortcuts",
    close: "Close",

    // Export progress
    exporting: "Exporting...",
    exportReady: "Export ready",
    exportDownload: "Download",

    // Empty state
    emptyTitle: "No data yet",
    emptyDescription: "There are no records to display. Try adjusting your filters or adding new data.",

    // Inline row creation
    addRow: "Add row",

    // Filter chips
    activeFilters: "Active filters",

    // Summary range
    summaryRange: "Range",

    // Replicate / Force-delete / Restore
    replicate: "Duplicate",
    forceDelete: "Permanently delete",
    restore: "Restore",

    // Date grouping
    dateGroupDay: "Day",
    dateGroupWeek: "Week",
    dateGroupMonth: "Month",
    dateGroupYear: "Year",

    // Accessibility
    skipToTable: "Skip to table",

    // Status bar
    statusBarSum: "Sum",
    statusBarAvg: "Avg",
    statusBarCount: "Count",
    statusBarMin: "Min",
    statusBarMax: "Max",

    // Clipboard paste
    pasteSuccess: "Pasted successfully",
    pasteError: "Paste failed",

    // Drag to fill
    dragToFill: "Drag to fill",

    // PDF export
    exportPdf: "PDF",

    // Pinned rows
    pinnedRow: "Pinned",

    // Saved filters (server-persisted)
    savedFilters: "Saved filters",
    saveCurrentFilters: "Save current filters",
    filterName: "Filter name",
    filterNamePlaceholder: "e.g. Active premium users",

    // Header filters
    headerFilterPlaceholder: "Filter...",
    headerFilterToggle: "Column filters",
    clearHeaderFilter: "Clear filter",

    // Tree data
    expandAll: "Expand all",
    collapseAll: "Collapse all",
    treeChildren: (count) => `${count} child${count !== 1 ? "ren" : ""}`,

    // Infinite scroll
    loadMore: "Load more",
    loadingMore: "Loading more...",
    noMoreData: "No more data",

    // Column auto-size
    autoSizeColumn: "Auto-size column",
    autoSizeAllColumns: "Auto-size all columns",

    // Cell range selection
    cellsSelected: (count) => `${count} cell${count !== 1 ? "s" : ""} selected`,

    // Sparklines
    sparklineLabel: "Trend",

    // Overflow menu
    more: "More",

    // AI assistant
    aiAssistant: "AI Assistant",
    aiPlaceholder: "Ask about your data...",
    aiQuerying: "Analyzing...",
    aiInsights: "AI Insights",
    aiInsightsLoading: "Generating insights...",
    aiSuggestions: "Suggestions",
    aiSuggestionsLoading: "Getting suggestions...",
    aiColumnSummary: "AI Summary",
    aiColumnSummaryLoading: "Analyzing column...",
    aiEnrich: "AI Enrich",
    aiEnrichPrompt: "Describe what to generate...",
    aiEnrichColumnName: "New column name",
    aiEnrichLoading: "Enriching rows...",
    aiEnrichSuccess: (count) => `Enriched ${count} row${count !== 1 ? "s" : ""}`,
    aiError: "AI request failed",
    aiApply: "Apply",
    aiAnomaly: "Anomaly",
    aiTrend: "Trend",
    aiPattern: "Pattern",
    aiRecommendation: "Recommendation",
    aiVisualize: "Visualize",
    aiVisualizePrompt: "Describe the visualization you want...",
    aiVisualizeLoading: "Generating visualization...",
    aiVisualizeGenerate: "Generate",
    aiNoInsights: "No insights yet. Click to analyze your data.",
    aiNoSuggestions: "No suggestions yet. Click to get recommendations.",
    aiEnrichDescription: (count) => count > 0 ? `Generate AI values for ${count} selected row${count !== 1 ? "s" : ""}.` : "Generate AI values for all visible rows.",
    aiSelectColumn: "Select a column...",
    aiRefresh: "Refresh",

    // Pivot
    pivotMode: "Pivot mode",
    pivotRowFields: "Row fields",
    pivotColumnFields: "Column fields",
    pivotValueField: "Value field",
    pivotAggregation: "Aggregation",

    // Window scroller
    scrollToTop: "Scroll to top",

    // API ref
    goToRow: "Go to row",

    // Analytics
    analyticsTitle: "Analytics",
    analyticsChange: "change",
    analyticsNoChange: "no change",

    // Layout switcher
    layoutTable: "Table",
    layoutGrid: "Grid",
    layoutCards: "Cards",
    layoutKanban: "Kanban",
    switchLayout: "Switch layout",

    // Column statistics
    columnStats: "Column statistics",
    statsCount: "Count",
    statsNulls: "Nulls",
    statsUnique: "Unique",
    statsMedian: "Median",
    statsDistribution: "Distribution",

    // Conditional formatting
    conditionalFormatting: "Conditional formatting",
    addRule: "Add rule",
    removeRule: "Remove",
    formatColumn: "Column",
    formatOperator: "Condition",
    formatValue: "Value",
    formatBackground: "Background",
    formatTextColor: "Text color",
    formatBold: "Bold",
    noRules: "No formatting rules yet",

    // Faceted filters
    facetedAll: "All",
    facetedClear: "Clear",

    // Presence
    presenceViewing: "viewing",
    presenceEditing: "editing",
    presenceUsers: (count) => `${count} user${count !== 1 ? "s" : ""} online`,

    // Spreadsheet mode
    spreadsheetMode: "Spreadsheet mode",
    tabToNext: "Tab to next cell",
    enterToConfirm: "Enter to confirm",
    escapeToCancel: "Escape to cancel",

    // Kanban
    kanbanNoColumn: "Select a column for kanban lanes",
    kanbanMoveCard: "Move card",
    kanbanEmpty: "No items",

    // Master/Detail
    masterDetailExpand: "Show details",
    masterDetailCollapse: "Hide details",
    masterDetailLoading: "Loading details...",

    // Integrated Charts
    chartColumn: "Column",
    chartType: "Chart type",
    chartBar: "Bar",
    chartLine: "Line",
    chartPie: "Pie",
    chartDoughnut: "Doughnut",
    chartClose: "Close chart",
    chartTitle: "Chart",
    chartNoData: "No numeric data to chart",

    // Find & Replace
    findReplace: "Find & Replace",
    findPlaceholder: "Find...",
    replacePlaceholder: "Replace with...",
    findNext: "Next",
    findPrevious: "Previous",
    replaceOne: "Replace",
    replaceAll: "Replace all",
    findMatchesCount: (current, total) => `${current} of ${total}`,
    findNoMatches: "No matches",
    findCaseSensitive: "Match case",
    replaceSuccess: (count) => `Replaced ${count} match${count !== 1 ? "es" : ""}`,
};

export const frTranslations: DataTableTranslations = {
    totalResults: (count) => `${count} résultat${count !== 1 ? "s" : ""}`,
    showingRange: (from, to, total) => `Affichage ${from}\u2013${to} sur ${total}`,
    rowsPerPage: "Lignes par page",
    pageOf: (current, last) => `Page ${current} / ${last}`,
    columns: "Colonnes",
    reorder: "Réordonner",
    done: "Terminé",
    export: "Exporter",
    exportFormat: "Format d'export",
    filter: "Filtrer",
    search: "Rechercher...",
    operators: "Opérateurs",
    clearAllFilters: "Effacer tous les filtres",
    noResults: "Aucun résultat.",
    pressEnterToFilter: "Appuyez sur Entrée pour filtrer",
    opContains: "contient",
    opExact: "est exactement",
    opEquals: "=",
    opNotEquals: "≠",
    opGreaterThan: ">",
    opGreaterOrEqual: "≥",
    opLessThan: "<",
    opLessOrEqual: "≤",
    opBetween: "entre",
    opIs: "est",
    opIsNot: "n'est pas",
    opOnDate: "est le",
    opBefore: "avant le",
    opAfter: "après le",
    yes: "Oui",
    no: "Non",
    selected: (count) => `${count} sélectionné${count > 1 ? "s" : ""}`,
    selectAll: "Tout sélectionner",
    selectRow: "Sélectionner la ligne",
    quickViews: "Vues rapides",
    savedViews: "Vues sauvegardées",
    saveFilters: "Sauvegarder les filtres",
    manageViews: "Gérer les vues",
    viewName: "Nom de la vue",
    viewNamePlaceholder: "Ex: Occasions récentes sans photo",
    viewWillBeSaved: "Sauvegarder la configuration de la vue pour un accès rapide.",
    viewSaveError: "Impossible de sauvegarder la vue. Veuillez réessayer.",
    viewDeleteError: "Impossible de supprimer la vue. Veuillez réessayer.",
    viewLoadError: "Impossible de charger les vues sauvegardées.",
    myViews: "Mes vues",
    teamViews: "Vues d'équipe",
    systemViews: "Vues système",
    shareWithTeam: "Partager avec l'équipe",
    sharedBadge: "Partagé",
    systemBadge: "Système",
    filtersLabel: "Filtres :",
    none: "Aucun",
    sortLabel: "Tri :",
    columnsCount: (visible, total) => `${visible}/${total} visibles`,
    cancel: "Annuler",
    save: "Sauvegarder",
    view: "Vue",
    min: "Min",
    max: "Max",
    value: "Valeur",
    noData: "Aucun résultat.",
    actions: "Actions",
    confirmTitle: "Êtes-vous sûr ?",
    confirmDescription: "Cette action est irréversible.",
    confirmAction: "Confirmer",
    confirmCancel: "Annuler",

    selectAllMatching: (count) => `Sélectionner les ${count} éléments correspondants`,
    clearSelection: "Effacer la sélection",

    editSave: "Enregistrer",
    editCancel: "Annuler",
    editSaving: "Enregistrement...",

    loading: "Chargement...",

    print: "Imprimer",

    // Detail row
    expand: "Développer",
    collapse: "Réduire",

    // Soft deletes
    showTrashed: "Afficher les supprimés",
    hideTrashed: "Masquer les supprimés",

    // Summary
    summarySum: "Somme",
    summaryAvg: "Moyenne",
    summaryMin: "Min",
    summaryMax: "Max",
    summaryCount: "Nombre",

    // Polling
    autoRefresh: "Rafraîchissement auto",

    // Toggle
    toggleOn: "Activé",
    toggleOff: "Désactivé",

    // Density
    density: "Densité",
    densityCompact: "Compact",
    densityComfortable: "Confortable",
    densitySpacious: "Spacieux",

    // Copy
    copied: "Copié !",
    copyToClipboard: "Copier dans le presse-papier",

    // Context menu
    sortAscending: "Tri croissant",
    sortDescending: "Tri décroissant",
    hideColumn: "Masquer la colonne",
    pinLeft: "Épingler à gauche",
    pinRight: "Épingler à droite",
    unpin: "Désépingler",

    // Row grouping
    groupBy: "Grouper",
    ungrouped: "Non groupé",

    // Row reorder
    reorderRows: "Réordonner les lignes",

    // Batch edit
    batchEdit: "Édition en lot",
    batchEditApply: "Appliquer à la sélection",
    batchEditColumn: "Colonne",
    batchEditValue: "Valeur",

    // Search highlight
    matches: (count) => `${count} résultat${count !== 1 ? "s" : ""}`,

    // Import
    importData: "Importer",
    importFile: "Sélectionner un fichier",
    importUploading: "Envoi en cours...",
    importSuccess: "Import réussi",
    importError: "Échec de l'import",

    // Undo/Redo
    undo: "Annuler",
    redo: "Refaire",
    editUndone: "Modification annulée",
    editRedone: "Modification rétablie",

    // Column pinning UI
    pinColumn: "Épingler la colonne",

    // Keyboard shortcuts
    keyboardShortcuts: "Raccourcis clavier",
    shortcutNavigation: "Naviguer entre les lignes",
    shortcutSelect: "Sélectionner / désélectionner",
    shortcutExpand: "Développer / réduire",
    shortcutEscape: "Effacer la sélection / fermer",
    shortcutSearch: "Rechercher",
    shortcutHelp: "Afficher les raccourcis",
    close: "Fermer",

    // Export progress
    exporting: "Export en cours...",
    exportReady: "Export prêt",
    exportDownload: "Télécharger",

    // Empty state
    emptyTitle: "Aucune donnée",
    emptyDescription: "Il n'y a aucun enregistrement à afficher. Essayez de modifier vos filtres ou d'ajouter des données.",

    // Inline row creation
    addRow: "Ajouter une ligne",

    // Filter chips
    activeFilters: "Filtres actifs",

    // Summary range
    summaryRange: "Plage",

    // Replicate / Force-delete / Restore
    replicate: "Dupliquer",
    forceDelete: "Supprimer définitivement",
    restore: "Restaurer",

    // Date grouping
    dateGroupDay: "Jour",
    dateGroupWeek: "Semaine",
    dateGroupMonth: "Mois",
    dateGroupYear: "Année",

    // Accessibility
    skipToTable: "Aller au tableau",

    // Status bar
    statusBarSum: "Somme",
    statusBarAvg: "Moy.",
    statusBarCount: "Nombre",
    statusBarMin: "Min",
    statusBarMax: "Max",

    // Clipboard paste
    pasteSuccess: "Collé avec succès",
    pasteError: "Échec du collage",

    // Drag to fill
    dragToFill: "Glisser pour remplir",

    // PDF export
    exportPdf: "PDF",

    // Pinned rows
    pinnedRow: "Épinglée",

    // Saved filters (server-persisted)
    savedFilters: "Filtres sauvegardés",
    saveCurrentFilters: "Sauvegarder les filtres actuels",
    filterName: "Nom du filtre",
    filterNamePlaceholder: "ex. Utilisateurs premium actifs",

    // Header filters
    headerFilterPlaceholder: "Filtrer...",
    clearHeaderFilter: "Effacer le filtre",

    // Tree data
    expandAll: "Tout développer",
    collapseAll: "Tout réduire",
    treeChildren: (count) => `${count} enfant${count !== 1 ? "s" : ""}`,

    // Infinite scroll
    loadMore: "Charger plus",
    loadingMore: "Chargement...",
    noMoreData: "Plus de données",

    // Column auto-size
    autoSizeColumn: "Ajuster la colonne",
    autoSizeAllColumns: "Ajuster toutes les colonnes",

    // Cell range selection
    cellsSelected: (count) => `${count} cellule${count !== 1 ? "s" : ""} sélectionnée${count !== 1 ? "s" : ""}`,

    // Sparklines
    sparklineLabel: "Tendance",

    // AI assistant
    aiAssistant: "Assistant IA",
    aiPlaceholder: "Posez une question sur vos données...",
    aiQuerying: "Analyse en cours...",
    aiInsights: "Analyses IA",
    aiInsightsLoading: "Génération des analyses...",
    aiSuggestions: "Suggestions",
    aiSuggestionsLoading: "Recherche de suggestions...",
    aiColumnSummary: "Résumé IA",
    aiColumnSummaryLoading: "Analyse de la colonne...",
    aiEnrich: "Enrichissement IA",
    aiEnrichPrompt: "Décrivez ce qu'il faut générer...",
    aiEnrichColumnName: "Nom de la nouvelle colonne",
    aiEnrichLoading: "Enrichissement en cours...",
    aiEnrichSuccess: (count) => `${count} ligne${count !== 1 ? "s" : ""} enrichie${count !== 1 ? "s" : ""}`,
    aiError: "La requête IA a échoué",
    aiApply: "Appliquer",
    aiAnomaly: "Anomalie",
    aiTrend: "Tendance",
    aiPattern: "Motif",
    aiRecommendation: "Recommandation",
    aiVisualize: "Visualiser",
    aiVisualizePrompt: "Décrivez la visualisation souhaitée...",
    aiVisualizeLoading: "Génération de la visualisation...",
    aiVisualizeGenerate: "Générer",
    aiNoInsights: "Aucune analyse pour l'instant. Cliquez pour analyser vos données.",
    aiNoSuggestions: "Aucune suggestion pour l'instant. Cliquez pour obtenir des recommandations.",
    aiEnrichDescription: (count) => count > 0 ? `Générer des valeurs IA pour ${count} ligne${count !== 1 ? "s" : ""} sélectionnée${count !== 1 ? "s" : ""}.` : "Générer des valeurs IA pour toutes les lignes visibles.",
    aiSelectColumn: "Sélectionner une colonne...",
    aiRefresh: "Actualiser",

    // Pivot
    pivotMode: "Mode tableau croisé",
    pivotRowFields: "Champs lignes",
    pivotColumnFields: "Champs colonnes",
    pivotValueField: "Champ valeur",
    pivotAggregation: "Agrégation",

    // Window scroller
    scrollToTop: "Retour en haut",

    // API ref
    goToRow: "Aller à la ligne",

    // Analytics
    analyticsTitle: "Analytique",
    analyticsChange: "variation",
    analyticsNoChange: "aucune variation",

    // Layout switcher
    layoutTable: "Tableau",
    layoutGrid: "Grille",
    layoutCards: "Cartes",
    layoutKanban: "Kanban",
    switchLayout: "Changer la disposition",

    // Column statistics
    columnStats: "Statistiques de colonne",
    statsCount: "Nombre",
    statsNulls: "Valeurs nulles",
    statsUnique: "Valeurs uniques",
    statsMedian: "Médiane",
    statsDistribution: "Distribution",

    // Conditional formatting
    conditionalFormatting: "Mise en forme conditionnelle",
    addRule: "Ajouter une règle",
    removeRule: "Supprimer",
    formatColumn: "Colonne",
    formatOperator: "Condition",
    formatValue: "Valeur",
    formatBackground: "Arrière-plan",
    formatTextColor: "Couleur du texte",
    formatBold: "Gras",
    noRules: "Aucune règle de formatage",

    // Faceted filters
    facetedAll: "Tous",
    facetedClear: "Effacer",

    // Presence
    presenceViewing: "en consultation",
    presenceEditing: "en édition",
    presenceUsers: (count) => `${count} utilisateur${count !== 1 ? "s" : ""} en ligne`,

    // Spreadsheet mode
    spreadsheetMode: "Mode tableur",
    tabToNext: "Tab pour la cellule suivante",
    enterToConfirm: "Entrée pour confirmer",
    escapeToCancel: "Échap pour annuler",

    // Kanban
    kanbanNoColumn: "Sélectionnez une colonne pour les colonnes kanban",
    kanbanMoveCard: "Déplacer la carte",
    kanbanEmpty: "Aucun élément",

    // Master/Detail
    masterDetailExpand: "Afficher les détails",
    masterDetailCollapse: "Masquer les détails",
    masterDetailLoading: "Chargement des détails...",

    // Integrated Charts
    chartColumn: "Colonne",
    chartType: "Type de graphique",
    chartBar: "Barres",
    chartLine: "Ligne",
    chartPie: "Camembert",
    chartDoughnut: "Anneau",
    chartClose: "Fermer le graphique",
    chartTitle: "Graphique",
    chartNoData: "Aucune donnée numérique à afficher",

    // Find & Replace
    findReplace: "Rechercher et remplacer",
    findPlaceholder: "Rechercher...",
    replacePlaceholder: "Remplacer par...",
    findNext: "Suivant",
    findPrevious: "Précédent",
    replaceOne: "Remplacer",
    replaceAll: "Tout remplacer",
    findMatchesCount: (current, total) => `${current} sur ${total}`,
    findNoMatches: "Aucun résultat",
    findCaseSensitive: "Respecter la casse",
    replaceSuccess: (count) => `${count} remplacement${count !== 1 ? "s" : ""} effectué${count !== 1 ? "s" : ""}`,
};

/**
 * Get translations for a given locale code.
 * Falls back to English if the locale is not supported.
 */
export function getTranslationsForLocale(locale?: string | null): DataTableTranslations {
    if (!locale) return defaultTranslations;
    const lang = locale.split("-")[0].toLowerCase();
    if (lang === "fr") return frTranslations;
    return defaultTranslations;
}
