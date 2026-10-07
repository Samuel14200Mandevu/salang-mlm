{{-- Compteurs consultations / rapports (admin = à valider, caissier = réponses admin) --}}
<script>
(function () {
    'use strict';

    var config = @json($workflowPollConfig ?? []);
    if (!config.url) return;

    function setBadge(badgeId, dotId, count) {
        var badge = badgeId ? document.getElementById(badgeId) : null;
        var dot = dotId ? document.getElementById(dotId) : null;
        var n = Number(count) || 0;

        if (badge) {
            if (n > 0) {
                badge.textContent = n > 99 ? '99+' : String(n);
                badge.style.display = 'flex';
                badge.classList.remove('hidden');
            } else {
                badge.style.display = 'none';
                badge.classList.add('hidden');
            }
        }
        if (dot) {
            dot.style.display = n > 0 ? 'block' : 'none';
        }
        return n;
    }

    function setDropdownCount(elId, count) {
        var el = elId ? document.getElementById(elId) : null;
        if (!el) return;
        var n = Number(count) || 0;
        if (n > 0) {
            el.textContent = n > 99 ? '99+' : String(n);
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    }

    function updateDropdown(data) {
        if (!config.dropdown) return;

        var c = Number(data.consultations) || 0;
        var r = Number(data.reports) || 0;
        var total = Number(data.total) || 0;

        setDropdownCount(config.dropdown.consultationCount, c);
        setDropdownCount(config.dropdown.reportCount, r);

        var mirror = config.dropdown.totalMirror ? document.getElementById(config.dropdown.totalMirror) : null;
        if (mirror) {
            if (total > 0) {
                mirror.textContent = total > 99 ? '99+' : String(total);
                mirror.classList.remove('hidden');
            } else {
                mirror.classList.add('hidden');
            }
        }

        var empty = config.dropdown.empty ? document.getElementById(config.dropdown.empty) : null;
        var items = config.dropdown.items ? document.getElementById(config.dropdown.items) : null;
        var urgentWrap = config.dropdown.urgentWrap ? document.getElementById(config.dropdown.urgentWrap) : null;
        var urgentLink = config.dropdown.urgentLink ? document.getElementById(config.dropdown.urgentLink) : null;
        var consultationRow = config.dropdown.consultationRow ? document.getElementById(config.dropdown.consultationRow) : null;
        var reportRow = config.dropdown.reportRow ? document.getElementById(config.dropdown.reportRow) : null;

        if (empty && items) {
            if (total === 0) {
                empty.classList.remove('hidden');
                items.classList.add('hidden');
            } else {
                empty.classList.add('hidden');
                items.classList.remove('hidden');
            }
        }

        if (urgentWrap && urgentLink && config.links) {
            if (total > 0) {
                urgentWrap.classList.remove('hidden');
                var urgentUrl = config.links.consultations;
                if (r > c && r > 0) {
                    urgentUrl = config.links.reports;
                } else if (c > 0) {
                    urgentUrl = config.links.consultations;
                } else if (r > 0) {
                    urgentUrl = config.links.reports;
                }
                urgentLink.href = urgentUrl;
            } else {
                urgentWrap.classList.add('hidden');
            }
        }

        if (consultationRow) {
            consultationRow.classList.toggle('workflow-notify-row-priority', c > 0 && c >= r);
        }
        if (reportRow) {
            reportRow.classList.toggle('workflow-notify-row-priority', r > 0 && r > c);
        }
    }

    function refresh() {
        if (localStorage.getItem('admin_workflow_poll_enabled') === '0') {
            return;
        }

        fetch(config.url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Erreur réseau');
                return response.json();
            })
            .then(function (data) {
                if (config.consultations) {
                    setBadge(config.consultations.badge, config.consultations.dot, data.consultations);
                }
                if (config.reports) {
                    setBadge(config.reports.badge, config.reports.dot, data.reports);
                }
                if (config.header) {
                    setBadge(config.header.badge, config.header.dot, data.total);
                }
                if (config.mobileNav) {
                    setBadge(config.mobileNav.badge, config.mobileNav.dot || null, data.total);
                }
                updateDropdown(data);
            })
            .catch(function (err) {
                console.error('Compteur workflow:', err);
            });
    }

    window.salangWorkflowPollRefresh = refresh;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', refresh);
    } else {
        refresh();
    }

    setInterval(refresh, 30000);
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) refresh();
    });
})();
</script>
