<svg class="public-onb-illu" viewBox="0 0 320 280" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="ob2Disc" x1="60" y1="40" x2="260" y2="240" gradientUnits="userSpaceOnUse">
            <stop stop-color="var(--ob-disc-a)"/>
            <stop offset="1" stop-color="var(--ob-disc-b)"/>
        </linearGradient>
        <linearGradient id="ob2Phone" x1="112" y1="56" x2="208" y2="226" gradientUnits="userSpaceOnUse">
            <stop stop-color="#1f2937"/>
            <stop offset="1" stop-color="#0b1220"/>
        </linearGradient>
        <linearGradient id="ob2Screen" x1="122" y1="74" x2="198" y2="200" gradientUnits="userSpaceOnUse">
            <stop stop-color="var(--ob-screen-a)"/>
            <stop offset="1" stop-color="var(--ob-screen-b)"/>
        </linearGradient>
        <linearGradient id="ob2Ok" x1="140" y1="100" x2="180" y2="140" gradientUnits="userSpaceOnUse">
            <stop stop-color="#4ade80"/>
            <stop offset="1" stop-color="#16a34a"/>
        </linearGradient>
        <linearGradient id="ob2Btn" x1="132" y1="184" x2="188" y2="196" gradientUnits="userSpaceOnUse">
            <stop stop-color="var(--ob-blue-a)"/>
            <stop offset="1" stop-color="var(--ob-blue-b)"/>
        </linearGradient>
        <filter id="ob2Sh" x="-25%" y="-25%" width="150%" height="150%">
            <feDropShadow dx="0" dy="12" stdDeviation="9" flood-color="#0f172a" flood-opacity="0.2"/>
        </filter>
    </defs>
    <circle cx="160" cy="146" r="108" fill="url(#ob2Disc)"/>
    <ellipse cx="160" cy="236" rx="72" ry="10" fill="var(--ob-ground)"/>

    <g filter="url(#ob2Sh)" transform="rotate(-12 78 168)">
        <rect x="50" y="148" width="58" height="40" rx="9" fill="var(--ob-surface)" stroke="var(--ob-blue-b)" stroke-width="1.5"/>
        <rect x="60" y="160" width="28" height="5" rx="2.5" fill="var(--ob-blue-a)"/>
        <rect x="60" y="170" width="18" height="5" rx="2.5" fill="var(--ob-surface-2)"/>
    </g>

    <g filter="url(#ob2Sh)">
        <rect x="112" y="56" width="96" height="170" rx="17" fill="url(#ob2Phone)"/>
        <rect x="121" y="74" width="78" height="128" rx="10" fill="url(#ob2Screen)"/>
        <rect x="147" y="63" width="26" height="5" rx="2.5" fill="#334155"/>
        <circle cx="160" cy="118" r="22" fill="url(#ob2Ok)"/>
        <path d="M149 118l7 7 14-14" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="136" y="152" width="48" height="8" rx="4" fill="var(--ob-muted)"/>
        <rect x="144" y="168" width="32" height="6" rx="3" fill="var(--ob-muted-2)"/>
        <rect x="134" y="184" width="52" height="11" rx="5.5" fill="url(#ob2Btn)"/>
    </g>

    <path d="M248 150c-14-26-36-34-48-34 6 16 4 36-4 50 22 4 38-2 52-16z" fill="#7cb342" opacity="0.9" filter="url(#ob2Sh)"/>
</svg>
