import { ArrowUpRight, ShieldCheck, Wallet } from "lucide-react";
import { Card, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { recentActivity } from "@/lib/mock-data";
import { cn } from "@/lib/utils";

const toneClass = {
  success: "bg-success",
  info: "bg-cyan",
  gold: "bg-gold",
  danger: "bg-danger",
};

export function RightSidebar({
  mobileOpen = false,
  onClose,
}: {
  mobileOpen?: boolean;
  onClose?: () => void;
}) {
  return (
    <>
      {mobileOpen && (
        <button
          type="button"
          className="fixed inset-0 z-30 bg-black/60 xl:hidden"
          aria-label="Close widgets"
          onClick={onClose}
        />
      )}
      <aside
        className={cn(
          "fixed inset-y-0 right-0 z-40 w-[300px] shrink-0 flex-col gap-4 overflow-y-auto border-l border-border bg-rail p-4",
          "xl:static xl:z-auto xl:flex",
          mobileOpen ? "flex" : "hidden"
        )}
      >
        <div className="flex items-center justify-between xl:hidden">
          <p className="text-sm font-semibold text-foreground">Widgets</p>
          <button type="button" className="text-xs text-muted" onClick={onClose}>
            Close
          </button>
        </div>

        <Card>
          <CardHeader>
            <CardTitle className="text-sm">Quick Profile</CardTitle>
          </CardHeader>
          <div className="flex items-center gap-3">
            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-cyan text-sm font-bold text-white shadow-glow-sm">
              SA
            </div>
            <div>
              <p className="font-semibold text-foreground">Samuel Admin</p>
              <p className="text-xs text-muted">salang@admin.test</p>
            </div>
          </div>
          <div className="mt-4 grid grid-cols-2 gap-2 text-center text-xs">
            <div className="rounded-xl bg-canvas p-2">
              <p className="text-muted">KYC</p>
              <p className="font-semibold text-success">Verified</p>
            </div>
            <div className="rounded-xl bg-canvas p-2">
              <p className="text-muted">Role</p>
              <p className="font-semibold text-primary">Super</p>
            </div>
          </div>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-sm">Recent Activity</CardTitle>
          </CardHeader>
          <ul className="space-y-3 text-sm">
            {recentActivity.map((item) => (
              <li key={item.id} className="flex gap-2 border-b border-border/60 pb-2 last:border-0 last:pb-0">
                <span className={cn("mt-1.5 h-2 w-2 shrink-0 rounded-full", toneClass[item.tone])} />
                <span>
                  <span className="block font-medium text-foreground">{item.user}</span>
                  <span className="block text-xs text-muted">{item.action}</span>
                  <span className="mt-1 block text-[10px] text-muted/80">{item.time}</span>
                </span>
              </li>
            ))}
          </ul>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-sm">Quick Actions</CardTitle>
          </CardHeader>
          <div className="space-y-2">
            <Button variant="secondary" className="w-full justify-between">
              Approve payouts <Wallet className="h-4 w-4 text-gold" />
            </Button>
            <Button variant="secondary" className="w-full justify-between">
              Review KYC <ShieldCheck className="h-4 w-4 text-success" />
            </Button>
            <Button variant="secondary" className="w-full justify-between">
              Export report <ArrowUpRight className="h-4 w-4 text-cyan" />
            </Button>
          </div>
        </Card>
      </aside>
    </>
  );
}
