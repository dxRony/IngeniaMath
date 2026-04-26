<div style="font-size: 15px; line-height: 2.2;">
    @php
    $parts = explode('___', $item->exercise->statement);
    $savedAnswers = $item->given_answer ? json_decode($item->given_answer, true) : [];
    @endphp

    @foreach($parts as $i => $part)
    {!! $part !!}
    @if(!$loop->last)
    <input type="text" name="answers[{{ $item->exercise_id }}][{{ $i }}]" value="{{ $savedAnswers[$i] ?? '' }}"
        placeholder="?" style="width: 90px; font-size: 15px; border: none;
                    border-bottom: 1.5px solid #374151; background: transparent;
                    padding: 2px 4px; margin: 0 4px; outline: none;">
    @endif
    @endforeach
</div>
