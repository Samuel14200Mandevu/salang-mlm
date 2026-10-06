<script>
(function () {
    window.salangReadSidebarOpen = function () {
        var w = window.innerWidth;
        if (w < 768) {
            return false;
        }
        var stored = localStorage.getItem('sidebar_open');
        if (stored === 'true') {
            return true;
        }
        if (stored === 'false') {
            return false;
        }
        return w > 1024;
    };

    window.salangApplySidebarRailDom = function (open) {
        var sidebar = document.getElementById('sidebar');
        if (!sidebar) {
            return;
        }
        var desktop = window.innerWidth >= 768;
        sidebar.classList.toggle('sidebar-is-rail', desktop && !open);
    };

    window.salangSyncSidebarShell = function (open) {
        var root = document.documentElement;
        var desktop = window.innerWidth >= 768;
        root.classList.remove('shell-is-mobile', 'shell-is-desktop', 'shell-sidebar-expanded', 'shell-sidebar-rail');
        if (!desktop) {
            root.classList.add('shell-is-mobile');
            window.salangApplySidebarRailDom(false);
            return;
        }
        root.classList.add('shell-is-desktop');
        root.classList.add(open ? 'shell-sidebar-expanded' : 'shell-sidebar-rail');
        window.salangApplySidebarRailDom(open);
    };

    window.salangPersistSidebarOpen = function (open) {
        localStorage.setItem('sidebar_open', open ? 'true' : 'false');
        window.salangSyncSidebarShell(open);
    };

    window.salangSyncSidebarShell(window.salangReadSidebarOpen());

    document.addEventListener('DOMContentLoaded', function () {
        window.salangApplySidebarRailDom(window.salangReadSidebarOpen());
    });
})();
</script>
