@if ($paginator->hasPages())
    <nav
        role="navigation"
        aria-label="{{ __('Pagination Navigation') }}"
        class="salang-pagination-nav salang-pagination-nav--simple"
        data-salang-pagination
        data-prev-url="{{ $paginator->onFirstPage() ? '' : $paginator->previousPageUrl() }}"
        data-next-url="{{ $paginator->hasMorePages() ? $paginator->nextPageUrl() : '' }}"
    >
        <div class="salang-pagination salang-pagination--mobile md:hidden">
            <div class="salang-pagination__home-bar" aria-hidden="true">
                <span class="salang-pagination__home-thumb" data-salang-pagination-thumb></span>
            </div>
        </div>

        <div class="salang-pagination salang-pagination--desktop hidden md:flex md:items-center md:justify-between md:gap-3 md:w-full">
            @if ($paginator->onFirstPage())
                <span class="salang-pagination__desktop-text is-disabled">{{ __('pagination.previous') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="salang-pagination__desktop-text">{{ __('pagination.previous') }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="salang-pagination__desktop-text">{{ __('pagination.next') }}</a>
            @else
                <span class="salang-pagination__desktop-text is-disabled">{{ __('pagination.next') }}</span>
            @endif
        </div>
    </nav>
@endif
