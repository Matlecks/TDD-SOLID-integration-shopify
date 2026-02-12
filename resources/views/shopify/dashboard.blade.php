@extends('layouts.app')

@section('title', 'Dashboard - Shopify App')

@section('content')
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 fw-bold mb-0">
                <i class="bi bi-speedometer2 me-2 text-primary"></i>
                Dashboard
            </h1>
            <p class="text-muted mt-2">
                @if($shop)
                    Welcome back, Store Owner! Here's an overview of <strong>{{ $shop->name }}</strong>.
                @else
                    Welcome to your Shopify dashboard! Get started by installing your first shop.
                @endif
            </p>
        </div>
    </div>

    @if(!$shop)
        {{-- No shop installed state --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <div class="py-5">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-4 mb-4">
                                <i class="bi bi-shop text-primary fs-1"></i>
                            </div>
                            <h3 class="fw-bold mb-3">No Shopify Store Connected</h3>
                            <p class="text-muted mb-4 col-lg-6 mx-auto">
                                Get started by connecting your Shopify store to manage products, track inventory, and monitor your store performance.
                            </p>
                            <a href="{{ route('shopify.install') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle me-2"></i>
                                Connect Shopify Store
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row g-4">
            {{-- Recent Products --}}
            <div class="col-xl-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-semibold mb-0">
                                <i class="bi bi-clock-history me-2 text-primary"></i>
                                Recent Products
                            </h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($recentProducts as $product)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                @if($product->image)
                                                    <img src="{{ $product->image }}"
                                                         alt="{{ $product->title }}"
                                                         class="rounded me-3"
                                                         width="40"
                                                         height="40"
                                                         style="object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                         style="width: 40px; height: 40px;">
                                                        <i class="bi bi-image text-secondary"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-medium">{{ Str::limit($product->title, 30) }}</div>
                                                    <small class="text-muted">{{ $product->vendor ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($product->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @elseif($product->status === 'draft')
                                                <span class="badge bg-warning text-dark">Draft</span>
                                            @else
                                                <span class="badge bg-secondary">Archived</span>
                                            @endif
                                        </td>
                                        <td>${{ number_format($product->price, 2) }}</td>
                                        <td>
                                            <span class="{{ $product->total_inventory > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $product->total_inventory }} in stock
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-muted mb-2">No products yet</p>
                                            <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-plus-circle me-2"></i>
                                                Add Your First Product
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
