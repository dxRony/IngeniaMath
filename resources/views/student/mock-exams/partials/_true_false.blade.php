<div class="d-flex flex-column gap-2">
    @foreach(['Verdadero' => 'Verdadero', 'Falso' => 'Falso'] as $value => $label)
        <label class="border rounded p-3 d-flex align-items-center gap-2 answer-option">
            <input type="radio" class="form-check-input m-0 answer-input"
                   name="given_answer"
                   value="{{ $value }}">
            <span>{{ $label }}</span>
        </label>
    @endforeach
</div>
