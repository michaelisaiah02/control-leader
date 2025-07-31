@if ($masterlists->hasPages())
    <div class="pagination">
        {{ $masterlists->links() }}
    </div>
@endif
