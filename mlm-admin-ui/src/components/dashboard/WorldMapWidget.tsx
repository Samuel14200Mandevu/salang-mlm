"use client";

import dynamic from "next/dynamic";
import { Card, CardHeader, CardTitle } from "@/components/ui/card";
import { geoStats } from "@/lib/mock-data";
import { formatNumber } from "@/lib/utils";

const MapContent = dynamic(() => import("./WorldMapInner"), {
  ssr: false,
  loading: () => (
    <div className="flex h-[260px] items-center justify-center text-sm text-muted">Loading map…</div>
  ),
});

export function WorldMapWidget() {
  const max = Math.max(...geoStats.map((item) => item.value));

  return (
    <Card>
      <CardHeader>
        <CardTitle>Global Network</CardTitle>
        <p className="text-sm text-muted">Active members by region</p>
      </CardHeader>
      <div className="grid items-center gap-4 lg:grid-cols-[minmax(0,1fr)_220px]">
        <MapContent data={geoStats} />
        <ul className="space-y-3">
          {geoStats.map((item) => (
            <li key={item.country}>
              <div className="mb-1 flex items-center justify-between text-xs">
                <span className="text-foreground">{item.label}</span>
                <span className="tabular-nums text-muted">{formatNumber(item.value)}</span>
              </div>
              <div className="h-1.5 overflow-hidden rounded-full bg-canvas">
                <div
                  className="h-full rounded-full bg-primary"
                  style={{ width: `${(item.value / max) * 100}%` }}
                />
              </div>
            </li>
          ))}
        </ul>
      </div>
    </Card>
  );
}
