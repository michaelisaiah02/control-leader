@if ($equipments->hasPages())
    <div class="pagination">
        {{ $equipments->links() }}
    </div>
@endif
