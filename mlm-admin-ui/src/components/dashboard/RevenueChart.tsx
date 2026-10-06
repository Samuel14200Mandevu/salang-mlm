"use client";

import { useState } from "react";
import {
  Area,
  AreaChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import { Card, CardHeader, CardTitle } from "@/components/ui/card";
import { revenueOverview } from "@/lib/mock-data";
import { cn, formatCurrency } from "@/lib/utils";

const ranges = {
  "3M": 3,
  "6M": 6,
  "12M": 12,
} as const;

type RangeKey = keyof typeof ranges;

type TooltipPayload = {
  name?: string;
  value?: number;
  color?: string;
};

function CustomTooltip({
  active,
  payload,
  label,
}: {
  active?: boolean;
  payload?: TooltipPayload[];
  label?: string;
}) {
  if (!active || !payload?.length) return null;
  return (
    <div className="rounded-xl border border-border bg-surface px-3 py-2 shadow-card">
      <p className="mb-1 text-xs text-muted">{label}</p>
      {payload.map((entry) => (
        <p key={entry.name} className="text-sm font-medium" style={{ color: entry.color }}>
          {entry.name}: {formatCurrency(Number(entry.value ?? 0))}
        </p>
      ))}
    </div>
  );
}

export function RevenueChart() {
  const [range, setRange] = useState<RangeKey>("12M");
  const data = revenueOverview.slice(-ranges[range]);

  return (
    <Card className="h-full min-h-[380px] lg:col-span-2">
      <CardHeader className="gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <CardTitle>Revenue Overview</CardTitle>
          <p className="text-sm text-muted">Total revenue vs net profit</p>
        </div>
        <div className="flex rounded-xl bg-canvas p-1" role="group" aria-label="Chart range">
          {(Object.keys(ranges) as RangeKey[]).map((key) => (
            <button
              key={key}
              type="button"
              aria-pressed={range === key}
              onClick={() => setRange(key)}
              className={cn(
                "rounded-lg px-3 py-1 text-xs font-medium text-muted transition",
                range === key && "bg-primary text-white shadow-glow-sm"
              )}
            >
              {key}
            </button>
          ))}
        </div>
      </CardHeader>
      <div className="mb-3 flex gap-4 text-xs text-muted">
        <span className="inline-flex items-center gap-2">
          <span className="h-2 w-2 rounded-full bg-primary shadow-glow-sm" /> Total Revenue
        </span>
        <span className="inline-flex items-center gap-2">
          <span className="h-2 w-2 rounded-full bg-cyan" /> Net Profit
        </span>
      </div>
      <div className="h-[260px] w-full">
        <ResponsiveContainer width="100%" height="100%">
          <AreaChart data={data} margin={{ top: 8, right: 8, left: 0, bottom: 0 }}>
            <defs>
              <linearGradient id="revenueFill" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stopColor="#7C3AED" stopOpacity={0.35} />
                <stop offset="100%" stopColor="#7C3AED" stopOpacity={0} />
              </linearGradient>
              <linearGradient id="profitFill" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stopColor="#06B6D4" stopOpacity={0.28} />
                <stop offset="100%" stopColor="#06B6D4" stopOpacity={0} />
              </linearGradient>
            </defs>
            <CartesianGrid stroke="#1F2937" strokeDasharray="4 4" vertical={false} />
            <XAxis dataKey="date" stroke="#9CA3AF" tick={{ fontSize: 12, fill: "#9CA3AF" }} axisLine={false} tickLine={false} />
            <YAxis
              stroke="#9CA3AF"
              tick={{ fontSize: 12, fill: "#9CA3AF" }}
              axisLine={false}
              tickLine={false}
              width={48}
              tickFormatter={(value: number) => `$${Math.round(value / 1000)}k`}
            />
            <Tooltip content={<CustomTooltip />} cursor={{ stroke: "#1F2937" }} />
            <Area
              type="monotone"
              dataKey="revenue"
              name="Total Revenue"
              stroke="#7C3AED"
              fill="url(#revenueFill)"
              strokeWidth={2.5}
              dot={false}
              activeDot={{ r: 5, fill: "#7C3AED", stroke: "#0B0F19", strokeWidth: 2 }}
            />
            <Area
              type="monotone"
              dataKey="profit"
              name="Net Profit"
              stroke="#06B6D4"
              fill="url(#profitFill)"
              strokeWidth={2.5}
              dot={false}
              activeDot={{ r: 5, fill: "#06B6D4", stroke: "#0B0F19", strokeWidth: 2 }}
            />
          </AreaChart>
        </ResponsiveContainer>
      </div>
    </Card>
  );
}
