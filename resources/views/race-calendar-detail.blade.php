<div class="fs-4">{{ $title }}</div>
<table class="table detail">
    <tbody>
        @foreach($data As $row)
        <tr>
            <td>{{$loop->index + 1}}R</td>
            @if($row['isExists'])
                <td>{{ $row["startingTime"] }}</td>
                <td>{{ $row["distance"] }}</td>
                <td>{{ $row["horseCount"] }}</td>
                <td>
                    <a href="{{ route('detail.index', $row['raceId']) }}" target="_blank">{{ $row["name"] }}</a>
                </td>
            @else
                <td></td>
                <td></td>
                <td></td>
                <td>※ データ未取得</td>
            @endif
            <td class="p-0 align-middle">
                <button type="button" class="race-scraping" style="border:solid 1px #CCC;" data-race-id="{{ $row['raceId'] }}" title="レース情報取得">
                    <img src="/img/download.png" alt="" />
                    <span>取得</span>
                </button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="bottom-0 mb-4 me-4 end-0 text-end">
    <button type="button" class="btn btn-success bulk-scraping">レース情報一括取得</button>
</div>