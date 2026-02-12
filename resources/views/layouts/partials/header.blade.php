{{-- file: header.blade.php --}}
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        {{-- Logo --}}
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
            <i class="bi bi-shop text-primary fs-3 me-2"></i>
            <span class="fw-bold text-dark">{{ config('app.name') }}</span>
        </a>

        {{-- Navigation --}}
        <ul class="navbar-nav ms-auto align-items-lg-center">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('home') ? 'active fw-semibold' : '' }}"
                   href="{{ route('home') }}">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </a>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {{ request()->routeIs('products.*') ? 'active fw-semibold' : '' }}"
                   href="#"
                   id="productsDropdown"
                   role="button"
                   data-bs-toggle="dropdown"
                   aria-expanded="false">
                    <i class="bi bi-box me-1"></i>Products
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="productsDropdown">
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('products.index') ? 'active' : '' }}"
                           href="{{ route('products.index') }}">
                            <i class="bi bi-list-ul me-2"></i>All Products
                        </a>
                    </li>
{{--                    <li>--}}
{{--                        <a class="dropdown-item {{ request()->routeIs('products.create') ? 'active' : '' }}"--}}
{{--                           href="{{ route('products.create') }}">--}}
{{--                            <i class="bi bi-plus-circle me-2"></i>Add New Product--}}
{{--                        </a>--}}
{{--                    </li>--}}
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#syncModal">
                            <i class="bi bi-arrow-repeat me-2"></i>Sync from Shopify
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

{{-- Sync Modal (добавьте этот модал здесь или в основном шаблоне) --}}
@auth
    <div class="modal fade" id="syncModal" tabindex="-1" aria-labelledby="syncModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="syncModalLabel">
                        <i class="bi bi-arrow-repeat me-2 text-primary"></i>
                        Sync Products
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary mb-3" role="status" id="syncSpinner" style="display: none;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mb-0">This will sync all products from your Shopify store.</p>
                        <p class="text-muted small mt-2">Existing products will be updated, new products will be added.</p>
                        <div id="syncMessage" class="alert mt-3" style="display: none;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="syncButton">
                        <i class="bi bi-arrow-repeat me-2"></i>
                        Start Sync
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Handle sync button
                const syncButton = document.getElementById('syncButton');
                if (syncButton) {
                    syncButton.addEventListener('click', function () {
                        const spinner = document.getElementById('syncSpinner');
                        const messageDiv = document.getElementById('syncMessage');
                        const modalFooter = document.querySelector('#syncModal .modal-footer');
                        const closeButton = document.querySelector('#syncModal .btn-close');

                        spinner.style.display = 'inline-block';
                        syncButton.disabled = true;
                        messageDiv.style.display = 'none';

                        // Disable close buttons during sync
                        if (closeButton) closeButton.disabled = true;
                        if (modalFooter) {
                            modalFooter.querySelectorAll('.btn-outline-secondary').forEach(btn => {
                                btn.disabled = true;
                            });
                        }

                        fetch('/products/sync', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                spinner.style.display = 'none';
                                messageDiv.style.display = 'block';

                                if (data.success) {
                                    messageDiv.className = 'alert alert-success mt-3';
                                    messageDiv.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message;

                                    // Reload page after 2 seconds
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 2000);
                                } else {
                                    messageDiv.className = 'alert alert-danger mt-3';
                                    messageDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + data.message;

                                    // Re-enable buttons
                                    syncButton.disabled = false;
                                    if (closeButton) closeButton.disabled = false;
                                    if (modalFooter) {
                                        modalFooter.querySelectorAll('.btn-outline-secondary').forEach(btn => {
                                            btn.disabled = false;
                                        });
                                    }
                                }
                            })
                            .catch(error => {
                                spinner.style.display = 'none';
                                messageDiv.style.display = 'block';
                                messageDiv.className = 'alert alert-danger mt-3';
                                messageDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>An error occurred during sync';

                                // Re-enable buttons
                                syncButton.disabled = false;
                                if (closeButton) closeButton.disabled = false;
                                if (modalFooter) {
                                    modalFooter.querySelectorAll('.btn-outline-secondary').forEach(btn => {
                                        btn.disabled = false;
                                    });
                                }
                            });
                    });
                }
            });
        </script>
    @endpush
@endauth
