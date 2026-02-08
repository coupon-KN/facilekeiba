<?php
namespace App\Netkeiba;
use App\Netkeiba\Analysis\RaceHistoryAnalyzer;
use App\Netkeiba\Service\RaceInfoService;
use App\Netkeiba\Service\HorseInfoService;
use App\Models\RaceData;
use App\Models\ViewRaceData;
use App\Models\ViewHorseData;
use App\Models\ViewParentData;
use Illuminate\Support\Facades\Storage;


/**
 * 表示用データ管理クラス
 */
class ClsViewRaceData
{
    /**
     * レースIDのみで表示用データ取得
     */
    public function GetViewDataByRaceId($raceId, $kaisaiDate): ViewRaceData
    {
        return $this->GetViewData($raceId, $kaisaiDate, "");
    }

    /**
     * 表示用レースデータ取得
     */
    public function GetViewData($raceId, $kaisaiDate, $loginUserNo): ViewRaceData
    {
        $rtnData = new ViewRaceData();
        $srvRace = new RaceInfoService();
        $srvHorse = new HorseInfoService();
        $clsMemo = new ClsHorseMemo();
        $analyzer = new RaceHistoryAnalyzer();

        $race = $srvRace->GetRaceData($raceId);
        if ($race != null) {
            $rtnData = new ViewRaceData();

            // レース情報を格納
            $rtnData->raceId       = $race->raceId;
            $rtnData->raceNo       = $race->GetRaceNo();
            $rtnData->babaCode     = $race->GetBabaCode();
            $rtnData->babaName     = $race->GetBabaName();
            $rtnData->kaisai       = $race->kaisai;
            $rtnData->raceName     = $race->raceName;
            $rtnData->startingTime = $race->startingTime;
            $rtnData->groundType   = $race->groundType;
            $rtnData->distance     = $race->distance;
            $rtnData->direction    = $race->direction;
            $rtnData->raceGrade    = $race->raceGrade;
            $rtnData->horseCount   = $race->horseCount;
            $rtnData->raceInfo     = $race->raceInfo;
            $rtnData->courseMemo   = $this->getCourseMemo($race);
            $rtnData->courseImage  = $this->getCourseImage($race);
            $rtnData->raceComment  = $clsMemo->GetRaceMemo($loginUserNo, $race->raceId);

            // 競走馬情報を格納
            foreach($race->shutsubaArray as $shutsuba) {
                $viewHorse = new ViewHorseData();
                // 出走情報
                $viewHorse->horseId  = $shutsuba->horseId;
                $viewHorse->waku     = $shutsuba->waku;
                $viewHorse->umaban   = $shutsuba->umaban;
                $viewHorse->name     = $shutsuba->name;
                $viewHorse->age      = $shutsuba->age;
                $viewHorse->kinryo   = $shutsuba->kinryo;
                $viewHorse->jockey   = $shutsuba->jockey;
                $viewHorse->isCancel = $shutsuba->isCancel;

                // 競走馬情報
                $horse = $srvHorse->GetHorseData($shutsuba->horseId);
                $viewHorse->recode        = $horse->recode;
                $viewHorse->totalPrize    = $horse->totalPrize;
                $viewHorse->winRate       = $horse->winRate;
                $viewHorse->podiumRate    = $horse->podiumRate;
                $viewHorse->nigePct       = $horse->nigePct;
                $viewHorse->senkoPct      = $horse->senkoPct;
                $viewHorse->sashiPct      = $horse->sashiPct;
                $viewHorse->oikomiPct     = $horse->oikomiPct;
                $viewHorse->userComment   = $clsMemo->GetMemo($loginUserNo, $shutsuba->horseId);
                $viewHorse->dad           = $this->getParent($horse->dadId);
                $viewHorse->dadSohu       = $this->getParent($horse->dadSohuId);
                $viewHorse->dadSobo       = $this->getParent($horse->dadSoboId);
                $viewHorse->mam           = $this->getParent($horse->mamId);
                $viewHorse->mamSohu       = $this->getParent($horse->mamSohuId);
                $viewHorse->mamSobo       = $this->getParent($horse->mamSoboId);
                $viewHorse->recodeArray   = $horse->recodeArray;

                $viewHorse->speed         = $horse->analysisScore->speed;
                $viewHorse->power         = $horse->analysisScore->power;
                $viewHorse->stamina       = $horse->analysisScore->stamina;
                $viewHorse->sprint        = $horse->analysisScore->sprint;
                $viewHorse->mile          = $horse->analysisScore->mile;
                $viewHorse->middle        = $horse->analysisScore->middle;
                $viewHorse->long          = $horse->analysisScore->long;
                $viewHorse->typeString    = $horse->analysisScore->getTypeString();
                $viewHorse->distanceString= $horse->analysisScore->getDistanceString();
            
                // 集計
                $viewHorse->historySummary = $analyzer->summary($viewHorse, $kaisaiDate);

                $rtnData->horseArray[] = $viewHorse;
            }
        }

        return $rtnData;
    }

    private function getCourseMemo(RaceData $race) {
        $babaCode = $race->GetBabaCode();
        $typeChar = ($race->groundType == 1) ? "s" : (($race->groundType == 2) ? "d" : "");

        if(array_key_exists($babaCode, config("const.BABA_MEMO"))){
            $memoKey = $typeChar . $race->distance;
            if(array_key_exists($memoKey, config("const.BABA_MEMO")[$babaCode])){
                return config("const.BABA_MEMO")[$babaCode][$memoKey];
            }
        }
        return "";
    }

    private function getCourseImage(RaceData $race) {
        $babaCode = $race->GetBabaCode();
        $typeChar = ($race->groundType == 1) ? "s" : (($race->groundType == 2) ? "d" : "");
        return  sprintf("/img/course/%s/%s%d.gif", $babaCode, $typeChar, $race->distance);
    }

    private function getParent($horseId): ViewParentData {
        $srvHorse = new HorseInfoService();
        $horse = $srvHorse->GetHorseData($horseId);

        if ($horse != null) {
            $parent = new ViewParentData();
            $parent->horseId       = $horse->horseId;
            $parent->name          = $horse->name;
            $parent->recode        = $horse->recode;
            $parent->winRate       = $horse->winRate;
            $parent->podiumRate    = $horse->podiumRate;
            $parent->speed         = $horse->analysisScore->speed;
            $parent->power         = $horse->analysisScore->power;
            $parent->stamina       = $horse->analysisScore->stamina;
            $parent->sprint        = $horse->analysisScore->sprint;
            $parent->mile          = $horse->analysisScore->mile;
            $parent->middle        = $horse->analysisScore->middle;
            $parent->long          = $horse->analysisScore->long;
            $parent->typeString    = $horse->analysisScore->getTypeString();
            $parent->distanceString= $horse->analysisScore->getDistanceString();
            return $parent;
        }

        return new ViewParentData();
    }

}
