import { IncomeDonutChart } from "@/components/dashboard/IncomeDonutChart";
import { RevenueChart } from "@/components/dashboard/RevenueChart";
import { StatsCard } from "@/components/dashboard/StatsCard";
import { TopRankTable } from "@/components/dashboard/TopRankTable";
import { WorldMapWidget } from "@/components/dashboard/WorldMapWidget";
import { kpiStats } from "@/lib/mock-data";

export default function DashboardPage() {
  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-primary">Salang Group</p>
          <h1 className="mt-1 font-display text-2xl font-bold text-foreground">Admin Dashboard</h1>
          <p className="text-sm text-muted">Health Care International · network &amp; commissions (mock data)</p>
        </div>
        <div className="w-fit rounded-xl border border-border bg-surface px-3 py-2 text-xs text-muted">
          Period <span className="ml-2 font-medium text-foreground">Last 30 days</span>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6">
        {kpiStats.map((stat) => (
          <StatsCard key={stat.title} {...stat} />
        ))}
      </div>

      <div className="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <RevenueChart />
        <IncomeDonutChart />
      </div>

      <WorldMapWidget />
      <TopRankTable />
    </div>
  );
}
