<div id="{{ $id }}" class="p-2 w-100 border border-2 border-primary rounded-2 d-none">
    <label class="form-label">{{ $label }}</label>
    @if ($type === 'text')
    <input type="text"
        class="form-control bg-warning-subtle"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}">
    @elseif ($type === 'radio')
    @foreach ($options as $option)
    <div class="form-check">
        <input class="form-check-input" type="radio" name="{{ $name }}" id="{{ $id }}-{{ $option }}" value="{{ $option }}">
        <label class="form-check-label" for="{{ $id }}-{{ $option }}">
            {{ ucfirst($option) }}
        </label>
    </div>
    @endforeach
    @endif
</div>