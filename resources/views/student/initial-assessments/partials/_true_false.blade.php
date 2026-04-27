<div class="d-flex flex-column gap-2">
    @foreach(['true' => 'Verdadero', 'false' => 'Falso'] as $value => $label)
    <label class="border rounded p-3 d-flex align-items-center gap-2">
        <input type="radio" name="answers[{{ $item->exercise_id }}]" value="{{ $value }}" {{ $item->given_answer ===
        $value ? 'checked' : '' }} class="form-check-input m-0">
        <span>{{ $label }}</span>
    </label>
    @endforeach
</div>
