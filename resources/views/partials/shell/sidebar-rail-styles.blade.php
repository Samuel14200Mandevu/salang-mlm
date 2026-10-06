<style>
/* Menu replié — icônes seules (sans texte / sections) */
html.shell-is-desktop.shell-sidebar-rail .admin-app #sidebar,
html.shell-is-desktop.shell-sidebar-rail .cashier-app #sidebar,
.admin-app #sidebar.sidebar-is-rail,
.cashier-app #sidebar.sidebar-is-rail {
    overflow: hidden;
}

html.shell-is-desktop.shell-sidebar-rail .admin-app #sidebar nav,
html.shell-is-desktop.shell-sidebar-rail .cashier-app #sidebar nav,
.admin-app #sidebar.sidebar-is-rail nav,
.cashier-app #sidebar.sidebar-is-rail nav {
    padding-left: 0.35rem;
    padding-right: 0.35rem;
}

html.shell-is-desktop.shell-sidebar-rail .admin-app #sidebar .sidebar-link,
html.shell-is-desktop.shell-sidebar-rail .cashier-app #sidebar .sidebar-link,
.admin-app #sidebar.sidebar-is-rail .sidebar-link,
.cashier-app #sidebar.sidebar-is-rail .sidebar-link {
    justify-content: center;
    align-items: center;
    gap: 0;
    padding: 0.5rem;
    overflow: hidden;
    max-width: 100%;
}

html.shell-is-desktop.shell-sidebar-rail .admin-app #sidebar li:has(.sidebar-section),
html.shell-is-desktop.shell-sidebar-rail .cashier-app #sidebar li:has(.sidebar-section),
.admin-app #sidebar.sidebar-is-rail li:has(.sidebar-section),
.cashier-app #sidebar.sidebar-is-rail li:has(.sidebar-section) {
    display: none !important;
}

html.shell-is-desktop.shell-sidebar-rail .admin-app #sidebar .sidebar-link .label,
html.shell-is-desktop.shell-sidebar-rail .cashier-app #sidebar .sidebar-link .label,
html.shell-is-desktop.shell-sidebar-rail .admin-app #sidebar .sidebar-section,
html.shell-is-desktop.shell-sidebar-rail .cashier-app #sidebar .sidebar-section,
html.shell-is-desktop.shell-sidebar-rail .admin-app #sidebar .badge-count,
html.shell-is-desktop.shell-sidebar-rail .cashier-app #sidebar .badge-count,
html.shell-is-desktop.shell-sidebar-rail .admin-app #sidebar .sidebar-user-text,
html.shell-is-desktop.shell-sidebar-rail .cashier-app #sidebar .sidebar-user-text,
.admin-app #sidebar.sidebar-is-rail .sidebar-link .label,
.cashier-app #sidebar.sidebar-is-rail .sidebar-link .label,
.admin-app #sidebar.sidebar-is-rail .sidebar-section,
.cashier-app #sidebar.sidebar-is-rail .sidebar-section,
.admin-app #sidebar.sidebar-is-rail .badge-count,
.cashier-app #sidebar.sidebar-is-rail .badge-count,
.admin-app #sidebar.sidebar-is-rail .sidebar-user-text,
.cashier-app #sidebar.sidebar-is-rail .sidebar-user-text {
    display: none !important;
    width: 0 !important;
    max-width: 0 !important;
    height: 0 !important;
    overflow: hidden !important;
    opacity: 0 !important;
    visibility: hidden !important;
    padding: 0 !important;
    margin: 0 !important;
    flex: 0 0 0 !important;
    position: absolute !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
}

.admin-app #sidebar .sidebar-logo-full,
.cashier-app #sidebar .sidebar-logo-full {
    height: 3.5rem;
    width: auto;
    max-width: 100%;
    object-fit: contain;
    object-position: center;
    display: block;
    margin: 0 auto;
}

.admin-app #sidebar .sidebar-logo-bar,
.cashier-app #sidebar .sidebar-logo-bar {
    overflow: visible;
}

.admin-app #sidebar .sidebar-brand-link,
.cashier-app #sidebar .sidebar-brand-link {
    min-height: 3.5rem;
}

/* Menu replié : même logo, réduit mais lisible dans le rail 5rem */
html.shell-is-desktop.shell-sidebar-rail .admin-app #sidebar .sidebar-logo-full,
html.shell-is-desktop.shell-sidebar-rail .cashier-app #sidebar .sidebar-logo-full,
.admin-app #sidebar.sidebar-is-rail .sidebar-logo-full,
.cashier-app #sidebar.sidebar-is-rail .sidebar-logo-full {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    height: 2.75rem;
    width: auto;
    max-width: 4.25rem;
    object-fit: contain;
    object-position: center;
}
</style>
