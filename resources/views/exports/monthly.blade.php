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
            <td>total score</td>
            @foreach($result as $idx => $activity)
            @php
            $total = $activity['count'];

            if(in_array($activity['type'], ['value', 'badhabit'])) {
                $total = $activity['histories']->sum('value');
            }
            @endphp
            <td>{{$total}}</td>
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
