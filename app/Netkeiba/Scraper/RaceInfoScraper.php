<?php
namespace App\Netkeiba\Scraper;
use App\Models\RaceData;
use App\Models\ShutsubaData;
use Illuminate\Support\Facades\Storage;
use phpQuery;


/**
 * レース情報スクレイピングクラス
 */
class RaceInfoScraper
{
    /**
     * スクレイピング
     */
    public function Scraping($raceId) : RaceData
    {
        $rtnObj = null;

        $babaCode = substr($raceId, 4, 2);
        if(in_array($babaCode, config("const.CENTRAL_BAMEI_CODE"))){
            $rtnObj = $this->scrapingCentralRace($raceId);
        }else{
            $rtnObj = $this->scrapingRegionRace($raceId);
        }
        return $rtnObj;
    }

    /**
     * 中央競馬のレース情報をスクレイピング
     */
    private function scrapingCentralRace($raceId)
    {
        // netkeibaのサイトからhtml情報を取得
        $html = file_get_contents("https://race.netkeiba.com/race/shutuba.html?race_id=" . $raceId);
        if(!$html) return;

        $html = mb_convert_encoding($html, "UTF-8", "EUC-JP");

        $raceData = new RaceData();
        try {
            $doc = phpQuery::newDocument($html);
            $raceColumn01 = $doc->find("#page div.RaceColumn01")->getDocument();

            // レースID
            $raceData->raceId = $raceId;
            // 開催場
            $raceData->kaisai = $this->getCentralKaisaiName($raceId);
            // レース名
            $raceData->raceName = str_replace(PHP_EOL, "", trim($raceColumn01->find("h1.RaceName")->text()));
            // レースデータを配列へ
            $strWork = preg_replace('/　|\s+/', '', $raceColumn01->find("div.RaceData01")->text());
            $raceArr = explode("/", $strWork);
            // 発送時刻
            $raceData->startingTime = str_replace("発走", "", $raceArr[0]);
            // 種類
            $type = mb_substr(preg_replace('/　|\s+/', '', $raceArr[1]), 0, 1);
            if($type == "芝"){
                $raceData->groundType = 1;
            }elseif($type == "ダ"){
                $raceData->groundType = 2;
            }elseif($type == "障"){
                $raceData->groundType = 3;
            }
            // 距離
            preg_match('/[0-9]+m/', $raceArr[1], $match);
            $raceData->distance = intval(preg_replace('/[^0-9]/', '', $match[0]));
            // 向き
            if(false !== strpos($raceArr[1], "左")){
                $raceData->direction = 1;
            }elseif(false !== strpos($raceArr[1], "右")){
                $raceData->direction = 2;
            }
            // レース等級の取得
            $raceData->raceGrade = $this->getCentralRaceGrade($doc);

            // 頭数取得
            $raceInfo = $doc->find(".RaceColumn01 .RaceList_Item02 .RaceData02")->text();
            preg_match('/[0-9]+頭/', $raceInfo, $match);
            $raceData->horseCount = intval(preg_replace('/[^0-9]/', '', $match[0]));

            // 出馬情報を取得
            $raceTable = $doc->find("div.RaceColumn02 div.RaceTableArea table")->getDocument();

            for($i=0; $i<$raceData->horseCount; $i++){
                $link = $raceTable->find("tr.HorseList:eq(" . $i . ") td.HorseInfo span.HorseName a")->attr("href");
                $title = $raceTable->find("tr.HorseList:eq(" . $i . ") td.HorseInfo span.HorseName a")->attr("title");
                $linkArr = explode("/", $link);

                $shutsuba = new ShutsubaData();
                // 馬ID
                $shutsuba->horseId = end($linkArr);
                // 枠番
                $shutsuba->waku = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(0) span")->text();
                // 馬番
                $shutsuba->umaban = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(1)")->text();
                // 名前
                $shutsuba->name = $title;
                // 年齢
                $shutsuba->age = $raceTable->find("tr.HorseList:eq(" . $i . ") td.Barei")->text();
                // 斤量
                $shutsuba->kinryo = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(5)")->text();
                // 騎手
                $shutsuba->jockey = $raceTable->find("tr.HorseList:eq(" . $i . ") td.Jockey a")->text();
                // 除外
                $shutsuba->isCancel = 0;
                $className = $raceTable->find("tr.HorseList:eq(" . $i . ")")->attr("class");
                if(false !== strpos($className, "Cancel")){
                    $shutsuba->isCancel = 1;
                }
                $raceData->shutsubaArray[] = $shutsuba;
            }

            // レース情報
            $raceData->raceInfo = $raceData->startingTime . "発走 ";
            $raceData->raceInfo .= config("const.GROUND_NAME")[$raceData->groundType];
            $raceData->raceInfo .= $raceData->distance . "m";
            $raceData->raceInfo .= "("  . config("const.DIRECTION_NAME")[$raceData->direction] . ") ";
            $raceData->raceInfo .= $raceData->horseCount . "頭";
        }
        catch(\Exception $err){
            return null;
        }

        return $raceData;
    }

    /**
     * レースIDから中央の開催名を取得
     * 年(4) + 場名(2) + 回数(2) + 日(2) + レースNo(2)
     */
    private function getCentralKaisaiName($raceId)
    {
        $rtnStr = intval(substr($raceId, 6, 2)) . "回";
        $rtnStr .= config("const.BAMEI_NAME")[substr($raceId, 4, 2)];
        $rtnStr .= intval(substr($raceId, 8, 2)) . "日目 ";
        $rtnStr .= intval(substr($raceId, 10, 2)) . "レース";
        return $rtnStr;
    }

    /**
     * 中央のレース等級を取得
     */
    private function getCentralRaceGrade($document)
    {
        $className = $document->find("#page div.RaceColumn01 .RaceName .Icon_GradeType")->attr("class");
        $textBody = trim($document->find("#page div.RaceColumn01 div.RaceData02")->text());
        $raceClass = explode("\n", $textBody)[4];

        $keys = array_keys(config("const.RACE_CLASS_RANK"));

        $garade1 = count($keys) - 1;
        $garade1 = (false !== strpos($className, "Icon_GradeType1") ) ? 0 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType2") ) ? 1 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType3") ) ? 2 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType15")) ? 3 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType5") ) ? 4 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType16")) ? 5 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType17")) ? 6 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType18")) ? 7 : $garade1;

        $garade1 = (false !== strpos($className, "Icon_GradeType10")) ? 0 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType11")) ? 1 : $garade1;
        $garade1 = (false !== strpos($className, "Icon_GradeType12")) ? 2 : $garade1;

        $garade2 = 0;
        foreach($keys as $val) {
            if(false !== strpos($val, $raceClass)) break;
            $garade2 ++;
        }

        return $keys[min($garade1, $garade2)];
    }

    /**
     * 地方競馬のレース情報をスクレイピング
     */
    private function scrapingRegionRace($raceId)
    {
        // netkeibaのサイトからhtml情報を取得
        $html = file_get_contents("https://nar.netkeiba.com/race/shutuba.html?race_id=" . $raceId);
        if(!$html){
            return;
        }

        $html = mb_convert_encoding($html, "UTF-8", "EUC-JP");

        $raceData = new RaceData();
        try {
            $doc = phpQuery::newDocument($html);
            $raceColumn01 = $doc->find("div.RaceColumn01")->getDocument();

            // レースID
            $raceData->raceId = $raceId;
            // レース名
            $raceData->raceName = str_replace("\n", "", trim($raceColumn01->find("div.RaceList_Item02 div.RaceName")->text()));
            // レースデータを配列へ
            $strWork = preg_replace('/　|\s+/', '', $raceColumn01->find("div.RaceData01")->text());
            $raceArr = explode("/", $strWork);
            // 発送時刻
            $raceData->startingTime = str_replace("発走", "", $raceArr[0]);
            // 種類
            $type = mb_substr(preg_replace('/　|\s+/', '', $raceArr[1]), 0, 1);
            if($type == "芝"){
                $raceData->groundType = 1;
            }elseif($type == "ダ"){
                $raceData->groundType = 2;
            }elseif($type == "障"){
                $raceData->groundType = 3;
            }
            // 距離
            $raceData->distance = intval(preg_replace('/[^0-9]/', '', $raceArr[1]));
            // 向き
            if(false !== strpos($raceArr[1], "左")){
                $raceData->direction = 1;
            }elseif(false !== strpos($raceArr[1], "右")){
                $raceData->direction = 2;
            }
        
            // 頭数取得
            $raceInfo = $doc->find(".RaceColumn01 .RaceList_Item02 .RaceData02")->text();
            preg_match('/[0-9]+頭/', $raceInfo, $match);
            $raceData->horseCount = intval(preg_replace('/[^0-9]/', '', $match[0]));

            // 出馬情報を取得
            $raceTable = $doc->find("div.RaceColumn02 div.Shutuba_HorseList table.ShutubaTable")->getDocument();

            // クラス
            $strRace = trim($doc->find("div.RaceColumn01 div.RaceData02")->text());
            $raceArr = explode("\n", $strRace);
            $raceData->raceGrade = $this->getRegionRaceGrade($raceArr[3]);
            
            for($i=0; $i<$raceData->horseCount; $i++){
                $link = $raceTable->find("tr.HorseList:eq(" . $i . ") td.HorseInfo span.HorseName a")->attr("href");
                $title = $raceTable->find("tr.HorseList:eq(" . $i . ") td.HorseInfo span.HorseName a")->text();
                $linkArr = explode("/", $link);

                $shutsuba = new ShutsubaData();
                // 馬ID
                $shutsuba->horseId = end($linkArr);
                // 枠番
                $shutsuba->waku = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(0)")->text();
                // 馬番
                $shutsuba->umaban = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(1)")->text();
                // 名前
                $shutsuba->name = $title;
                // 年齢
                $shutsuba->age = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(4) span")->text();
                // 斤量
                $shutsuba->kinryo = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(5)")->text();
                // 騎手
                $shutsuba->jockey = $raceTable->find("tr.HorseList:eq(" . $i . ") td:eq(6) span.Jockey a")->text();
                // 除外
                $shutsuba->isCancel = 0;
                $className = $raceTable->find("tr.HorseList:eq(" . $i . ")")->attr("class");
                if(false !== strpos($className, "Cancel")){
                    $shutsuba->isCancel = 1;
                }

                $raceData->shutsubaArray[] = $shutsuba;
            }
 
            // レース情報
            $raceData->raceInfo = $raceData->startingTime . "発走 ";
            $raceData->raceInfo .= config("const.GROUND_NAME")[$raceData->groundType];
            $raceData->raceInfo .= $raceData->distance . "m";
            $raceData->raceInfo .= "("  . config("const.DIRECTION_NAME")[$raceData->direction] . ") ";
            $raceData->raceInfo .= $raceData->horseCount . "頭";
            // メモ情報
            $babaCode = substr($raceId, 4, 2);
            if(array_key_exists($babaCode, config("const.BABA_MEMO"))){
                $memoKey = "d" . $raceData->distance;
                if(array_key_exists($memoKey, config("const.BABA_MEMO")[$babaCode])){
                    $raceData->courseMemo = config("const.BABA_MEMO")[$babaCode][$memoKey];
                }
            }
            // コースイメージ
            $raceData->courseImage = sprintf("/img/course/%s/d%d.gif", $babaCode, $raceData->distance);
       }
        catch(\Exception $err){
            return null;
        }

        return $raceData;
    }

    /**
     * 地方のレース等級を取得
     */
    private function getRegionRaceGrade($gradeText)
    {
        $gradeCheckArr = [
            "A1A2", "A2B1", "B1B2", "B2B3", "B3C1", "C1C2", "C2C3",
            "A1", "A2", "B1", "B2", "B3", "C1", "C2", "C3",
        ];

        foreach ($gradeCheckArr as $val) {
            if (strpos($gradeText, $val) !== false) {
                return $val;
            }
        }
        return $gradeText;
    }

}
