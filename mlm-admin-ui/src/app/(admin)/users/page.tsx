import { UsersDataGrid } from "@/components/users/UsersDataGrid";

export default function UsersPage() {
  return (
    <div className="space-y-6">
      <div>
        <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-primary">Network</p>
        <h1 className="mt-1 font-display text-2xl font-bold text-foreground">User Management</h1>
        <p className="text-sm text-muted">Search, filter, and review member accounts</p>
      </div>
      <UsersDataGrid />
    </div>
  );
}
