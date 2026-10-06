import Image from "next/image";
import Link from "next/link";
import { cn } from "@/lib/utils";

type SalangLogoProps = {
  className?: string;
  showWordmark?: boolean;
  href?: string;
};

export function SalangLogo({ className, showWordmark = true, href = "/" }: SalangLogoProps) {
  const content = (
    <>
      <Image
        src="/salang_logo.png"
        alt="Salang Group"
        width={132}
        height={36}
        className="h-9 w-auto"
        priority
      />
      {showWordmark && (
        <span className="font-display text-base font-bold tracking-tight text-ink dark:text-ink-inverse">
          Salang
        </span>
      )}
    </>
  );

  return (
    <Link
      href={href}
      className={cn("inline-flex items-center gap-2.5 no-underline", className)}
      aria-label="Salang — accueil"
    >
      {content}
    </Link>
  );
}
