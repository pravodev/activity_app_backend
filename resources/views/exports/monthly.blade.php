<table>
    <thead>
        <tr>
            <th></th>
            @foreach($result as $activity)
            <th style="background-color: {{ $activity['color'] }}; color: white">{{ $activity['title'] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php
        $max = $result->max('count');
        @endphp
        <tr>
            <td>total count</td>
            @foreach($result as $idx => $activity)
            <td>{{$activity['count']}}</td>
            @endforeach
        </tr>
        @for ($i = 0; $i < $max; $i++)
        <tr>
            <td></td>
            @foreach($result as $idx => $activity)
            <td>{{$activity['histories'][$i]->value ?? null}}</td>
            @endforeach
        </tr>
        @endfor
    </tbody>
</table>
