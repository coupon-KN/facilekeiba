<?php
namespace App\Netkeiba\Scraper;
use App\Models\HorseData;
use App\Models\HorseHistory;
use App\Netkeiba\ClsParentHorse;
use App\Netkeiba\ClsRegionRaceGrade;
use Illuminate\Support\Facades\Storage;
use phpQuery;


/**
 * 競走馬情報スクレイピングクラス
 */
class HorseInfoScraper
{
    /**
     * スクレイピング
     */
    public function Scraping($horseId, $horseData) : HorseData
    {
        // netkeibaのサイトからhtml情報を取得
        $html = file_get_contents("https://db.netkeiba.com/horse/" . $horseId);
        if(!$html){
            return null;
        }

        $firstRecodeDate = "";
        $tempArray = [];
        if($horseData != null) {
            if(count($horseData->recodeArray) > 0) {
                $firstRecodeDate = $horseData->recodeArray[0]->date;
                $tempArray = $horseData->recodeArray;
                $horseData->recodeArray = [];
            }
        }else{
            $horseData = new HorseData();
        }
        
        try {
            // htmlを読み込み
            $doc = phpQuery::newDocument($html);

            $strWork = $doc->find('div.db_head div.db_head_name div.horse_title')->text();

            // ID、名前、年齢
            $horseData->horseId = $horseId;
            $horseData->name = trim(explode("\n", trim($strWork))[0]);
            $horseData->age = mb_substr($strWork, mb_strpos($strWork, "歳")-2, 3);

            // 通算成績
            $profTable = $doc->find('div.db_main_deta table.db_prof_table')->getDocument();
            $loop = count($profTable->find('tr')->elements);
            for($i=0; $i<$loop; $i++){
                $th = $profTable->find("tr:eq(". $i . ") th")->text();
                if($th == "生年月日"){
                    $strWork = $profTable->find("tr:eq(". $i . ") td")->text();
                    $dtWork = \DateTime::createFromFormat('Y年m月d日', $strWork);
                    if ($dtWork === false) continue;
                    $horseData->barthDate = $dtWork->format('Y/m/d');
                }elseif($th == "通算成績"){
                    $strWork = $profTable->find("tr:eq(". $i . ") td a")->text();
                    $rankArr = explode("-", $strWork);
                    // 1位数
                    $rank1Count = intval($rankArr[0]);
                    // 2位数
                    $rank2Count = intval($rankArr[1]);
                    // 3位数
                    $rank3Count = intval($rankArr[2]);
                    // ランク外
                    $rankEtcCount = intval($rankArr[3]);
                    // 通算レース数
                    $raceTotal = $rank1Count + $rank2Count + $rank3Count + $rankEtcCount;

                    $horseData->recode = sprintf("[%d-%d-%d-%d]", $rank1Count, $rank2Count, $rank3Count, $rankEtcCount);
                    if($raceTotal > 0){
                        $horseData->winRate = round($rank1Count / $raceTotal * 100);
                        $horseData->podiumRate = round(($rank1Count + $rank2Count + $rank3Count) / $raceTotal * 100);
                    }else{
                        $horseData->winRate = 0;
                        $horseData->podiumRate = 0;
                    }
                }elseif($th == "獲得賞金"){
                    $strWork = $profTable->find("tr:eq(". $i . ") td")->text();
                    $strWork = str_replace(" ", "", trim($strWork));
                    $horseData->totalPrize = str_replace("/", "\n", trim($strWork));
                }
            }

            // 血統情報
            $html = file_get_contents("https://db.netkeiba.com/horse/ajax_horse_pedigree.html?input=UTF-8&output=html&id=" . $horseId);
            if($html){
                $doc = phpQuery::newDocument($html);
                $bloodIdArr = [];
                $ankArr = $doc->find('table.blood_table a')->elements;
                for($i=0; $i<count($ankArr); $i++) {
                    $link = $ankArr[$i]->getAttribute("href");
                    $linkArr = explode("/", trim($link,'/'));
                    $bloodIdArr[] = end($linkArr);
                }
            }
        
            // 競走成績
            $html = file_get_contents("https://db.netkeiba.com/horse/ajax_horse_results.html?input=UTF-8&output=html&id=" . $horseId);
            if($html){
                $html = mb_convert_encoding($html, "UTF-8", "EUC-JP");
                $doc = phpQuery::newDocument($html);

                $loop = count($doc->find('table.db_h_race_results tbody tr')->elements);
                for($i=0; $i<$loop; $i++){
                    $row = $doc->find("table.db_h_race_results tbody tr:eq(" . $i . ")");

                    $raceDate = $row->find("td:eq(0) a")->text();
                    if($firstRecodeDate == $raceDate) break;

                    $history = new HorseHistory();
                    $history->date = $row->find("td:eq(0) a")->text();
                    $history->baba = $row->find("td:eq(1) a")->text();
                    $history->tenki = $row->find("td:eq(2)")->text();
                    $history->raceNo = intval($row->find("td:eq(3)")->text());
                    $history->raceName = $row->find("td:eq(4) a")->text();
                    $history->horseCount = intval($row->find("td:eq(6)")->text());
                    $history->waku = intval($row->find("td:eq(7)")->text());
                    $history->umaban = intval($row->find("td:eq(8)")->text());
                    $history->odds = $row->find("td:eq(9)")->text();
                    $history->ninki = intval($row->find("td:eq(10)")->text());
                    $history->rankNo = $row->find("td:eq(11)")->text();
                    $history->jockey = $row->find("td:eq(12) a")->text();
                    $history->kinryo = intval($row->find("td:eq(13)")->text());
                    $strWork = $row->find("td:eq(14)")->text();
                    $history->distance = intval(preg_replace('/[^0-9]/', '', $strWork));
                    if(false !== strpos($strWork, "芝")){
                        $history->groundType = 1;
                        $history->groundShortName = config("const.GROUND_SHORT_NAME")[1];
                    }elseif(false !== strpos($strWork, "ダ")){
                        $history->groundType = 2;
                        $history->groundShortName = config("const.GROUND_SHORT_NAME")[2];
                    }else{
                        $history->groundType = 3;
                        $history->groundShortName = config("const.GROUND_SHORT_NAME")[3];
                    }
                    $history->condition = $row->find("td:eq(16)")->text();
                    $history->time = $row->find("td:eq(18)")->text();
                    $history->difference = trim($row->find("td:eq(19)")->text());
                    $history->pointTime = $row->find("td:eq(21)")->text();
                    $strWork = $row->find("td:eq(22)")->text();
                    $strArr = explode("-", $strWork);
                    if(count($strArr) >= 2){
                        $history->firstPace = $strArr[0];
                        $history->latterPace = $strArr[1];
                    }else{
                        $history->firstPace = "";
                        $history->latterPace = "";
                    }
                    $history->agari600m = $row->find("td:eq(23)")->text();
                    // 上りランク
                    $history->agariRank = 99;
                    $clsNm = $row->find("td:eq(23)")->attr("class");
                    if(strpos($clsNm, "rank_") !== false) {
                        preg_match('/rank_[0-9]/', $clsNm, $match);
                        $history->agariRank = intval(preg_replace('/[^0-9]/', '', $match[0]));
                    }
                    $history->weight = $row->find("td:eq(24)")->text();
                    $history->winHorse = $row->find("td:eq(27) a")->text();

                    // ペース区分を計算
                    if($history->firstPace != "" && $history->latterPace != ""){
                        $paceDiff = (double)$history->firstPace - (double)$history->latterPace;
                        if($paceDiff <= 1.0 && $paceDiff >= -1.0){
                            $history->paceKbn = "M";
                        }elseif($paceDiff > 1.0){
                            $history->paceKbn = "S";
                        }else{
                            $history->paceKbn = "H";
                        }
                    }

                    // 過去のレースURLを作成
                    if($history->date != "" && $history->baba != "" && $history->raceNo) {
                        // 馬名からコードを取得
                        $babaName = "";
                        $babaCode = "";
                        foreach(config("const.BAMEI_NAME") as $key => $val){
                            if(strpos($history->baba, $val) !== false){
                                $babaCode = $key;
                                $babaName = $val;
                                break;
                            }
                        }

                        if($babaCode != ""){
                            if(in_array($babaCode, config("const.CENTRAL_BAMEI_CODE"))){
                                // 中央競馬
                                $rtnId = date("Y", strtotime($history->date));
                                $rtnId .= substr("00" . preg_replace("/[^0-9]/", "", explode($babaName, $history->baba)[0]), -2);
                                $rtnId .= $babaCode;
                                $rtnId .= substr("00" . preg_replace("/[^0-9]/", "", explode($babaName, $history->baba)[1]), -2);
                                $rtnId .= substr("00" . $history->raceNo, -2);
                                $history->raceUrl = str_replace("{raceid}", $rtnId, config("const.JRA_MOVIE_PAGE"));

                                $history->raceGrade = $this->GetCentralRaceGrade($history->raceName);
                            }
                            else if(array_key_exists($babaCode, config('const.REGION_TRACK_ID'))){
                                // 地方競馬
                                $raceUrl = config("const.REGION_MOVIE_PAGE");
                                $raceUrl = str_replace("{track}", config('const.REGION_TRACK_ID')[$babaCode], $raceUrl);
                                $raceUrl = str_replace("{date}", date("Ymd", strtotime($history->date)), $raceUrl);
                                $raceUrl = str_replace("{raceno}", substr("00" . $history->raceNo, -2), $raceUrl);
                                $history->raceUrl = $raceUrl;

                                $history->raceGrade = ClsRegionRaceGrade::getInstance()->getRaceGrade($history->date, $babaCode, $history->raceNo);
                            }
                        }
                    }

                    // 脚質判定
                    if($history->pointTime != "") {
                        $history->kyakushitsu = "";
                        $cornerArr = explode("-", $history->pointTime);
                        for($j=0; $j<count($cornerArr)-1; $j++) {
                            if($cornerArr[$j] == "1") $history->kyakushitsu = "逃げ";
                        }
                        $lastCorner = $cornerArr[count($cornerArr)-1];
                        if($history->kyakushitsu == "" && $lastCorner <= 4) {
                            $history->kyakushitsu = "先行";
                        }
                        if($history->kyakushitsu == "" && $history->horseCount > 8 && $history->rankNo < ($history->horseCount * 2 / 3)) {
                            $history->kyakushitsu = "差し";
                        }
                        if($history->kyakushitsu == "") $history->kyakushitsu = "追込";
                    }

                    $horseData->recodeArray[] = $history;
                    $horseData->analysisScore = null;
                }
            }

            if($firstRecodeDate != "") {
                $horseData->recodeArray = array_merge($horseData->recodeArray, $tempArray);
            }

            // 父の情報
            if(count($bloodIdArr) > 0){
                $horseData->dadId = $bloodIdArr[0];
            }
            // 父方の祖父の情報
            if(count($bloodIdArr) > 1){
                $horseData->dadSohuId = $bloodIdArr[1];
            }
            // 父方の祖母の情報
            if(count($bloodIdArr) > 2){
                $horseData->dadSoboId = $bloodIdArr[2];
            }
            // 母の情報
            if(count($bloodIdArr) > 3){
                $horseData->mamId = $bloodIdArr[3];
            }
            // 母方の祖父の情報
            if(count($bloodIdArr) > 4){
                $horseData->mamSohuId = $bloodIdArr[4];
            }
            // 母方の祖母の情報
            if(count($bloodIdArr) > 5){
                $horseData->mamSoboId = $bloodIdArr[5];
            }

            // 履歴から脚質を計算
            if(count($horseData->recodeArray) > 0) {
                $nigeCnt = 0;
                $senkoCnt = 0;
                $sashiCnt = 0;
                $oikomiCnt = 0;
                $ashiTotal = count($horseData->recodeArray);
                foreach($horseData->recodeArray as $his) {
                    if($his->kyakushitsu == "逃げ") $nigeCnt += 1;
                    if($his->kyakushitsu == "先行") $senkoCnt += 1;
                    if($his->kyakushitsu == "差し") $sashiCnt += 1;
                    if($his->kyakushitsu == "追込") $oikomiCnt += 1;
                }
                $horseData->nigePct = floor($nigeCnt / $ashiTotal * 100);
                $horseData->senkoPct = floor($senkoCnt / $ashiTotal * 100);
                $horseData->sashiPct = floor($sashiCnt / $ashiTotal * 100);
                $horseData->oikomiPct = floor($oikomiCnt / $ashiTotal * 100);
            }
        }
        catch(\Exception $err){
            return null;
        }
        
        return $horseData;
    }

    /**
     * 中央のレース等級の取得
     */
    private function GetCentralRaceGrade($raceName) : string {

        if(strpos($raceName, "(GI)") !== false) return "G1";
        if(strpos($raceName, "(GII)") !== false) return "G2";
        if(strpos($raceName, "(GIII)") !== false) return "G3";
        if(strpos($raceName, "(L)") !== false) return "リステッド";
        if(strpos($raceName, "(OP)") !== false) return "オープン";
        if(strpos($raceName, "3勝クラス") !== false) return "３勝クラス";
        if(strpos($raceName, "2勝クラス") !== false) return "２勝クラス";
        if(strpos($raceName, "1勝クラス") !== false) return "１勝クラス";
        return "未勝利";
    }

}
