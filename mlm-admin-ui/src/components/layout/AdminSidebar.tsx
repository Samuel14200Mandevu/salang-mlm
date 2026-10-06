"use client";

import Image from "next/image";
import Link from "next/link";
import { usePathname } from "next/navigation";
import {
  GitBranch,
  LayoutDashboard,
  Settings,
  Users,
  Wallet,
  type LucideIcon,
} from "lucide-react";
import { cn } from "@/lib/utils";

const sections: { label: string; items: { href: string; label: string; icon: LucideIcon }[] }[] = [
  {
    label: "Overview",
    items: [{ href: "/dashboard", label: "Dashboard", icon: LayoutDashboard }],
  },
  {
    label: "Network",
    items: [
      { href: "/users", label: "User Management", icon: Users },
      { href: "/binary", label: "Binary Management", icon: GitBranch },
    ],
  },
  {
    label: "Finance",
    items: [{ href: "/wallets", label: "Wallet Management", icon: Wallet }],
  },
  {
    label: "System",
    items: [{ href: "/settings", label: "Settings", icon: Settings }],
  },
];

export function AdminSidebar({ onNavigate }: { onNavigate?: () => void }) {
  const pathname = usePathname();

  return (
    <aside className="flex h-full w-[250px] shrink-0 flex-col border-r border-border bg-sidebar">
      <div className="flex h-16 items-center gap-3 border-b border-border px-5">
        <div className="relative flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-border bg-surface shadow-glow-sm">
          <Image src="/salang_logo.png" alt="" width={36} height={36} className="object-contain p-0.5" priority />
        </div>
        <div className="min-w-0">
          <p className="truncate font-display text-lg font-bold leading-none text-foreground">Salang Group</p>
          <p className="mt-1 truncate text-[10px] uppercase tracking-[0.18em] text-muted">MLM Admin</p>
        </div>
      </div>

      <nav className="flex-1 space-y-5 overflow-y-auto p-3">
        {sections.map((section) => (
          <div key={section.label}>
            <p className="px-3 pb-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-muted/80">
              {section.label}
            </p>
            <div className="space-y-1">
              {section.items.map(({ href, label, icon: Icon }) => {
                const active = pathname === href || pathname.startsWith(`${href}/`);
                return (
                  <Link
                    key={href}
                    href={href}
                    aria-current={active ? "page" : undefined}
                    onClick={onNavigate}
                    className={cn(
                      "flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-muted transition-all hover:bg-surface hover:text-foreground",
                      active && "nav-link-active"
                    )}
                  >
                    <Icon className="h-4 w-4 shrink-0" />
                    {label}
                  </Link>
                );
              })}
            </div>
          </div>
        ))}
      </nav>

      <div className="p-3">
        <div className="rounded-2xl bg-upgrade-card p-4 shadow-glow">
          <p className="text-sm font-semibold text-white">Salang Analytics Pro</p>
          <p className="mt-1 text-xs leading-relaxed text-white/80">
            Commissions en temps réel, alertes réseau et exports conformes pour votre back-office Laravel.
          </p>
          <button
            type="button"
            className="mt-3 w-full rounded-xl bg-white/15 py-2 text-xs font-semibold text-white backdrop-blur transition hover:bg-white/25"
          >
            Bientôt disponible
          </button>
        </div>
      </div>
    </aside>
  );
}
