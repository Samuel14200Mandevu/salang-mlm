<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
@include('cashier.layouts.partials.head')
@include('cashier.layouts.partials.app-shell-open')
@include('cashier.layouts.partials.sidebar')
@include('cashier.layouts.partials.main-content')
@include('cashier.layouts.partials.bottom-nav')
@include('cashier.layouts.partials.pos-interface')
@include('cashier.layouts.partials.modals')
@include('cashier.layouts.partials.scripts')
</html>
