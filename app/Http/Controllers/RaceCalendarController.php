<?php
namespace App\Http\Controllers;
use App\Netkeiba\Service\ScheduleService;
use App\Netkeiba\Service\RaceInfoService;
use App\Netkeiba\Service\HorseInfoService;
use App\Models\RaceData;
use Illuminate\Http\Request;


/**
 * レースカレンダー
 */
class RaceCalendarController extends Controller
{
    /**
     * 初期表示
     */
    function index(Request $request)
    {
        $viewData = [];

        $queryParam = $request->query();
        if(isset($queryParam["selYm"])){
            $dtTarget = date("Y-m-d", strtotime($queryParam["selYm"] . "01"));
        }else{
            $dtTarget = date("Y-m-d");
        }

        // カレンダー情報を作成
        $viewData["targetDate"] = $dtTarget;
        $viewData["prevMonth"] = date('Ym', strtotime(substr($dtTarget, 0, 8) . '01 -1 Month'));
        $viewData["nextMonth"] = date('Ym', strtotime(substr($dtTarget, 0, 8) . '01 +1 Month'));

        // スケジュールデータを取得
        $srvSched = new ScheduleService();
        $scheduleArray = $srvSched->GetMonthSchedule($dtTarget);

        $loop = $scheduleArray[0]->dayOfWeek;
        if($loop > 0){
            for($i=0; $i<$loop; $i++) {
                array_unshift($scheduleArray, null);
            }
        }
        $loop = 6 - ($scheduleArray[array_key_last($scheduleArray)]->dayOfWeek);
        if($loop > 0){
            for($i=0; $i<$loop; $i++) {
                $scheduleArray[] = null;
            }
        }
        $viewData["scheduleArray"] = $scheduleArray;

        return view("race-calendar", $viewData);
    }

    /**
     * レース情報を取得
     */
    function getRaceInfo(Request $request)
    {
        $srvSched = new ScheduleService();
        $srvRace = new RaceInfoService();

        $input = $request->all();
        $selDate = $input["sel_date"];
        $raceId = $input["race_id"];

        // 開催情報の取得
        $kaisai = $srvSched->getKaisaiByRaceId($raceId);
        $kaisai->raceCount;

        $rtnData["title"] = date("n月j日 ", strtotime($selDate)) . $kaisai->babaName;

        for($i=1; $i<=$kaisai->raceCount; $i++){
            $searchId = $raceId . sprintf('%02d', $i);

            $raceData = $srvRace->GetRaceData($searchId);
            if ($raceData != null) {
                $rtnData["data"][] = array(
                    "isExists" => true,
                    "raceId" => $raceData->raceId,
                    "name" => $raceData->raceName,
                    "startingTime" => $raceData->startingTime,
                    "distance" => config("const.GROUND_SHORT_NAME")[$raceData->groundType] . $raceData->distance . "m",
                    "horseCount" => $raceData->horseCount . "頭",
                );
            }else{
                $rtnData["data"][] = array(
                    "isExists" => false,
                    "raceId" => $searchId
                );
            }
        }

        $view = view('race-calendar-detail', $rtnData)->render();
        return response(["view" => $view], 200);
    }

    /**
     * スケジュールのデータをスクレイピング
     */
    function getScheduleData(Request $request)
    {
        $input = $request->all();
        $selDate = date("Y-m-d", strtotime($input["sel_date"]));

        $srvSched = new ScheduleService();
        $srvSched->fetch($selDate);

        return redirect()->route("calendar.index", ["selYm" => date("Ym", strtotime($selDate))]);
    }

    /**
     * スクレイピング処理をキック
     */
    function executeScrapingRace(Request $request)
    {
        $input = $request->all();
        $raceId = $input["race_id"];

        // レース情報を取得(スクレイピング処理をキック)
        $srvRace = new RaceInfoService();
        $srvRace->fetch($raceId);

        $raceData = $srvRace->GetRaceData($raceId);
        if($raceData == null) {
            $raceData = new RaceData();
        }

        // 返却データ
        $rtnData["data"] = array(
            "raceNo" => intval(substr($raceData->raceId, -2)),
            "raceId" => $raceData->raceId,
            "startingTime" => $raceData->startingTime,
            "distance" => config("const.GROUND_SHORT_NAME")[$raceData->groundType] . $raceData->distance . "m",
            "horseCount" => $raceData->horseCount . "頭",
            "name" => $raceData->raceName,
            "detailUrl" => route('detail.index', $raceData->raceId)
        );

        return response($rtnData, 200);
    }

}
