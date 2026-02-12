@extends('layouts.app')

@section('title', 'Install Shopify App')

@section('content')
    <div class="row justify-content-center min-vh-75 align-items-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    {{-- Header --}}
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-4 mb-3">
                            <i class="bi bi-shop text-primary fs-1"></i>
                        </div>

                        <h1 class="h2 fw-bold text-dark mb-2">
                            Install Shopify App
                        </h1>

                        <p class="text-muted">
                            Connect your Shopify store to start syncing products and orders
                        </p>
                    </div>

                    {{-- Install Form --}}
                    <form action="{{ route('shopify.install') }}" method="GET">
                        <div class="mb-4">
                            <label for="shop" class="form-label fw-medium text-dark">
                                Your Shopify Store URL
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-light">https://</span>
                                <input type="text"
                                       class="form-control"
                                       id="shop"
                                       name="shop"
                                       value="{{ old('shop', 'ndpmsu-41.myshopify.com') }}"
                                       placeholder="your-store.myshopify.com"
                                       required>
                            </div>

                            <div class="form-text">
                                Enter your Shopify store domain (e.g., your-store.myshopify.com)
                            </div>
                        </div>

                        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                            <div>
                                You will be redirected to Shopify to authorize the app installation.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-medium">
                            <i class="bi bi-download me-2"></i>
                            Install App
                        </button>
                    </form>

                    {{-- Help section --}}
                    <div class="text-center mt-4">
                        <p class="text-muted small mb-0">
                            Don't have a Shopify store?
                            <a href="https://www.shopify.com/signup" target="_blank" class="text-primary text-decoration-none fw-medium">
                                Create one now
                                <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const shopInput = document.getElementById('shop');

            shopInput.addEventListener('input', function(e) {
                let value = e.target.value;
                // Remove http://, https://, and trailing slashes
                value = value.replace(/^https?:\/\//, '')
                    .replace(/\/$/, '');
                e.target.value = value;
            });
        });
    </script>
@endpush
