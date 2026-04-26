<div style="display: flex; flex-direction: column; gap: 8px;">
    @foreach(['true' => 'Verdadero', 'false' => 'Falso'] as $value => $label)
    <label style="display: flex; align-items: center; gap: 12px; padding: 0.75rem 1rem;
                border: 1px solid #e5e7eb; border-radius: 8px; cursor: pointer;">
        <input type="radio" name="answers[{{ $item->exercise_id }}]" value="{{ $value }}" {{ $item->given_answer ===
        $value ? 'checked' : '' }}>
        <span style="font-size: 15px;">{{ $label }}</span>
    </label>
    @endforeach
</div>
