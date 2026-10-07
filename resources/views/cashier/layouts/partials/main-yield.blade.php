            <!-- Content -->
            <main class="main-content" id="main-content">
                <div class="admin-mobile-page space-y-3 sm:space-y-6 @stack('cashier_mobile_page_extra_classes')">
                    @stack('cashier_mobile_greeting')
                    @yield('content')
                </div>
            </main>
