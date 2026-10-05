@if (isset($paginator) && $paginator->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $paginator->links() }}
    </div>
@endif
