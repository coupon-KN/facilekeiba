@extends('common.base')
@section('pageTitle', $pageTitle)


@section('pageContents')
    @include('common.menu-header', ['title' => $menuTitle])

    @if(!is_null($info))
        <div class="mx-4 fs-5" style="height:30px;">{{ $info->raceInfo . " " . mb_convert_kana($info->raceGrade, "n") }}</div>
        <div class="row m-2" style="height:calc(100vh - 110px)">
            <div class="col-3 h-100">
                <div class="fs-5" style="height:35px;">コメント</div>
                <div style="height:calc(100% - 50px); overflow-y:scroll;">
                    <div>レース</div>
                    <textarea class="w-100" rows="3" onChange="commentChange(this, true)">{{$info->raceComment}}</textarea>
                    @foreach($info->horseArray as $h)
                        <div class="waku{{$h->waku}}">
                            <div class="d-inline-block">{{$h->umaban}}</div>
                            <div class="d-inline-block">{{$h->name}}</div>
                        </div>
                        <textarea class="w-100" rows="3" data-horse-id="{{$h->horseId}}" onChange="commentChange(this)"
                        >{{$h->userComment}}</textarea>
                    @endforeach
                </div>
            </div>

            <div class="col-4 h-100">
                <div class="fs-5" style="height:35px;">競走馬
                    <div class="float-end mt-auto mb-0">
                        <a href="#" class="btn-tool btn-course" data-bs-toggle="modal" data-bs-target="#courseModal" title="コース"></a>
                        <a href="#" class="btn-tool btn-door" onclick="allOpenClick(this)" title="全て開く"></a>
                    </div>
                </div>

                <div class="w-100" style="height:calc(100% - 50px); overflow-y:scroll;">
                    <table class="w-100 tbl-detail">
                        <thead class="fs12 text-center">
                            <th class="py-1" style="width:35px;">枠</th>
                            <th class="py-1" style="width:35px;">馬番</th>
                            <th class="py-1" style="width:50px;">印</th>
                            <th class="py-1" style="width:auto;">馬名</th>
                        </thead>
                        <tbody>
                        @foreach($info->horseArray as $h)
                            <tr class="horse-info fs12 {{($h->isCancel) ? 'deselect' : 'normal'}}">
                                <td class="py-2 text-center waku{{$h->waku}}">{{ $h->waku }}</td>
                                <td class="py-2 text-center">{{ $h->umaban }}</td>
                            @if($h->isCancel)
                                <td class="text-center" data-index="{{$loop->index}}">除外</td>
                            @else
                                <td class="text-center fs-4 mark-cell" data-index="{{$loop->index}}" onclick="markCellClick(this)"></td>
                            @endif
                                <td class="px-1" @if(!$h->isCancel) onclick="toggleRow(this)" @endif >
                                    <div class="d-inline-block w-50 fs16">{{$h->name}}</div>
                                    <div class="d-inline-block w-auto">{{$h->age}}</div>
                                    <div class="d-inline-block w-50">
                                        <span>{{$h->jockey}}</span>
                                        <span>({{$h->kinryo}})</span>
                                    </div>
                                    <div class="d-inline-block w-auto">
                                        {{$h->recode . "  勝" . $h->winRate . "  複勝" . $h->podiumRate . "%"}}
                                    </div>
                                    @if(count($h->recodeArray) > 0)
                                        <div class="d-inline-block w-auto">
                                            {{"逃げ" . $h->nigePct . "%　"}}
                                            {{"先行" . $h->senkoPct . "%　"}}
                                            {{"差し" . $h->sashiPct . "%　"}}
                                            {{"追込" . $h->oikomiPct . "%"}}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @if(!$h->isCancel)
                            <tr class="row-hidden">
                                <td colspan="4" class="p-1" style="background-color:#dee2e6;">
                                    @include('horse-history-table', ['horse' => $h])
                                </td>
                            </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-5 h-100">
                <div class="fs-5" style="height:35px;">分析</div>
                <div style="height:calc(100% - 50px); overflow-y:scroll;">

                    <table class="tbl-analysis fs12 w-100">
                        <thead class="text-center">
                            <tr>
                                <th style="width:35px;" >No</th>
                                <th style="width:140px;">実績</th>
                                <th style="width:100px;" >距離別</th>
                                <th style="width:100px;" >馬場別</th>
                                <th style="width:auto;" >血統</th>
                            </tr>
                        </thead>
                        <tbody class="fs12">
                        @foreach($info->horseArray as $h)
                            <tr class="horse-info {{($h->isCancel) ? 'deselect' : 'normal'}}">
                                <td class="text-center waku{{$h->waku}} fs14">{{ $h->umaban }}</td>
                                <td class="text-start align-top">
                                    <div>間隔：{{$h->historySummary->intervalDay}}日</div>
                                    <div>ランク：{{$h->historySummary->highestRank}}</div>
                                    <div>タイプ：{{$h->typeString}}</div>
                                    <div>距離：{{$h->distanceString}}</div>
                                </td>
                                <td class="text-start align-top">
                                    @if(count($h->historySummary->distanceScores) > 0)
                                        @foreach($h->historySummary->distanceScores as $item)
                                            <div>{{$item['key'] . "m　　" . $item['value'] . "点"}}</div>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-start align-top">
                                    @if(count($h->historySummary->trackScores) > 0)
                                        @foreach($h->historySummary->trackScores as $item)
                                            <div>{{$item['key'] . "　　" . $item['value'] . "点"}}</div>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-start align-top">
                                    <div>
                                        <div class="d-inline-block text-end" style="width:40px;">父：</div>
                                        <div class="d-inline-block" style="width:110px;">{{$h->dad->name}}</div>
                                        <div class="d-inline-block" style="width:100px;">{{$h->dad->typeString}}</div>
                                        <div class="d-inline-block" style="width:100px;">{{$h->dad->distanceString}}</div>
                                    </div>
                                    <div>
                                        <div class="d-inline-block text-end" style="width:40px;">母父：</div>
                                        <div class="d-inline-block" style="width:110px;">{{$h->mamSohu->name}}</div>
                                        <div class="d-inline-block" style="width:100px;">{{$h->mamSohu->typeString}}</div>
                                        <div class="d-inline-block" style="width:100px;">{{$h->mamSohu->distanceString}}</div>
                                    </div>
                                    <div>
                                        <div class="d-inline-block text-end" style="width:40px;">母：</div>
                                        <div class="d-inline-block" style="width:110px;">{{$h->mam->name}}</div>
                                        <div class="d-inline-block" style="width:100px;">{{$h->mam->typeString}}</div>
                                        <div class="d-inline-block" style="width:100px;">{{$h->mam->distanceString}}</div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="courseModalLabel">コース情報</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img src="{{ $info->courseImage }}" />
                        <div className="p-2">{{ $info->courseMemo }}</div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="p-4 fs-4">
            <span>対象のデータが存在しません</span>
        </div>
    @endif

    <input type="hidden" id="raceId" value="{{$raceid}}" />
    <input type="hidden" id="urlScoringRace" value="{{route('detail.scoring', $raceid)}}" />
    <input type="hidden" id="urlComment" value="{{route('detail.comment')}}" />
@endsection


@section('pageJs')
<script type="text/javascript">
    const COOKIE_AGE = String(60 * 60 * 24 * 3);

    window.onload = function() {
        let raceId = document.getElementById("raceId").value;

        const markCells = document.getElementsByClassName("mark-cell");
        for(let i=0; i<markCells.length; i++) {
            markCells[i].innerText = getCookie("mark" + raceId + markCells[i].dataset.index);
            if(markCells[i].innerText == "消"){
                let parentRow = markCells[i].parentElement;
                parentRow.className = parentRow.className.replace("normal", "delete");
            }
        }
    }

    function toggleRow(e){
        const targetRow = e.parentElement.nextElementSibling;
        if(targetRow.className.indexOf('row-hidden') >= 0){
            targetRow.className = targetRow.className.replace("row-hidden", "row-visible");
        }else{
            targetRow.className = targetRow.className.replace("row-visible", "row-hidden");
        }
    }

    function markCellClick(cell) {
        const markArr = ["", "◎", "〇", "▲", "△", "☆", "消"];
        let currentMark = cell.innerText;
        cell.innerText = "";
        for(let i=0; i<markArr.length-1; i++) {
            if(currentMark == markArr[i]){
                cell.innerText = markArr[i+1];
                break;
            }
        }
        
        // マーク保存
        let raceId = document.getElementById("raceId").value;
        setCookie("mark" + raceId + cell.dataset.index, cell.innerText, COOKIE_AGE);

        // 背景制御
        const parentRow = cell.parentElement;
        if(cell.innerText == "消"){
            parentRow.className = parentRow.className.replace("normal", "delete");
        }else{
            parentRow.className = parentRow.className.replace("delete", "normal");
        }
    }

    function commentChange(comment, isRace=false) {
        const url = document.getElementById("urlComment").value;
        const addHeader = {'Content-Type': 'application/json'};
        let bodyData = null;
        if(isRace) {
            bodyData = JSON.stringify({
                race_id : document.getElementById("raceId").value,
                comment : comment.value
            });
        }else{
            bodyData = JSON.stringify({
                horse_id : comment.dataset.horseId,
                comment : comment.value
            });
        }
        asyncPost(url, addHeader, bodyData, () => {});
    }

    function bloodClick(e) {
        const tblParents = document.getElementsByClassName("tbl-horse-parent");
        let isOn = (e.className.indexOf('blood-off') >= 0);
        if(isOn){
            e.className = e.className.replace("blood-off", "blood-on");
        }else{
            e.className = e.className.replace("blood-on", "blood-off");
        }
        for(let i=0; i<tblParents.length; i++) {
            tblParents[i].style = isOn ? "display : table;" : "display : none;";
        }
    }

    function allOpenClick(e) {
        const rows = document.getElementsByClassName("row-hidden");
        for(let i=rows.length-1; i>=0; i--) {
            rows[i].className = rows[i].className.replace("row-hidden", "row-visible");
        }
    }

    async function scoringClick() {
        try {
            const url = document.getElementById("urlScoringRace").value;
            const addHeader = {'Content-Type': 'application/json'};
            asyncPost(url, addHeader, null, async(response) => {
                const json = await response.json();
                console.log(json);

                let strScore = "";
                for(let i=0; i<json.length; i++) {
                    let td = document.getElementsByClassName("score" + json[i].id);
                    td[0].innerText = json[i].score + "点";
                }
            });
        } catch (error) {
            console.log(error);
        }
    }
</script>
@endsection


@section('pageCss')
<style>
    .tbl-detail {
        border-collapse: separate;
        border-spacing: 0;
        border-bottom: solid 1px gray;
        border-left: solid 1px gray;
    }
    .tbl-detail > thead {
        background-color: #f4f2ec;
        position: sticky;
        top: 0;
        height: 30px;
        z-index: 10;
    }
    .tbl-detail > tbody {
        position: sticky;
        top: 30px;
    }
    .tbl-detail tr {
        border-top: solid 1px gray;
    }
    .tbl-detail tr.horse-info {
        position: sticky;
        top: inherit;
    }
    .tbl-detail tr.horse-info td:not(:first-child) {
        background-color: white;
    }
    .tbl-detail tr.horse-info.opend td {
        border-bottom: solid 1px gray;
    }
    .tbl-detail tr.row-hidden {
        display: none;
    }
    .tbl-detail tr.horse-info:has(+ tr.row-visible) td {
        border-bottom: solid 1px gray;
    }

    .tbl-detail tr.delete td,
    .tbl-detail tr.delete input {
        background-color: #d0d0d0 !important;
    }
    .tbl-detail tr.deselect td:not(:first-child){
        background-color: #a9a9a9;
    }
    .tbl-detail th,
    .tbl-detail td {
        border-top: solid 1px gray;
        border-right: solid 1px gray;
    }

    a.btn-tool {
        display: inline-block;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
    }
    a.btn-blood {
        width: 64px;
        height: 29px;
    }
    a.blood-off {
        background-image: url(/img/bloodline-off.png);
    }
    a.blood-on {
        background-image: url(/img/bloodline-on.png);
    }
    a.btn-door {
        width: 29px;
        height: 29px;
        margin-right: 15px;
        background-image: url(/img/door.png);
    }
    a.btn-course {
        width: 45px;
        height: 29px;
        margin-right: 15px;
        background-image: url(/img/course.png);
    }

    .tbl-analysis {
        border-collapse: separate;
        border-spacing: 0;
        border-bottom: solid 1px gray;
        border-left: solid 1px gray;
    }
    .tbl-analysis > thead {
        background-color: #f4f2ec;
        position: sticky;
        top: 0;
        height: 30px;
        z-index: 10;
    }
    .tbl-analysis > tbody {
        position: sticky;
        top: 30px;
    }
    .tbl-analysis th,
    .tbl-analysis td {
        border-top: solid 1px gray;
        border-right: solid 1px gray;
        padding: 3px;
    }
    .tbl-analysis th{
        border-bottom: solid 1px gray;
    }
    .tbl-analysis tbody tr:first-child td{
        border-top: none;
    }

</style>
@endsection
