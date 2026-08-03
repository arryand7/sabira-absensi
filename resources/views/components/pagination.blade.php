@props(['paginator'])
@if($paginator && $paginator->hasPages())
    <nav class="sabira-pagination" aria-label="Navigasi halaman">{{ $paginator->onEachSide(1)->links() }}</nav>
@endif
