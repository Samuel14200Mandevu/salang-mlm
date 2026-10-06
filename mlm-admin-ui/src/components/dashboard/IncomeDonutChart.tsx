"use client";

import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from "recharts";
import { Card, CardHeader, CardTitle } from "@/components/ui/card";
import { incomeBreakdown, incomeTotal } from "@/lib/mock-data";
import { formatCompactCurrency, formatCurrency } from "@/lib/utils";

export function IncomeDonutChart() {
  return (
    <Card className="h-full min-h-[380px]">
      <CardHeader>
        <CardTitle>Income Source Breakdown</CardTitle>
        <p className="text-sm text-muted">Commission mix · {formatCurrency(incomeTotal)}</p>
      </CardHeader>
      <div className="flex flex-col items-center gap-5 sm:flex-row sm:items-center xl:flex-col">
        <div className="relative h-[210px] w-[210px] shrink-0">
          <ResponsiveContainer width="100%" height="100%">
            <PieChart margin={{ top: 0, right: 0, bottom: 0, left: 0 }}>
              <Pie
                data={incomeBreakdown}
                dataKey="value"
                nameKey="name"
                innerRadius={68}
                outerRadius={92}
                paddingAngle={3}
                stroke="none"
              >
                {incomeBreakdown.map((entry) => (
                  <Cell key={entry.name} fill={entry.color} />
                ))}
              </Pie>
              <Tooltip
                contentStyle={{
                  background: "#151B2B",
                  border: "1px solid #1F2937",
                  borderRadius: 12,
                  color: "#F3F4F6",
                }}
                formatter={(value) => [`${value}%`, "Share"]}
              />
            </PieChart>
          </ResponsiveContainer>
          <div className="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
            <p className="text-[10px] uppercase tracking-wider text-muted">Total</p>
            <p className="font-display text-lg font-bold text-foreground">{formatCompactCurrency(incomeTotal)}</p>
          </div>
        </div>
        <ul className="w-full flex-1 space-y-3 text-sm">
          {incomeBreakdown.map((item) => (
            <li key={item.name}>
              <div className="mb-1 flex items-center justify-between gap-2">
                <span className="flex items-center gap-2 text-muted">
                  <span className="h-2.5 w-2.5 rounded-full" style={{ background: item.color }} />
                  {item.name}
                </span>
                <span className="font-medium tabular-nums text-foreground">{item.value}%</span>
              </div>
              <div className="h-1.5 overflow-hidden rounded-full bg-canvas">
                <div className="h-full rounded-full" style={{ width: `${item.value}%`, background: item.color }} />
              </div>
            </li>
          ))}
        </ul>
      </div>
    </Card>
  );
}
