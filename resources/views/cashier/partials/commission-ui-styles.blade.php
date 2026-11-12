{{-- Styles partagés avec « Toutes les commissions » (desktop + mobile) --}}
<style>
    [x-cloak] { display: none !important; }

    .commission-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .commission-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1rem;
        text-align: center;
    }
    .commission-stat-card .number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
    }
    .commission-stat-card .label {
        font-size: 0.7rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .commission-stat-card .icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md, 6px);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
    }
    .icon-green { background: rgba(34, 197, 94, 0.10); color: #16a34a; }
    .icon-blue { background: rgba(59, 130, 246, 0.10); color: #2563eb; }
    .icon-purple { background: rgba(139, 92, 246, 0.10); color: #e8940f; }
    .icon-orange { background: rgba(245, 158, 11, 0.10); color: #d97706; }

    .commission-table {
        width: 100%;
        font-size: 0.813rem;
        border-collapse: collapse;
    }
    .commission-table th {
        text-align: left;
        padding: 0.5rem 0.75rem;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-tertiary);
        border-bottom: 1px solid var(--border-color);
        font-weight: 600;
    }
    .commission-table td {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
        vertical-align: middle;
    }
    .commission-table tr:hover td { background: var(--bg-secondary); }
    .commission-table tbody tr.commission-row { cursor: pointer; }
    .commission-table thead th.col-amount,
    .commission-table tbody td.col-amount {
        text-align: left;
        font-weight: 650;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
        color: var(--text-primary);
    }
    .commission-member {
        font-size: 0.8125rem;
        font-weight: 650;
        color: var(--text-primary);
        letter-spacing: -0.01em;
    }
    .commission-table .badge {
        display: inline-block;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.6rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-success { background: rgba(34, 197, 94, 0.12); color: #16a34a; }
    .badge-warning { background: rgba(245, 158, 11, 0.12); color: #d97706; }
    .badge-danger { background: rgba(179, 42, 42, 0.12); color: #b32a2a; }
    .badge-info { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
    .badge-secondary { background: var(--bg-secondary); color: var(--text-secondary); }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 8px);
        padding: 1.25rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        padding: 0.5rem 1.25rem;
        border-radius: var(--radius-md, 6px);
        font-weight: 600;
        font-size: 0.875rem;
        transition: background 0.2s ease;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--primary-hover, #134178); color: #fff; }
    .btn-outline {
        background: transparent;
        color: var(--text-primary);
        border: 1.5px solid var(--border-color);
    }
    .btn-outline:hover {
        background: var(--bg-secondary);
        border-color: var(--primary);
        color: var(--primary);
    }
    .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.75rem; }

    .detail-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.55rem 0;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.875rem;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-row .label { color: var(--text-secondary); }
    .detail-row .value {
        font-weight: 650;
        color: var(--text-primary);
        text-align: right;
    }

    .table-wrap { overflow-x: auto; }

    @media (max-width: 640px) {
        .commission-stats { grid-template-columns: 1fr 1fr; }
        .commission-table { font-size: 0.7rem; }
        .commission-table th, .commission-table td { padding: 0.3rem 0.4rem; }
        .card { padding: 0.875rem; }
        .header-actions { flex-wrap: wrap; width: 100%; }
        .header-actions .btn { flex: 1; justify-content: center; }
        .btn { padding: 0.35rem 0.75rem; font-size: 0.75rem; }
        .btn-sm { padding: 0.2rem 0.5rem; font-size: 0.65rem; }
    }
</style>
