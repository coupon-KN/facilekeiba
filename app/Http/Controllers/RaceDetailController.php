<?php
namespace App\Http\Controllers;
use App\Netkeiba\ClsViewRaceData;
use App\Netkeiba\ClsHorseMemo;
use App\Netkeiba\ClsScoringCentral;
use App\Netkeiba\ClsScoringRegion;
use App\Netkeiba\Service\ScheduleService;
use Illuminate\Http\Request;


/**
 * レース詳細
 */
class RaceDetailController extends Controller
{
    /**
     * 初期表示
     */
    function index($raceId)
    {
        $viewData["raceid"] = $raceId;
        $viewData["pageTitle"] = "レース詳細";
        $viewData["menuTitle"] = "レース詳細";
        $viewData["info"] = null;
        $viewData["horseArray"] = null;
        $viewData["summary"] = null;

        // ログインユーザ
        $loginUserNo = "";
        if(session()->exists(config("const.SESSION_LOGIN_USER_NO"))){
            $loginUserNo = session(config("const.SESSION_LOGIN_USER_NO"));
        }

        // 開催日を取得
        $srvSched = new ScheduleService();
        $kaisaiDate = $srvSched->GetScheduleDay($raceId);

        // レース情報取得
        $clsViewRaceData = new ClsViewRaceData();
        $viewRace = $clsViewRaceData->GetViewData($raceId, $kaisaiDate, $loginUserNo);
        if($viewRace != null) {
            $viewData["info"] = $viewRace;
            $title = $viewRace->babaName . $viewRace->raceNo . "R " . $viewRace->raceName;
            $viewData["pageTitle"] = $title;
            $viewData["menuTitle"] = $title;
            $viewData["centralFlg"] = (in_array($viewRace->babaCode, config("const.CENTRAL_BAMEI_CODE")));

            // 履歴を10件に削除
            foreach($viewRace->horseArray as $horse){
                if(count($horse->recodeArray) > 0){
                    $horse->recodeArray = array_splice($horse->recodeArray, 0, 10);
                }
            }
        }

        return view("race-detail", $viewData);
    }

    /**
     * コメントの書き込み
     */
    function writeComment(Request $request) {
        if(!session()->exists(config("const.SESSION_LOGIN_USER_NO"))){
            return;
        }
        $loginUserNo = session(config("const.SESSION_LOGIN_USER_NO"));

        $clsMemo = new ClsHorseMemo();

        $input = $request->all();
        if(array_key_exists("horse_id", $input) && array_key_exists("comment", $input))
        {
            $horseId = $input["horse_id"];
            $comment = is_null($input["comment"]) ? "" : $input["comment"];
            $clsMemo->SetMemo($loginUserNo, $horseId, $comment);
        }
        else if(array_key_exists("race_id", $input) && array_key_exists("comment", $input)){
            $raceId = $input["race_id"];
            $comment = is_null($input["comment"]) ? "" : $input["comment"];
            $clsMemo->SetRaceMemo($loginUserNo, $raceId, $comment);
        }
    }

}
