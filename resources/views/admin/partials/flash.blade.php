@if (session('success'))
    <div class="alert alert-success alert-dismissible mb-4 fade show" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible mb-4 fade show" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
