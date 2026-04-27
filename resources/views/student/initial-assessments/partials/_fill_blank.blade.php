<div class="lh-lg">
    @php
    $parts = explode('__', $item->exercise->statement);
    $savedAnswers = $item->given_answer ? json_decode($item->given_answer, true) : [];
    @endphp

    @foreach($parts as $i => $part)
    {!! $part !!}
    @if(!$loop->last)
    <input type="text" name="answers[{{ $item->exercise_id }}][{{ $i }}]" value="{{ $savedAnswers[$i] ?? '' }}"
        placeholder="?" class="form-control-sm d-inline-block border-0 border-bottom rounded-0 px-1 mx-1 shadow-none text-center"
        style="width: 56px; min-width: 56px;">
    @endif
    @endforeach
</div>
