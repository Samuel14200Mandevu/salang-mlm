@php
    $accent = $dashboardLevel['gradient'] ?? '#22c55e';
@endphp
<style>
    .dashboard-level-badge {
        background: linear-gradient(135deg, {{ $accent }}, {{ $accent }});
        color: white;
        padding: 0.25rem 1rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        display: inline-block;
    }
    .dashboard-welcome-card {
        background: linear-gradient(135deg, {{ $accent }}14, {{ $accent }}05);
        border: 1px solid {{ $accent }}33;
        border-radius: var(--radius-lg);
        padding: 1.25rem;
    }
    .dashboard-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem;
        transition: all var(--transition-bounce);
    }
    .dashboard-stat-card:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: var(--shadow-lg);
    }
    .dashboard-stat-card .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: {{ $accent }}1f;
        color: {{ $accent }};
    }
    .dashboard-quick-action {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem;
        text-align: center;
        transition: all var(--transition-bounce);
        text-decoration: none;
        display: block;
    }
    .dashboard-quick-action:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
        border-color: {{ $accent }};
    }
    .dashboard-quick-action .icon {
        width: 1.5rem;
        height: 1.5rem;
        margin: 0 auto 0.25rem;
        display: block;
        color: {{ $accent }};
    }
    .dashboard-quick-action .label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .dashboard-rank-progress-bar {
        width: 100%;
        height: 6px;
        background: var(--bg-secondary);
        border-radius: 9999px;
        overflow: hidden;
        margin-top: 0.5rem;
    }
    .dashboard-rank-progress-bar .fill {
        height: 100%;
        border-radius: 9999px;
        background: linear-gradient(135deg, {{ $accent }}, {{ $accent }});
        transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dashboard-activity-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    .dashboard-activity-item:last-child { border-bottom: none; }
    .dashboard-band-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1rem;
    }
    .dashboard-band-card .band-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .dashboard-max-banner {
        background: linear-gradient(135deg, {{ $accent }}, {{ $accent }}cc);
        color: white;
        border-radius: var(--radius-lg);
        padding: 1rem 1.25rem;
        text-align: center;
    }
    .dashboard-leader-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        border-radius: var(--radius-md);
    }
    .dashboard-leader-row.highlight {
        background: {{ $accent }}0d;
    }
    .dashboard-objective-bar {
        width: 100%;
        height: 6px;
        background: var(--bg-secondary);
        border-radius: 9999px;
        overflow: hidden;
        margin-top: 0.35rem;
    }
    .dashboard-objective-bar .fill {
        height: 100%;
        background: {{ $accent }};
        border-radius: 9999px;
    }
    @media (max-width: 640px) {
        .dashboard-stat-card { padding: 0.75rem; }
        .dashboard-welcome-card { padding: 0.875rem; }
        .dashboard-quick-action { padding: 0.75rem; }
    }
</style>
