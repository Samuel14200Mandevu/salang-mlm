"use client";

import { useState } from "react";
import { Bell, Menu, PanelRight, Search } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Avatar } from "@/components/ui/avatar";
import { recentActivity } from "@/lib/mock-data";
import { cn } from "@/lib/utils";

const toneClass = {
  success: "bg-success",
  info: "bg-cyan",
  gold: "bg-gold",
  danger: "bg-danger",
};

export function AdminHeader({
  onMenuClick,
  onWidgetsClick,
}: {
  onMenuClick?: () => void;
  onWidgetsClick?: () => void;
}) {
  const [notificationsOpen, setNotificationsOpen] = useState(false);

  return (
    <header className="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-3 border-b border-border bg-canvas/80 px-4 backdrop-blur-md lg:px-6">
      <Button variant="ghost" size="icon" className="lg:hidden" onClick={onMenuClick} aria-label="Open menu">
        <Menu className="h-5 w-5" />
      </Button>

      <div className="relative hidden min-w-0 max-w-md flex-1 sm:block">
        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
        <Input className="pl-9 pr-14" placeholder="Search members, IDs, transactions…" aria-label="Search" />
        <kbd className="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-md border border-border bg-surface px-1.5 py-0.5 text-[10px] text-muted md:inline">
          ⌘K
        </kbd>
      </div>

      <div className="ml-auto flex items-center gap-2">
        <div className="relative">
          <Button
            variant="ghost"
            size="icon"
            aria-label="Notifications"
            aria-expanded={notificationsOpen}
            onClick={() => setNotificationsOpen((open) => !open)}
          >
            <Bell className="h-5 w-5" />
            <span className="absolute right-2 top-2 h-2 w-2 rounded-full bg-danger shadow-[0_0_8px_#EF4444]" />
          </Button>
          {notificationsOpen && (
            <div className="absolute right-0 top-12 z-30 w-80 rounded-2xl border border-border bg-surface p-3 shadow-card">
              <div className="mb-2 flex items-center justify-between px-1">
                <p className="text-sm font-semibold text-foreground">Notifications</p>
                <button
                  type="button"
                  className="text-xs text-muted hover:text-foreground"
                  onClick={() => setNotificationsOpen(false)}
                >
                  Close
                </button>
              </div>
              <ul className="space-y-2">
                {recentActivity.map((item) => (
                  <li key={item.id} className="flex gap-2 rounded-xl px-2 py-2 hover:bg-canvas">
                    <span className={cn("mt-1.5 h-2 w-2 shrink-0 rounded-full", toneClass[item.tone])} />
                    <span>
                      <span className="block text-sm font-medium text-foreground">{item.user}</span>
                      <span className="block text-xs text-muted">{item.action}</span>
                      <span className="mt-0.5 block text-[10px] text-muted/80">{item.time}</span>
                    </span>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>

        <Button
          variant="ghost"
          size="icon"
          className="xl:hidden"
          onClick={onWidgetsClick}
          aria-label="Open widgets"
        >
          <PanelRight className="h-5 w-5" />
        </Button>

        <div className="hidden items-center gap-2 rounded-xl border border-border bg-surface px-2 py-1 sm:flex">
          <Avatar name="Samuel Admin" />
          <div className="pr-2 text-left">
            <p className="text-xs font-semibold text-foreground">Samuel Admin</p>
            <p className="text-[10px] text-muted">Super Admin</p>
          </div>
        </div>
      </div>
    </header>
  );
}
