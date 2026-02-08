<?php
namespace App\Http\Controllers\api;
use App\Netkeiba\Analysis\RaceHistoryAnalyzer;
use App\Netkeiba\Service\ScheduleService;
use App\Netkeiba\Service\RaceInfoService;
use App\Netkeiba\Service\HorseInfoService;
use App\Netkeiba\ClsHorseMemo;
use App\Netkeiba\ClsViewRaceData;
use App\Netkeiba\ClsScoringCentral;
use App\Netkeiba\ClsScoringRegion;
use App\Util\MobileLoginUtil;
use Illuminate\Http\Request;


/**
 * 競馬情報API
 */
class HorseRaceApi
{
    /**
     * ログインチェック
     */
    function loginCheck(Request $request)
    {
        $statusCode = 401;
        $response = null;
        if(MobileLoginUtil::isLogin($request->cookie("facile-token"))){
            $loginUser = MobileLoginUtil::getLoginUserInfo($request->cookie("facile-token"));
            $response = json_encode($loginUser, JSON_UNESCAPED_UNICODE);
            $statusCode = 200;
        }
        return response($response, $statusCode);
    }

    /**
     * ログイン処理
     */
    function login(Request $request)
    {
        $input = $request->all();
        $token = MobileLoginUtil::login($input["user_id"], $input["password"]);
        if($token != "") {
            $loginUser = MobileLoginUtil::getLoginUserInfo($token);
            $response = json_encode($loginUser, JSON_UNESCAPED_UNICODE);
            return response($response, 200);
        }
        return response(null, 401);
    }


    /**
     * スケジュールの取得
     */
    function getSchdule(Request $request)
    {
        $input = $request->all();
        $rtnArr[] = ["id" => "", "name" => ""];

        $srvSched = new ScheduleService();
        $schedule = $srvSched->GetSchedule($input["sel_date"])[0];

        $scheArr = array_merge($schedule->centralArray, $schedule->regionArray);
        if(count($scheArr) == 0) {
            return $rtnArr;
        }

        $srvRace = new RaceInfoService();
        foreach($scheArr as $kaisai) {
            for($i=1; $i<=$kaisai->raceCount; $i++) {
                $raceId = $kaisai->id . sprintf('%02d', $i);
                $raceData = $srvRace->GetRaceData($raceId);
                if ($raceData != null) {
                    $name = config("const.BAMEI_NAME")[$kaisai->babaCode] . " " . $i . "R";
                    $name .= " " . $raceData->startingTime;
                    $name .= " " . $raceData->raceName;
                    $rtnArr[] = ["id" => $raceId, "name" => $name];
                }
            }
        }

        return response($rtnArr, 200);
    }

    /**
     * レース情報の取得
     */
    function getRaceData(Request $request)
    {
        // レースIDを作成
        $input = $request->all();
        $raceId = $input["race_id"];

        // 取得
        $statusCode = 204;
        $rtnData = [];

        // ログインユーザ
        $loginUserNo = MobileLoginUtil::getLoginUserInfo($request->cookie("facile-token"))->userNo;

        // 開催日を取得
        $srvSched = new ScheduleService();
        $kaisaiDate = $srvSched->GetScheduleDay($raceId);

        $clsViewRaceData = new ClsViewRaceData();
        $viewRace = $clsViewRaceData->GetViewData($raceId, $kaisaiDate, $loginUserNo);
        if($viewRace->raceId != null) {
            // 履歴を10件に削除
            foreach($viewRace->horseArray as $horse){
                if(count($horse->recodeArray) > 0){
                    $horse->recodeArray = array_splice($horse->recodeArray, 0, 10);
                }
            }

            $rtnData = json_encode($viewRace, JSON_UNESCAPED_UNICODE);
            $statusCode = 200;
        }

        // jsonで返却
        return response($rtnData, $statusCode);
    }


    /**
     * 日付から開催馬場を取得
     */
    function getKaisaiBaba(Request $request)
    {
        $input = $request->all();

        $srvSched = new ScheduleService();
        $schedule = $srvSched->GetSchedule($input["sel_date"])[0];
        $scheArr = array_merge($schedule->centralArray, $schedule->regionArray);

        $rtnObj = [];
        if(count($scheArr) > 0){
            foreach($scheArr as $sche){
                $rtnObj[] = ["key" => $sche->id, "value" => $sche->name];
            }
        }
        return response($rtnObj, 200);
    }

    /**
     * 開催馬場情報をスクレイピング
     */
    function scrapingKaisaiBaba(Request $request)
    {
        $input = $request->all();
        $selDate = date("Y-m-d", strtotime($input["sel_date"]));

        $srvSched = new ScheduleService();
        $srvSched->fetch($selDate);

        $schedule = $srvSched->GetSchedule($selDate)[0];
        $scheArr = array_merge($schedule->centralArray, $schedule->regionArray);

        $rtnObj = [];
        if(count($scheArr) > 0){
            foreach($scheArr as $kaisai){
                $rtnObj[] = ["key" => $kaisai->id, "value" => $kaisai->name];
            }
        }
        return response($rtnObj, 200);
    }

    /**
     * 日付と開催馬場からレース取得情報を取得
     */
    function getRaceList(Request $request)
    {
        $input = $request->all();

        $selRaceId = $input["race_id"];
        $selDate = $input["sel_date"];

        $srvSched = new ScheduleService();
        $schedule = $srvSched->GetSchedule($selDate)[0];
        $scheArr = array_merge($schedule->centralArray, $schedule->regionArray);

        // レースリストを取得
        $rtnObj = [];
        $srvRace = new RaceInfoService();
        if($selRaceId != "" && count($scheArr) > 0){
            foreach($scheArr as $kaisai){
                if($kaisai->id == $selRaceId){
                    for($i=1; $i<=$kaisai->raceCount; $i++) {
                        $raceId = $kaisai->id . substr("00" . $i, -2);
                        $raceData = [
                            "id" => $raceId,
                            "name" => "",
                            "status" => 0
                        ];
                        // 取得済であるか？
                        $race = $srvRace->GetRaceData($raceId);
                        if ($race != null) {
                            $raceData["name"] = $race->raceName;
                            $raceData["status"] = 1;
                        }
                        $rtnObj[] = $raceData;
                    }
                }
            }
        }

        return response($rtnObj, 200);
    }

    /**
     * スクレイピング処理をキック
     */
    function scrapingRaceData(Request $request)
    {
        $input = $request->all();
        $raceId = $input["race_id"];
        $response = ["id" => $raceId, "name" => "", "status" => 0];

        $srvHorse = new HorseInfoService();

        // スクレイピング処理をキック
        $srvRace = new RaceInfoService();
        $srvRace->fetch($raceId);

        $raceData = $srvRace->GetRaceData($raceId);
        if($raceData != null) {
            $response["name"] = $raceData->raceName;
            $response["status"] = 1;
        }

        return response($response, 200);
    }


    /**
     * コメントの書き込み
     */
    function writeComment(Request $request) {
        // ログインNoを取得
        $loginUserNo = MobileLoginUtil::getLoginUserInfo($request->cookie("facile-token"))->userNo;

        $input = $request->all();
        if(!array_key_exists("race_id", $input) &&
            !array_key_exists("horse_id", $input) &&
            !array_key_exists("comment", $input)) {
            return response([], 400);
        }
        $raceId = $input["race_id"];
        $horseId = $input["horse_id"];
        $comment = is_null($input["comment"]) ? "" : $input["comment"];

        $clsMemo = new ClsHorseMemo();
        if($raceId != "" && $horseId == "") {
            $clsMemo->SetRaceMemo($loginUserNo, $raceId, $comment);
        }
        if($raceId == "" && $horseId != "") {
            $clsMemo->SetMemo($loginUserNo, $horseId, $comment);
        }

        return response([], 200);
    }

    /**
     * プロファイルの更新
     */
    function updateProfile(Request $request)
    {
        $input = $request->all();

        // ログインユーザ情報取得
        $loginUser = MobileLoginUtil::getLoginUserInfo($request->cookie("facile-token"));

        // 内容セット
        if(array_key_exists("use_storage", $input)) {
            $loginUser->useStorage = $input["use_storage"];
        }

        // プロファイルを更新
        MobileLoginUtil::updateProfile($loginUser);

        return response([], 200);
    }

}
