<form action="{{ route('logout') }}" method="post" class="ms-auto">
    @csrf
    <button type="submit"
        class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold d-flex align-items-center mx-auto">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
    </button>
</form>
