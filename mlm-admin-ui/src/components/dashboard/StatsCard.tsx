"use client";

import { Area, AreaChart, ResponsiveContainer } from "recharts";
import {
  DollarSign,
  GitBranch,
  TrendingUp,
  Users,
  Wallet,
  type LucideIcon,
} from "lucide-react";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { formatCurrency, formatNumber } from "@/lib/utils";
import type { KpiIcon } from "@/lib/mock-data";

const icons: Record<KpiIcon, LucideIcon> = {
  revenue: DollarSign,
  profit: TrendingUp,
  members: Users,
  wallet: Wallet,
  binary: GitBranch,
  payout: Wallet,
};

type StatsCardProps = {
  title: string;
  value: number;
  change: number;
  sparkline: number[];
  isCount?: boolean;
  accent?: string;
  icon?: KpiIcon;
};

export function StatsCard({
  title,
  value,
  change,
  sparkline,
  isCount,
  accent = "#7C3AED",
  icon = "revenue",
}: StatsCardProps) {
  const positive = change >= 0;
  const chartData = sparkline.map((point, index) => ({ index, point }));
  const Icon = icons[icon];

  return (
    <Card className="relative min-h-[148px] overflow-hidden p-4 transition-colors hover:border-primary/40 hover:shadow-glow-sm">
      <div className="relative z-10 flex items-start justify-between gap-3">
        <p className="text-[11px] font-medium uppercase tracking-[0.14em] text-muted">{title}</p>
        <span
          className="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl"
          style={{ backgroundColor: `${accent}22`, color: accent }}
        >
          <Icon className="h-4 w-4" />
        </span>
      </div>
      <p className="relative z-10 mt-3 font-display text-xl font-bold tracking-tight text-foreground tabular-nums sm:text-2xl">
        {isCount ? formatNumber(value) : formatCurrency(value)}
      </p>
      <Badge variant={positive ? "success" : "danger"} className="relative z-10 mt-3">
        {positive ? "+" : ""}
        {change}% vs last 30 days
      </Badge>
      <div className="pointer-events-none absolute bottom-1 right-1 h-14 w-28 opacity-70">
        <ResponsiveContainer width="100%" height="100%">
          <AreaChart data={chartData} margin={{ top: 8, right: 0, left: 0, bottom: 0 }}>
            <Area
              type="monotone"
              dataKey="point"
              stroke={accent}
              fill={accent}
              fillOpacity={0.16}
              strokeWidth={2}
              dot={false}
              isAnimationActive={false}
            />
          </AreaChart>
        </ResponsiveContainer>
      </div>
    </Card>
  );
}
