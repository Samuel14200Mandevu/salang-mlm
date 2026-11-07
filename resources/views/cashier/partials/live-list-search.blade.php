{{--
  Recherche live (debounce) comme admin Membres.
  Props:
    - inputs: array of input element ids
    - replaceIds: array of DOM ids to swap from fetched HTML
--}}
@php
    $inputs = $inputs ?? ['searchInput', 'searchInputDesktop'];
    $replaceIds = $replaceIds ?? ['tableBody', 'paginationContainer', 'listSubtitle'];
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputIds = @json($inputs);
    const replaceIds = @json($replaceIds);
    const searchInputs = inputIds
        .map((id) => document.getElementById(id))
        .filter(Boolean);

    if (!searchInputs.length) return;

    let searchTimeout;

    function syncInputs(value, except) {
        searchInputs.forEach((el) => {
            if (el !== except) el.value = value;
        });
    }

    function fetchList(query) {
        const url = new URL(window.location.href);
        if (query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.set('page', '1');

        const tableBody = document.getElementById('tableBody');
        if (tableBody) {
            const cols = tableBody.closest('table')?.querySelectorAll('thead th')?.length || 8;
            tableBody.innerHTML = `
                <tr>
                    <td colspan="${cols}" class="text-center py-8 text-[var(--text-secondary)]">
                        <div class="flex items-center justify-center gap-3">
                            <svg class="animate-spin h-5 w-5 text-[var(--primary-navy)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Recherche en cours…</span>
                        </div>
                    </td>
                </tr>`;
        }

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((r) => r.text())
            .then((html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                replaceIds.forEach((id) => {
                    const current = document.getElementById(id);
                    const next = doc.getElementById(id);
                    if (current && next) {
                        current.innerHTML = next.innerHTML;
                    } else if (current && !next) {
                        current.innerHTML = '';
                    }
                });
                if (typeof window.initSalangPagination === 'function') {
                    window.initSalangPagination(document);
                }
                window.history.replaceState({}, '', url.toString());
            })
            .catch(() => {
                window.location.href = url.toString();
            });
    }

    searchInputs.forEach((input) => {
        input.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            syncInputs(this.value, this);
            searchTimeout = setTimeout(() => fetchList(query), 300);
        });
    });
});
</script>
