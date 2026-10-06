import { cn } from "@/lib/utils";

const variants = {
  default: "bg-primary/20 text-primary border-primary/30",
  success: "bg-success/15 text-success border-success/30",
  danger: "bg-danger/15 text-danger border-danger/30",
  gold: "bg-gold/15 text-gold border-gold/30",
  cyan: "bg-cyan/15 text-cyan border-cyan/30",
  muted: "bg-border/40 text-muted border-border",
};

export function Badge({
  className,
  variant = "default",
  ...props
}: React.HTMLAttributes<HTMLSpanElement> & { variant?: keyof typeof variants }) {
  return (
    <span
      className={cn(
        "inline-flex items-center rounded-lg border px-2 py-0.5 text-xs font-medium",
        variants[variant],
        className
      )}
      {...props}
    />
  );
}
