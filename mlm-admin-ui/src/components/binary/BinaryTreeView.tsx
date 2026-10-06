"use client";

import { useState } from "react";
import type { BinaryNode } from "@/lib/mock-data";
import { Avatar } from "@/components/ui/avatar";
import { cn, formatNumber } from "@/lib/utils";

function findNode(node: BinaryNode, id: string): BinaryNode | null {
  if (node.id === id) return node;
  for (const child of node.children) {
    const found = findNode(child, id);
    if (found) return found;
  }
  return null;
}

function TreeNode({
  node,
  x,
  y,
  width,
  selectedId,
  onSelect,
}: {
  node: BinaryNode;
  x: number;
  y: number;
  width: number;
  selectedId: string;
  onSelect: (id: string) => void;
}) {
  const childY = y + 128;
  const leftX = x - width / 4;
  const rightX = x + width / 4;
  const selected = selectedId === node.id;

  return (
    <g>
      {node.children[0] && (
        <>
          <path
            d={`M ${x} ${y + 34} C ${x} ${(y + childY) / 2}, ${leftX} ${(y + childY) / 2}, ${leftX} ${childY - 38}`}
            fill="none"
            stroke="#7C3AED"
            strokeOpacity={0.55}
            strokeWidth={1.5}
          />
          <TreeNode
            node={node.children[0]}
            x={leftX}
            y={childY}
            width={width / 2}
            selectedId={selectedId}
            onSelect={onSelect}
          />
        </>
      )}
      {node.children[1] && (
        <>
          <path
            d={`M ${x} ${y + 34} C ${x} ${(y + childY) / 2}, ${rightX} ${(y + childY) / 2}, ${rightX} ${childY - 38}`}
            fill="none"
            stroke="#06B6D4"
            strokeOpacity={0.55}
            strokeWidth={1.5}
          />
          <TreeNode
            node={node.children[1]}
            x={rightX}
            y={childY}
            width={width / 2}
            selectedId={selectedId}
            onSelect={onSelect}
          />
        </>
      )}
      <foreignObject x={x - 78} y={y - 36} width={156} height={76}>
        <button
          type="button"
          aria-pressed={selected}
          onClick={() => onSelect(node.id)}
          className={cn(
            "flex h-[72px] w-[152px] items-center gap-2 rounded-xl border bg-surface px-2 text-left shadow-card transition",
            selected ? "border-primary shadow-glow-sm" : "border-border hover:border-primary/40"
          )}
        >
          <Avatar name={node.name} className="h-8 w-8 rounded-lg text-[10px]" />
          <span className="min-w-0">
            <span className="block truncate text-[11px] font-semibold text-foreground">{node.name}</span>
            <span className="mt-0.5 block text-[10px] text-primary">L {formatNumber(node.leftVolume)}</span>
            <span className="block text-[10px] text-cyan">R {formatNumber(node.rightVolume)}</span>
          </span>
        </button>
      </foreignObject>
    </g>
  );
}

export function BinaryTreeView({ root }: { root: BinaryNode }) {
  const [selectedId, setSelectedId] = useState(root.id);
  const selected = findNode(root, selectedId) ?? root;
  const weaker = Math.min(selected.leftVolume, selected.rightVolume);
  const carry = Math.abs(selected.leftVolume - selected.rightVolume);

  return (
    <div className="space-y-4">
      <div className="overflow-x-auto rounded-2xl border border-border bg-canvas/60 p-4">
        <svg viewBox="0 0 960 420" className="min-w-[760px] w-full" role="tree" aria-label="Binary genealogy">
          <TreeNode node={root} x={480} y={56} width={860} selectedId={selectedId} onSelect={setSelectedId} />
        </svg>
      </div>

      <div className="grid gap-3 sm:grid-cols-4">
        <div className="rounded-xl border border-border bg-canvas p-3 sm:col-span-1">
          <p className="text-[10px] uppercase tracking-wider text-muted">Selected</p>
          <p className="mt-1 truncate text-sm font-semibold text-foreground">{selected.name}</p>
        </div>
        <div className="rounded-xl border border-border bg-canvas p-3">
          <p className="text-[10px] uppercase tracking-wider text-muted">Left volume</p>
          <p className="mt-1 font-display text-lg font-bold text-primary">{formatNumber(selected.leftVolume)}</p>
        </div>
        <div className="rounded-xl border border-border bg-canvas p-3">
          <p className="text-[10px] uppercase tracking-wider text-muted">Right volume</p>
          <p className="mt-1 font-display text-lg font-bold text-cyan">{formatNumber(selected.rightVolume)}</p>
        </div>
        <div className="rounded-xl border border-border bg-canvas p-3">
          <p className="text-[10px] uppercase tracking-wider text-muted">Pairable / carry</p>
          <p className="mt-1 text-sm font-semibold text-foreground">
            {formatNumber(weaker)} <span className="text-muted">/</span> {formatNumber(carry)}
          </p>
        </div>
      </div>
    </div>
  );
}
