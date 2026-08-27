@props([
    'title' => '',
])

<div class="page-header">
    <div>
        @if (isset($breadcrumbs))
            <nav class="breadcrumb-nav">
                {{ $breadcrumbs }}
            </nav>
        @endif
        <h1 class="page-title">{{ $title }}</h1>
    </div>
    @if (isset($actions))
        <div class="page-header-actions d-flex align-items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
