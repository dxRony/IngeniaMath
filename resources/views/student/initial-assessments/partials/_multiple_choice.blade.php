<div class="d-flex flex-column gap-2">
    @foreach($item->exercise->multipleChoiceOptions as $option)
    <label class="border rounded p-3 d-flex align-items-center gap-2 cursor-pointer">
        <input type="radio" class="form-check-input m-0" name="answers[{{ $item->exercise_id }}]" value="{{ $option->option_text }}" {{ $item->given_answer ==
        $option->option_text ? 'checked' : '' }}>
        <span>{{ $option->option_text }}</span>
    </label>
    @endforeach
</div>
