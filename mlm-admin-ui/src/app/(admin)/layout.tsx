import { AdminShell } from "@/components/layout/AdminShell";

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="admin-theme dark min-h-screen">
      <AdminShell>{children}</AdminShell>
    </div>
  );
}
