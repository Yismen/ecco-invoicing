<div class="flex gap-2 justify-start items-start">
    {{-- {{ dd($activity->changes['old']) }} --}}
    <p>
        <small class="text-gray-500">
            @if ($activity->causer)
                {{ $activity->description == 'created' ? 'Created' : 'Updated' }} by {{ $activity->causer->name ?? 'Unknown User' }}
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

    @if ($activity->changes['old'] ?? null)
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
                        <strong>
                            {{ $item['field'] }}:
                        </strong>
                        <span class="text-wrap" style="color: rgb(170, 10, 10);">{{ $item['old_value'] }}</span>
                        &rarr;
                        <span class="text-wrap" style="color: rgb(9, 81, 9);">{{ $item['new_value'] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
