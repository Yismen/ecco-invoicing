<div class="flex gap-2">
    {{-- {{ dd($activity->changes['old']) }} --}}
    <p>
        <small class="text-gray-500">
            @if ($activity->causer)
                Edited by {{ $activity->causer->name ?? 'Unknown User' }}
            @else
                System
            @endif
            <br>
            <time datetime="{{ $activity->created_at->toW3cString() }}">
                {{ $activity->created_at->format('d-M-y') }}, {{ $activity->created_at->diffForHumans() }}
            </time>
        </small>
    </p>
    {{-- {{ html_entity_decode($activity->changes) }} --}}

    <div class="flex flex-wrap gap-2">
        @foreach ($activity->changes['old'] as $key => $value)
            @php
                $item = [
                    'field' => ucwords(str_replace('_', ' ', $key)),
                    'old_value' => $value,
                    'new_value' => $activity->changes['attributes'][$key] ?? null,
                ];
            @endphp
            @if ($item['old_value'] !== $item['new_value'])
                <div class="bg-gray-100 p-2 rounded border border-gray-300 text-xs text-wrap ">
                    <strong>{{ $item['field'] }}</strong>: {{ $item['old_value'] }} &rarr; {{ $item['new_value'] }}
                </div>
            @endif
        @endforeach
    </div>
</div>
