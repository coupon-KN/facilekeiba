@if(count($horse->recodeArray) == 0)
    <span class="tbl-history">競走成績がありません</span>
@else
    <div class="horse-history-cell bg-white">
        @foreach($horse->recodeArray as $rec)
        <div class="row m-0 border fs12 rank{{$rec->rankNo}}">
            <div class="col-4 p-1">
                <div>{{$rec->date}}</div>
                <div>{{$rec->baba}}{{($rec->raceGrade != "" ? "  " . $rec->raceGrade : "")}}</div>
                <div>
                    @if(empty($rec->raceUrl))
                        <span>{{ $rec->raceName }}</span>
                    @else
                        <a href="{{$rec->raceUrl}}" target="_blank">{{ $rec->raceName }}</a>
                    @endif
                </div>
                <div>{{$rec->groundShortName . $rec->distance . " " . $rec->condition}}</div>
            </div>
            <div class="col-1 p-1 fs-4">
                <div>{{$rec->rankNo}}</div>
            </div>
            <div class="col-7 p-1">
                <div>
                    <span class='w-50 d-inline-block'>{{$rec->jockey}}</span>
                    <span class='w-25 d-inline-block'>{{$rec->kinryo}}</span>
                    <span class='w-25 d-inline-block'>{{$rec->weight}}</span>
                </div>
                <div>
                    <span class='w-100 d-inline-block'>{{$rec->horseCount . "頭　" . $rec->umaban . "番　" . $rec->ninki . "人気(" . $rec->odds . ")"}}</span>
                </div>
                <div>
                    <span class='w-100 d-inline-block'>{{$rec->time . " (" . $rec->difference . ")　" . $rec->firstPace . " - " . $rec->latterPace . "　3F：" . $rec->agari600m . ($rec->agariRank<=3 ? "(" . $rec->agariRank . "位)" : "")}}</span>
                </div>
                <div>{{$rec->pointTime}}</div>
                <div>{{$rec->winHorse}}</div>
            </div>
        </div>
        @endforeach
    </div>
@endif

{{--
<table class="tbl-horse-parent fs12 w-100" style="display:none;">
    <colgroup>
        <col class="w-25">
        <col class="w-25">
        <col class="w-25">
        <col class="w-25">
    </colgroup>
    <tr>
        <td rowspan="2" class="p-1 bg-dad">{{$horse->dad->name}}</td>
        <td rowspan="2" class="p-1 bg-dad">{{$horse->dad->recode . " 勝" . $horse->dad->winRate . "% 複" . $horse->dad->podiumRate . "%"}}</td>
        <td class="p-1 bg-dad">{{$horse->dadSohu->name}}</td>
        <td class="p-1 bg-dad">{{$horse->dadSohu->recode . " 勝" . $horse->dadSohu->winRate . "% 連" . $horse->dadSohu->podiumRate . "%"}}</td>
    </tr>
    <tr>
        <td class="p-1 bg-mam">{{$horse->dadSobo->name}}</td>
        <td class="p-1 bg-mam">{{$horse->dadSobo->recode . " 勝" . $horse->dadSobo->winRate . "% 連" . $horse->dadSobo->podiumRate . "%"}}</td>
    </tr>
    <tr>
        <td rowspan="2" class="p-1 bg-mam">{{$horse->mam->name}}</td>
        <td rowspan="2" class="p-1 bg-mam">{{$horse->mam->recode . " 勝" . $horse->mam->winRate . "% 連" . $horse->mam->podiumRate . "%"}}</td>
        <td class="p-1 bg-dad">{{$horse->mamSohu->name}}</td>
        <td class="p-1 bg-dad">{{$horse->mamSohu->recode . " 勝" . $horse->mamSohu->winRate . "% 連" . $horse->mamSohu->podiumRate . "%"}}</td>
    </tr>
    <tr>
        <td class="p-1 bg-mam">{{$horse->mamSobo->name}}</td>
        <td class="p-1 bg-mam">{{$horse->mamSobo->recode . " 勝" . $horse->mamSobo->winRate . "% 連" . $horse->mamSobo->podiumRate . "%"}}</td>
    </tr>
</table>
--}}
