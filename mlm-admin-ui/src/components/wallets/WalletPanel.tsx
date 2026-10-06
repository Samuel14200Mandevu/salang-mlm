"use client";

import { useMemo, useState } from "react";
import { StatsCard } from "@/components/dashboard/StatsCard";
import { Badge } from "@/components/ui/badge";
import { Card, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { walletKpis, walletTransactions, type WalletTx } from "@/lib/mock-data";
import { cn, formatCurrency } from "@/lib/utils";

const filters = ["All", "Commission", "Withdrawal", "Bonus", "Fee"] as const;

export function WalletPanel() {
  const [filter, setFilter] = useState<(typeof filters)[number]>("All");

  const rows = useMemo(
    () => (filter === "All" ? walletTransactions : walletTransactions.filter((tx) => tx.type === filter)),
    [filter]
  );

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {walletKpis.map((stat) => (
          <StatsCard key={stat.title} {...stat} />
        ))}
      </div>

      <Card className="p-0">
        <CardHeader className="gap-3 px-5 pt-5 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <CardTitle>Ledger</CardTitle>
            <p className="text-sm text-muted">Recent wallet movements</p>
          </div>
          <div className="flex flex-wrap gap-1 rounded-xl bg-canvas p-1">
            {filters.map((item) => (
              <button
                key={item}
                type="button"
                aria-pressed={filter === item}
                onClick={() => setFilter(item)}
                className={cn(
                  "rounded-lg px-3 py-1 text-xs font-medium text-muted",
                  filter === item && "bg-primary text-white shadow-glow-sm"
                )}
              >
                {item}
              </button>
            ))}
          </div>
        </CardHeader>
        <Table>
          <TableHeader>
            <TableRow className="hover:bg-transparent">
              <TableHead>Reference</TableHead>
              <TableHead>Member</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Date</TableHead>
              <TableHead>Amount</TableHead>
              <TableHead>Status</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {rows.map((tx) => (
              <LedgerRow key={tx.id} tx={tx} />
            ))}
          </TableBody>
        </Table>
      </Card>
    </div>
  );
}

function LedgerRow({ tx }: { tx: WalletTx }) {
  const statusVariant = tx.status === "Completed" ? "success" : tx.status === "Failed" ? "danger" : "gold";
  return (
    <TableRow>
      <TableCell className="font-mono text-xs">{tx.id}</TableCell>
      <TableCell>{tx.member}</TableCell>
      <TableCell>{tx.type}</TableCell>
      <TableCell className="tabular-nums">{tx.date}</TableCell>
      <TableCell className={tx.amount >= 0 ? "text-success" : "text-danger"}>
        {formatCurrency(tx.amount)}
      </TableCell>
      <TableCell>
        <Badge variant={statusVariant}>{tx.status}</Badge>
      </TableCell>
    </TableRow>
  );
}
