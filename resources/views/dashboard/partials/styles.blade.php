<style>
    /* Dashboard membre — sobre, palette Salang (primary + neutres) */

    .dashboard-level-badge {
        background: var(--color-primary-600);
        color: #fff;
        padding: 0.2rem 0.65rem;
        border-radius: 8px;
        font-size: 0.6875rem;
        font-weight: 600;
        display: inline-block;
        letter-spacing: 0.02em;
    }

    .dashboard-welcome-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 1rem 1.125rem;
    }

    .dashboard-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 0.875rem 1rem;
    }

    .dashboard-stat-card .stat-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: var(--bg-secondary);
        color: var(--color-primary-600);
    }

    .dashboard-stat-card .stat-value {
        color: var(--text-primary);
        font-weight: 700;
    }

    .dashboard-stat-card .stat-value--accent {
        color: var(--color-primary-600);
    }

    .dashboard-quick-action {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 0.875rem 0.75rem;
        text-align: center;
        text-decoration: none;
        display: block;
    }

    .dashboard-quick-action .icon {
        width: 1.375rem;
        height: 1.375rem;
        margin: 0 auto 0.35rem;
        display: block;
        color: var(--color-primary-600);
    }

    .dashboard-quick-action .label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .dashboard-rank-progress-bar,
    .dashboard-objective-bar {
        width: 100%;
        height: 6px;
        background: var(--bg-secondary);
        border-radius: 6px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .dashboard-rank-progress-bar .fill,
    .dashboard-objective-bar .fill {
        height: 100%;
        border-radius: 6px;
        background: var(--color-primary-600);
    }

    .dashboard-activity-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .dashboard-activity-item:last-child {
        border-bottom: none;
    }

    /* Bandes commissions : liste 60/40 desktop, pas de liserés colorés */
    .dashboard-bands-layout {
        display: grid;
        gap: 0.75rem;
    }

    @media (min-width: 768px) {
        .dashboard-bands-layout {
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
            align-items: start;
        }
    }

    .dashboard-band-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 0.875rem 1rem;
    }

    .dashboard-band-card--highlight {
        padding: 1rem 1.125rem;
    }

    .dashboard-band-card__label {
        font-size: 0.625rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-secondary);
        line-height: 1.35;
    }

    @media (min-width: 640px) {
        .dashboard-band-card__label {
            font-size: 0.6875rem;
        }
    }

    .dashboard-band-card__meta {
        font-size: 0.625rem;
        color: var(--text-tertiary);
        margin-top: 0.25rem;
    }

    .dashboard-band-card .band-value {
        font-size: 1.25rem;
        font-weight: 700;
        margin-top: 0.25rem;
        line-height: 1.2;
        color: var(--color-primary-600);
        font-variant-numeric: tabular-nums;
    }

    .dashboard-band-card .band-value--neutral {
        color: var(--text-primary);
    }

    .dashboard-bands-stack {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .dashboard-max-banner {
        background: var(--color-primary-600);
        color: #fff;
        border-radius: 10px;
        padding: 1rem 1.125rem;
        text-align: left;
    }

    .dashboard-leader-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        border-radius: 8px;
    }

    .dashboard-leader-row.highlight {
        background: var(--bg-secondary);
    }

    @media (max-width: 640px) {
        .dashboard-stat-card,
        .dashboard-welcome-card {
            padding: 0.875rem;
        }
    }
</style>
