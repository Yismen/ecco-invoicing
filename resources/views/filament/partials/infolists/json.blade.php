@foreach ($json as $key => $item)
    <h5>
        <strong>{{ str($key)->headline() }}</strong>
    </h5>
    <ul style="margin-left: 20px;">
        @foreach ($item as $subKey => $subItem)
            <li>
                <strong>{{ str($subKey)->headline() }}:</strong> {{ is_array($subItem) || is_object($subItem) ? json_encode($subItem) : $subItem }}
            </li>
        @endforeach
    </ul>
    <hr>
@endforeach
