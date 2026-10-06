"use client";

import { useMemo, useState } from "react";
import {
  Area,
  AreaChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import { cn } from "@/lib/utils";

type Panel = "commissions" | "reseau" | "portefeuille";

const panels: { id: Panel; label: string }[] = [
  { id: "commissions", label: "Commissions" },
  { id: "reseau", label: "Réseau" },
  { id: "portefeuille", label: "Portefeuille" },
];

const chartData = [
  { m: "Jan", v: 1240 },
  { m: "Fév", v: 1580 },
  { m: "Mar", v: 1420 },
  { m: "Avr", v: 1890 },
  { m: "Mai", v: 2105 },
  { m: "Juin", v: 1980 },
];

function PanelIcon({ panel }: { panel: Panel }) {
  if (panel === "commissions") {
    return (
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden>
        <path d="M2 12V4h2v8H2zm5-6v6H5V6h2zm5 3v3h-2V9h2z" fill="currentColor" />
      </svg>
    );
  }
  if (panel === "reseau") {
    return (
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden>
        <circle cx="8" cy="3" r="1.5" fill="currentColor" />
        <circle cx="4" cy="11" r="1.5" fill="currentColor" />
        <circle cx="12" cy="11" r="1.5" fill="currentColor" />
        <path d="M8 4.5v3M8 7.5L4 9.5M8 7.5l4 2" stroke="currentColor" strokeWidth="1.2" />
      </svg>
    );
  }
  return (
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden>
      <rect x="2" y="4" width="12" height="9" rx="1.5" stroke="currentColor" strokeWidth="1.2" />
      <path d="M2 7h12" stroke="currentColor" strokeWidth="1.2" />
    </svg>
  );
}

export function DashboardMockup() {
  const [panel, setPanel] = useState<Panel>("commissions");

  const summary = useMemo(() => {
    if (panel === "reseau") {
      return { k: "Membres actifs (L/R)", v: "248 / 231", hint: "Volume binaire du mois" };
    }
    if (panel === "portefeuille") {
      return { k: "Solde disponible", v: "4 820 USD", hint: "Retrait soumis à validation" };
    }
    return { k: "Commissions (30 j)", v: "2 105 USD", hint: "Direct + indirect consolidés" };
  }, [panel]);

  return (
    <div
      className="overflow-hidden rounded-lg border border-border bg-[#FAFBFC] dark:border-border-dark dark:bg-[#1A2332]"
      role="region"
      aria-label="Aperçu interactif du tableau de bord Salang"
    >
      <div className="flex items-center justify-between gap-3 border-b border-border px-4 py-3 dark:border-border-dark">
        <p className="font-display text-sm font-semibold text-ink dark:text-ink-inverse">Console Salang</p>
        <span className="rounded-md bg-brand-accent/15 px-2 py-0.5 text-[0.6875rem] font-semibold uppercase tracking-wide text-brand-accent-muted">
          En attente · 2
        </span>
      </div>

      <div className="flex gap-1 border-b border-border px-3 py-2 dark:border-border-dark">
        {panels.map((p) => (
          <button
            key={p.id}
            type="button"
            onClick={() => setPanel(p.id)}
            className={cn(
              "inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-colors",
              panel === p.id
                ? "bg-brand-primary text-white"
                : "text-ink-muted hover:text-ink dark:text-ink-subtle dark:hover:text-ink-inverse"
            )}
          >
            <PanelIcon panel={p.id} />
            {p.label}
          </button>
        ))}
      </div>

      <div className="grid gap-4 p-4 sm:grid-cols-[1fr_1.4fr]">
        <div className="space-y-3">
          <div className="rounded-lg border border-border bg-page-light p-3 dark:border-border-dark dark:bg-page-dark">
            <p className="text-[0.6875rem] font-medium uppercase tracking-wide text-ink-subtle">{summary.k}</p>
            <p className="mt-1 font-display text-xl font-bold tabular-nums text-ink dark:text-ink-inverse">{summary.v}</p>
            <p className="mt-2 text-xs text-ink-muted">{summary.hint}</p>
          </div>
          <ul className="space-y-2 text-xs text-ink-muted dark:text-ink-subtle">
            <li className="flex justify-between border-b border-border/80 pb-2 dark:border-border-dark">
              <span>Dernier cycle</span>
              <span className="font-medium text-ink dark:text-ink-inverse">Clôturé · 04/06</span>
            </li>
            <li className="flex justify-between">
              <span>Grade actuel</span>
              <span className="font-medium text-ink dark:text-ink-inverse">Manager Senior</span>
            </li>
          </ul>
        </div>

        <div className="h-[180px] min-h-[160px] rounded-lg border border-border bg-page-light p-2 dark:border-border-dark dark:bg-page-dark">
          <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={chartData} margin={{ top: 8, right: 8, left: -16, bottom: 0 }}>
              <CartesianGrid stroke="#E5E7EB" strokeDasharray="3 3" vertical={false} />
              <XAxis dataKey="m" tick={{ fontSize: 10, fill: "#6B7280" }} axisLine={false} tickLine={false} />
              <YAxis hide domain={["auto", "auto"]} />
              <Tooltip
                contentStyle={{
                  fontSize: 12,
                  borderRadius: 8,
                  border: "1px solid #E5E7EB",
                  background: "#FAFBFC",
                }}
                formatter={(value: number) => [`${value} USD`, "Volume"]}
              />
              <Area
                type="monotone"
                dataKey="v"
                stroke="#1E5DAD"
                fill="#1E5DAD"
                fillOpacity={0.12}
                strokeWidth={2}
              />
            </AreaChart>
          </ResponsiveContainer>
        </div>
      </div>
    </div>
  );
}
