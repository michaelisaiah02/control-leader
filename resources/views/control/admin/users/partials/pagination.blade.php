@if ($users->hasPages())
    <div class="pagination">
        {{ $users->links() }}
    </div>
@endif
