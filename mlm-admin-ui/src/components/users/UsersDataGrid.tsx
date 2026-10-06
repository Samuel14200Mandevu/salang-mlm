"use client";

import { useEffect, useMemo, useState } from "react";
import { Download, Search, Trash2, Upload, X } from "lucide-react";
import { mockUsers, type MockUser } from "@/lib/mock-data";
import { Avatar } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Select } from "@/components/ui/select";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { cn, formatCurrency } from "@/lib/utils";

const PAGE_SIZE = 8;
const RANKS = [
  "Distributeur",
  "Qualification",
  "Directeur",
  "Manager Senior",
  "Saphire Manager",
  "Diamant Bleu",
];
const STATUSES = ["Active", "Inactive", "Suspended"];

function rankVariant(rank: string) {
  if (rank === "Diamant Bleu") return "default" as const;
  if (rank === "Saphire Manager") return "cyan" as const;
  if (rank === "Manager Senior") return "gold" as const;
  if (rank === "Directeur") return "success" as const;
  return "muted" as const;
}

export function UsersDataGrid() {
  const [users, setUsers] = useState(mockUsers);
  const [search, setSearch] = useState("");
  const [rank, setRank] = useState("all");
  const [status, setStatus] = useState("all");
  const [page, setPage] = useState(1);
  const [selected, setSelected] = useState<Set<string>>(new Set());
  const [active, setActive] = useState<MockUser | null>(null);
  const [notice, setNotice] = useState("");

  const filtered = useMemo(() => {
    const query = search.toLowerCase();
    return users.filter((user) => {
      const matchSearch =
        !query ||
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        user.id.toLowerCase().includes(query);
      const matchRank = rank === "all" || user.rank === rank;
      const matchStatus = status === "all" || user.status === status;
      return matchSearch && matchRank && matchStatus;
    });
  }, [users, search, rank, status]);

  const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  const pageRows = filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);

  useEffect(() => {
    if (page > totalPages) setPage(totalPages);
  }, [page, totalPages]);

  function toggleAll(checked: boolean) {
    setSelected(checked ? new Set(pageRows.map((row) => row.id)) : new Set());
  }

  function toggleOne(id: string) {
    setSelected((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  }

  function removeSelected() {
    setUsers((prev) => prev.filter((user) => !selected.has(user.id)));
    setNotice(`${selected.size} member${selected.size > 1 ? "s" : ""} removed from this view`);
    setSelected(new Set());
  }

  function exportCsv() {
    const header = ["id", "name", "email", "rank", "joined", "package", "wallet", "status", "kyc"];
    const lines = filtered.map((user) =>
      [user.id, user.name, user.email, user.rank, user.joined, user.package, user.wallet, user.status, user.kyc].join(",")
    );
    const blob = new Blob([[header.join(","), ...lines].join("\n")], { type: "text/csv" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = "mlm-members.csv";
    link.click();
    URL.revokeObjectURL(url);
    setNotice(`Exported ${filtered.length} members`);
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-col gap-3 rounded-2xl border border-border bg-surface p-3 lg:flex-row lg:items-center lg:justify-between">
        <div className="flex flex-1 flex-wrap gap-2">
          <div className="relative min-w-[200px] flex-1">
            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
            <Input
              className="pl-9"
              placeholder="Search name, email, or ID"
              aria-label="Search users"
              value={search}
              onChange={(event) => {
                setSearch(event.target.value);
                setPage(1);
              }}
            />
          </div>
          <Select
            aria-label="Filter by rank"
            value={rank}
            onChange={(event) => {
              setRank(event.target.value);
              setPage(1);
            }}
          >
            <option value="all">All ranks</option>
            {RANKS.map((item) => (
              <option key={item} value={item}>
                {item}
              </option>
            ))}
          </Select>
          <Select
            aria-label="Filter by status"
            value={status}
            onChange={(event) => {
              setStatus(event.target.value);
              setPage(1);
            }}
          >
            <option value="all">All status</option>
            {STATUSES.map((item) => (
              <option key={item} value={item}>
                {item}
              </option>
            ))}
          </Select>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button variant="secondary" size="sm" onClick={() => setNotice("Import is preview-only in this demo")}>
            <Upload className="h-4 w-4" /> Import
          </Button>
          <Button variant="secondary" size="sm" onClick={exportCsv}>
            <Download className="h-4 w-4" /> Export
          </Button>
          <Button variant="danger" size="sm" disabled={selected.size === 0} onClick={removeSelected}>
            <Trash2 className="h-4 w-4" /> Delete{selected.size > 0 ? ` (${selected.size})` : ""}
          </Button>
        </div>
      </div>

      {notice && (
        <p className="rounded-xl border border-primary/30 bg-primary/10 px-3 py-2 text-sm text-foreground">{notice}</p>
      )}

      <div className="rounded-2xl border border-border bg-surface">
        <Table>
          <TableHeader>
            <TableRow className="hover:bg-transparent">
              <TableHead className="w-10">
                <input
                  type="checkbox"
                  className="h-4 w-4 accent-primary"
                  aria-label="Select all on this page"
                  checked={pageRows.length > 0 && pageRows.every((row) => selected.has(row.id))}
                  onChange={(event) => toggleAll(event.target.checked)}
                />
              </TableHead>
              <TableHead>User</TableHead>
              <TableHead>User ID</TableHead>
              <TableHead>Rank</TableHead>
              <TableHead>Joined</TableHead>
              <TableHead>Package</TableHead>
              <TableHead>Wallet</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>KYC</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {pageRows.length === 0 ? (
              <TableRow>
                <TableCell colSpan={10} className="py-12 text-center text-muted">
                  No members match these filters.
                </TableCell>
              </TableRow>
            ) : (
              pageRows.map((user) => (
                <UserRow
                  key={user.id}
                  user={user}
                  checked={selected.has(user.id)}
                  onToggle={() => toggleOne(user.id)}
                  onView={() => setActive(user)}
                />
              ))
            )}
          </TableBody>
        </Table>
      </div>

      <div className="flex flex-col gap-3 text-sm text-muted sm:flex-row sm:items-center sm:justify-between">
        <p>
          {filtered.length} users · page {page} / {totalPages}
        </p>
        <div className="flex flex-wrap items-center gap-2">
          <Button variant="secondary" size="sm" disabled={page <= 1} onClick={() => setPage((current) => current - 1)}>
            Previous
          </Button>
          {Array.from({ length: totalPages }, (_, index) => index + 1).map((number) => (
            <button
              key={number}
              type="button"
              onClick={() => setPage(number)}
              className={cn(
                "h-8 min-w-8 rounded-lg px-2 text-xs font-medium",
                number === page ? "bg-primary text-white shadow-glow-sm" : "text-muted hover:bg-surface"
              )}
            >
              {number}
            </button>
          ))}
          <Button
            variant="secondary"
            size="sm"
            disabled={page >= totalPages}
            onClick={() => setPage((current) => current + 1)}
          >
            Next
          </Button>
        </div>
      </div>

      {active && <UserSheet user={active} onClose={() => setActive(null)} />}
    </div>
  );
}

function UserRow({
  user,
  checked,
  onToggle,
  onView,
}: {
  user: MockUser;
  checked: boolean;
  onToggle: () => void;
  onView: () => void;
}) {
  const statusVariant = user.status === "Active" ? "success" : user.status === "Suspended" ? "danger" : "muted";
  const kycVariant = user.kyc === "Verified" ? "success" : user.kyc === "Rejected" ? "danger" : "gold";

  return (
    <TableRow>
      <TableCell>
        <input
          type="checkbox"
          className="h-4 w-4 accent-primary"
          checked={checked}
          onChange={onToggle}
          aria-label={`Select ${user.name}`}
        />
      </TableCell>
      <TableCell>
        <div className="flex items-center gap-2">
          <Avatar name={user.name} />
          <div>
            <p className="font-medium">{user.name}</p>
            <p className="text-xs text-muted">{user.email}</p>
          </div>
        </div>
      </TableCell>
      <TableCell className="font-mono text-xs">{user.id}</TableCell>
      <TableCell>
        <Badge variant={rankVariant(user.rank)}>{user.rank}</Badge>
      </TableCell>
      <TableCell className="tabular-nums">{user.joined}</TableCell>
      <TableCell>{user.package}</TableCell>
      <TableCell className="tabular-nums">{formatCurrency(user.wallet)}</TableCell>
      <TableCell>
        <Badge variant={statusVariant}>{user.status}</Badge>
      </TableCell>
      <TableCell>
        <Badge variant={kycVariant}>{user.kyc}</Badge>
      </TableCell>
      <TableCell>
        <Button variant="ghost" size="sm" onClick={onView} aria-label={`View ${user.name}`}>
          View
        </Button>
      </TableCell>
    </TableRow>
  );
}

function UserSheet({ user, onClose }: { user: MockUser; onClose: () => void }) {
  return (
    <div className="fixed inset-0 z-50 flex justify-end bg-black/60" onClick={onClose}>
      <aside
        className="h-full w-full max-w-md overflow-y-auto border-l border-border bg-surface p-6 shadow-card"
        onClick={(event) => event.stopPropagation()}
        role="dialog"
        aria-label={`${user.name} profile`}
      >
        <div className="flex items-start justify-between gap-3">
          <div className="flex items-center gap-3">
            <Avatar name={user.name} className="h-12 w-12 text-sm" />
            <div>
              <h2 className="font-display text-lg font-bold text-foreground">{user.name}</h2>
              <p className="text-sm text-muted">{user.email}</p>
            </div>
          </div>
          <button type="button" onClick={onClose} aria-label="Close profile" className="text-muted hover:text-foreground">
            <X className="h-5 w-5" />
          </button>
        </div>
        <dl className="mt-6 grid grid-cols-2 gap-3 text-sm">
          {[
            ["User ID", user.id],
            ["Rank", user.rank],
            ["Package", user.package],
            ["Joined", user.joined],
            ["Wallet", formatCurrency(user.wallet)],
            ["Status", user.status],
            ["KYC", user.kyc],
          ].map(([label, value]) => (
            <div key={label} className="rounded-xl bg-canvas p-3">
              <dt className="text-[10px] uppercase tracking-wider text-muted">{label}</dt>
              <dd className="mt-1 font-medium text-foreground">{value}</dd>
            </div>
          ))}
        </dl>
        <Button className="mt-6 w-full" variant="secondary" onClick={onClose}>
          Close
        </Button>
      </aside>
    </div>
  );
}
