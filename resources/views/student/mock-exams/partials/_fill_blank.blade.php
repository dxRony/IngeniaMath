@php
    $parts = explode('___', $examExercise->exercise->statement);
@endphp

<div class="lh-lg">
    @foreach($parts as $i => $part)
        {!! $part !!}
        @if(!$loop->last)
            <input type="text"
                   name="given_answer[]"
                   class="form-control-sm d-inline-block border-0 border-bottom rounded-0 px-1 mx-1 shadow-none text-center answer-input"
                   placeholder="?"
                   autocomplete="off"
                   style="width: 80px; min-width: 80px;">
        @endif
    @endforeach
</div>

<small class="text-muted d-block mt-3">
    Completa el espacio en blanco con la respuesta correcta.
</small>
