<div class="d-flex flex-column gap-2">
    @foreach($examExercise->exercise->multipleChoiceOptions as $option)
        <label class="border rounded p-3 d-flex align-items-center gap-2 cursor-pointer answer-option">
            <input type="radio" class="form-check-input m-0 answer-input"
                   name="given_answer"
                   value="{{ $option->option_text }}">
            <span>{{ $option->option_text }}</span>
        </label>
    @endforeach
</div>
