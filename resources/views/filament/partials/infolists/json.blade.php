@foreach ($json as $key => $item)
    <h5>{{ str($key)->headline() }}</h5>
    @foreach ($item as $elementKey => $elementValue)
        <p style="margin-left: 20px;">{{ $elementKey }}: {{ is_array($elementValue) ? \collect($elementValue)->toPrettyJson() : $elementValue }}</p>
    @endforeach
    <hr>
@endforeach
