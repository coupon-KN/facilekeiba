@extends('common.base')
@section('pageTitle', "レースカレンダー")


@section('pageContents')
    @include('common.menu-header', ['title' => 'レースカレンダー'])

    <div class="row m-2">
        <div class="col-12">
            <a class="btn btn-link" href="#" onclick="changeMonth(this); return false;" data-sel-ym="{{ $prevMonth }}">前の月</a>
            <span class="fs-5">{{ date("Y年m月", strtotime($targetDate)) }}</span>
            <a class="btn btn-link" href="#" onclick="changeMonth(this); return false;" data-sel-ym="{{ $nextMonth }}">次の月</a>

            {{ Form::open(['route' => 'calendar.index', 'method' => 'get', 'id' => 'frmChangeMonth']) }}
                <input type="hidden" id="selYm" name="selYm" />
            {{ Form::close() }}
        </div>

        <div class="col-6">
            <table class="table table-bordered calendar">
                <thead class="text-center">
                    @foreach(config("const.WEEK_SHORT_NAME") as $dayName)
                    <th>{{ $dayName }}</th>
                    @endforeach
                </thead>
                <tbody>
                @foreach($scheduleArray as $day)
                @if(($loop->iteration % 7) == 1)
                    <tr>
                @endif
                    @if(is_null($day))
                        <td></td>
                    @else
                        <td @if($day->date==date('Y-m-d')) class="today" @endif>
                            <span>{{ date("j", strtotime($day->date)) }}</span>
                            <a href="#" class="float-end" style="font-size: 0.7em;"
                                onclick="downloadSchedule(this); return false;"
                                data-target-date="{{$day->date}}"
                                title="開催情報取得"
                                >
                                <img src="/img/download.png" alt="取得" />
                            </a>
                            <div>
                            @if(count($day->centralArray) > 0)
                                <div style="font-size:10px;">中央</div>
                                <div style="font-size:14px;">
                                    @foreach($day->centralArray as $item)
                                        <a href="#" onclick="getRaceInfo(this); return false;"
                                            data-target-date="{{$day->date}}"
                                            data-race-id="{{ $item->id }}"
                                        >{{ $item->babaName }}</a>
                                    @endforeach
                                </div>
                            @endif
                            @if(count($day->regionArray) > 0)
                                <div style="font-size:10px;">地方</div>
                                <div style="font-size:14px;">
                                    @foreach($day->regionArray as $item)
                                        <a href="#" onclick="getRaceInfo(this); return false;"
                                            data-target-date="{{$day->date}}"
                                            data-race-id="{{ $item->id }}"
                                        >{{ $item->babaName }}</a>
                                    @endforeach
                                </div>
                            @endif
                            </div>
                        </td>
                    @endif
                @if(($loop->iteration % 7) == 0)
                    </tr>
                @endif
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-6">
            <div id="raceDetail" style="display: none;"></div>
        </div>
    </div>
    <input type="hidden" id="urlGetRaceInfo" value="{{ route('calendar.raceinfo') }}" />
    <input type="hidden" id="urlRaceDetail" value="{{ route('detail.index', 'dummy') }}" />
    <input type="hidden" id="urlKickRaceData" value="{{ route('calendar.kick.racedata') }}" />
    <img src="/img/spinner.gif" style="display:none" />
    {{ Form::open(['route' => 'calendar.get.schedule', 'method' => 'post', 'id' => 'frmDownloadSchedule']) }}
        <input type="hidden" id="selDate" name="sel_date" />
    {{ Form::close() }}

@endsection


@section('pageJs')
<script type="text/javascript">
    function changeMonth(e) {
        document.getElementById("selYm").value = e.dataset.selYm;
        document.getElementById("frmChangeMonth").submit();
    }

    function downloadSchedule(e) {
        document.getElementById("selDate").value = e.dataset.targetDate;
        document.getElementById("frmDownloadSchedule").submit();
    }

    async function getRaceInfo(e) {
        try {
            const url = document.getElementById("urlGetRaceInfo").value;
            const addHeader = {'Content-Type': 'application/json'};
            const bodyData = JSON.stringify({sel_date : e.dataset.targetDate, race_id : e.dataset.raceId});
            asyncPost(url, addHeader, bodyData, async(response) => {
                const json = await response.json();
                const detailBlock = document.getElementById("raceDetail");
                detailBlock.innerHTML = json.view;
                detailBlock.style = "display : block;";

                // ボタンを登録
                const btnRaceArr = document.getElementsByClassName("race-scraping");
                for(let i=0; i<btnRaceArr.length; i++) {
                    btnRaceArr[i].onclick = downloadRaceData;
                }
                const btnBulkArr = document.getElementsByClassName("bulk-scraping");
                for(let i=0; i<btnBulkArr.length; i++) {
                    btnBulkArr[i].onclick = bulkDownloadRaceData;
                }
            });
        } catch (error) {
            console.log(error);
        }
    }

    async function downloadRaceData(e){
        let tr = e.currentTarget.parentElement.parentElement;
        let raceId = e.currentTarget.dataset.raceId;
        await callScrapingRaceData(tr, raceId);
    }
    async function bulkDownloadRaceData(e){
        const btnRaceArr = document.getElementsByClassName("race-scraping");
        for(let i=0; i<btnRaceArr.length; i++) {
            let tr = document.getElementById("raceDetail").getElementsByTagName("tr")[i];
            let raceId = btnRaceArr[i].dataset.raceId;
            await callScrapingRaceData(tr, raceId);
        }
    }
    async function callScrapingRaceData(tblRow, raceId) {
        let waitingDiv = document.createElement("div");
        waitingDiv.className = "waiting";
        tblRow.getElementsByTagName("td")[4].innerText = "";
        tblRow.getElementsByTagName("td")[4].append(waitingDiv);

        try {
            const url = document.getElementById("urlKickRaceData").value;
            const addHeader = {'Content-Type': 'application/json'};
            const bodyData = JSON.stringify({race_id : raceId});
            await asyncPost(url, addHeader, bodyData, async(response) => {
                const json = await response.json();
                console.log(json);

                let anchor = document.createElement("a");
                tblRow.getElementsByTagName("td")[1].innerText = json.data.startingTime;
                tblRow.getElementsByTagName("td")[2].innerText = json.data.distance;
                tblRow.getElementsByTagName("td")[3].innerText = json.data.horseCount;
                tblRow.getElementsByTagName("td")[4].innerText = "";
                anchor.href = json.data.detailUrl;
                anchor.target = "_blank";
                anchor.text = json.data.name;
                tblRow.getElementsByTagName("td")[4].append(anchor);
            });
        } catch (error) {
            console.log(error);
        }
    }

</script>
@endsection


@section('pageCss')
<style>
    table {
        table-layout:fixed;
    }
    .calendar {
        margin-bottom:0px !important;
    }
    .calendar th {
        background-color:#fff4e6 !important;
    }
    .calendar td {
        height: 130px;
        position: relative;
    }
    .calendar td:nth-child(1) {
        background-color:#fbf1f1 !important;
    }
    .calendar td:nth-child(7) {
        background-color:#f1f4fb !important;
    }
    .calendar td.today {
        background-color:#fffada !important;
    }
    .calendar td > div {
        position: absolute;
        bottom: 0;
        margin-bottom: 4px;
    }
    #raceDetail {
        position: relative;
        border: dashed 1px #ccc;
        height: 100%;
        padding: 5px 10px;
    }
    table.detail td:nth-child(1) {width : 40px;}
    table.detail td:nth-child(2) {width : 60px;}
    table.detail td:nth-child(3) {width : 90px;}
    table.detail td:nth-child(4) {width : 60px;}
    table.detail td:nth-child(5) {width : auto;}
    table.detail td:nth-child(6) {width : 80px;}
    .waiting{
        width: 24px;
        height: 24px;
        background-image: url("/img/spinner.gif");
        background-position: center;
        background-repeat: no-repeat;
    }
</style>
@endsection
