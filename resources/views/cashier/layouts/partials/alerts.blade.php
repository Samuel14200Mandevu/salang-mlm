            @if (session('success'))
                <div class="mx-3 sm:mx-4 lg:mx-6 mt-3 rounded-lg border border-primary-200 bg-primary-50 px-4 py-3 text-sm text-primary-900" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mx-3 sm:mx-4 lg:mx-6 mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    {{ session('error') }}
                </div>
            @endif
