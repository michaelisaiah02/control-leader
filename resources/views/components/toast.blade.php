@props([])

@php
    $toastType = null;
    $message = null;
    $title = 'Notification';
    $icon = 'bi-info-circle-fill';
    $bgColor = 'text-bg-primary';

    // Cek Success
    if (session()->has('success')) {
        $toastType = 'success';
        $message = session('success');
        $title = config('app.name') . ' - Success';
        $icon = 'bi-check-square-fill';
        $bgColor = 'text-bg-success';
    }
    // Cek Warning
    elseif (session()->has('warning')) {
        $toastType = 'warning';
        $message = session('warning');
        $title = config('app.name') . ' - Warning';
        $icon = 'bi-exclamation-triangle-fill';
        $bgColor = 'text-bg-warning';
    }
    // Cek Error (Session)
    elseif (session()->has('error')) {
        $toastType = 'error';
        $message = session('error');
        $title = config('app.name') . ' - Error';
        $icon = 'bi-x-square-fill';
        $bgColor = 'text-bg-danger';
    }
    // Cek Info
    elseif (session()->has('info')) {
        $toastType = 'info';
        $message = session('info');
        $title = config('app.name') . ' - Info';
        $icon = 'bi-info-circle-fill';
        $bgColor = 'text-bg-info';
    }
    // Cek Validation Errors
    elseif ($errors->any()) {
        $toastType = 'validation';
        $title = config('app.name') . ' - Error';
        $icon = 'bi-x-square-fill';
        $bgColor = 'text-bg-danger';
        // Gabungkan error jadi list HTML
        $message = '<ul class="mb-0 ps-3">';
        foreach ($errors->all() as $error) {
            $message .= "<li>$error</li>";
        }
        $message .= '</ul>';
    }
@endphp

@if ($toastType)
    {{-- Tambahkan class 'show' agar CSS Bootstrap langsung merendernya,
         tapi kita tetap butuh JS untuk fitur close/autohide --}}
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055;">
        <div class="toast align-items-center {{ $bgColor }} border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="toast-header">
                <i class="bi {{ $icon }} {{ str_replace('text-bg-', 'text-', $bgColor) }} me-2"></i>
                <strong class="me-auto">{{ $title }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {!! $message !!}
            </div>
        </div>
    </div>
@endif
