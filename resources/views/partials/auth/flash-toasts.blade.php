@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    {{-- Erreurs de formulaire : affichées dans partials.auth.alerts (évite toast + icône surdimensionnée) --}}
    @if (session('success'))
        window.showToast?.(@json(session('success')), 'success');
    @endif
    @if (session('error'))
        window.showToast?.(@json(session('error')), 'error');
    @endif
});
</script>
@endpush
