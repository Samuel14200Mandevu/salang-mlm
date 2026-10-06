"use client";

import { useState } from "react";
import { ComposableMap, Geographies, Geography } from "react-simple-maps";

const geoUrl = "https://cdn.jsdelivr.net/npm/world-atlas@2/countries-110m.json";

type Props = {
  data: { country: string; label: string; value: number }[];
};

export default function WorldMapInner({ data }: Props) {
  const [hovered, setHovered] = useState<string | null>(null);
  const byName = new Map(data.map((item) => [item.country, item]));
  const max = Math.max(...data.map((item) => item.value));
  const active = hovered ? byName.get(hovered) : undefined;

  return (
    <div className="relative h-[260px] w-full">
      <ComposableMap
        projectionConfig={{ scale: 145 }}
        width={800}
        height={260}
        style={{ width: "100%", height: "100%" }}
      >
        <Geographies geography={geoUrl}>
          {({ geographies }) =>
            geographies.map((geo) => {
              const name = String(geo.properties?.name ?? "");
              const item = byName.get(name);
              const strength = item ? 0.45 + (item.value / max) * 0.55 : 0;
              const fill = item ? `rgba(124, 58, 237, ${strength})` : "#1F2937";
              return (
                <Geography
                  key={geo.rsmKey}
                  geography={geo}
                  fill={hovered === name ? "#06B6D4" : fill}
                  stroke="#0B0F19"
                  strokeWidth={0.4}
                  onMouseEnter={() => setHovered(name)}
                  onMouseLeave={() => setHovered(null)}
                  style={{
                    default: { outline: "none" },
                    hover: { outline: "none", cursor: item ? "pointer" : "default" },
                    pressed: { outline: "none" },
                  }}
                />
              );
            })
          }
        </Geographies>
      </ComposableMap>
      {active && (
        <div className="pointer-events-none absolute bottom-2 left-2 rounded-xl border border-border bg-surface/95 px-3 py-2 text-xs shadow-card">
          <p className="font-medium text-foreground">{active.label}</p>
          <p className="text-muted">{active.value.toLocaleString()} members</p>
        </div>
      )}
    </div>
  );
}
