@if ($standards->hasPages())
    <div class="pagination">
        {{ $standards->links() }}
    </div>
@endif
