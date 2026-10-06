"use client";

import { useEffect, useState } from "react";
import { usePathname } from "next/navigation";
import { AdminHeader } from "@/components/layout/AdminHeader";
import { AdminSidebar } from "@/components/layout/AdminSidebar";
import { RightSidebar } from "@/components/layout/RightSidebar";
import { cn } from "@/lib/utils";

export function AdminShell({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const [mobileNavOpen, setMobileNavOpen] = useState(false);
  const [widgetsOpen, setWidgetsOpen] = useState(false);

  useEffect(() => {
    setMobileNavOpen(false);
    setWidgetsOpen(false);
  }, [pathname]);

  return (
    <div className="flex h-screen overflow-hidden bg-canvas text-foreground">
      <div
        className={cn(
          "fixed inset-y-0 left-0 z-40 transition-transform duration-200 lg:static lg:translate-x-0",
          mobileNavOpen ? "translate-x-0" : "-translate-x-full"
        )}
      >
        <AdminSidebar onNavigate={() => setMobileNavOpen(false)} />
      </div>
      {mobileNavOpen && (
        <button
          type="button"
          className="fixed inset-0 z-30 bg-black/60 lg:hidden"
          aria-label="Close menu"
          onClick={() => setMobileNavOpen(false)}
        />
      )}

      <div className="flex min-w-0 flex-1 flex-col">
        <AdminHeader
          onMenuClick={() => {
            setWidgetsOpen(false);
            setMobileNavOpen(true);
          }}
          onWidgetsClick={() => {
            setMobileNavOpen(false);
            setWidgetsOpen(true);
          }}
        />
        <div className="flex min-h-0 flex-1">
          <main className="min-w-0 flex-1 overflow-y-auto">
            <div className="mx-auto w-full max-w-[1440px] animate-fade-in p-4 lg:p-6">{children}</div>
          </main>
          <RightSidebar mobileOpen={widgetsOpen} onClose={() => setWidgetsOpen(false)} />
        </div>
      </div>
    </div>
  );
}
