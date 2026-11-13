<a href="{{ route('admin.reports.export', ['type' => 'users']) }}" class="btn btn-outline btn-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
    </svg>
    Exporter CSV
</a>
<a href="{{ route('admin.reports.pdf', ['type' => 'users']) }}" class="btn btn-outline btn-sm">
    <x-pdf-icon class="w-4 h-4" />
    PDF
</a>
