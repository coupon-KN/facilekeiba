<?php
namespace App\Netkeiba\Analysis;
use App\Models\ViewHorseData;
use App\Models\HorseHistory;
use App\Models\HistorySummary;

/**
 * 過去レース分析クラス
 */
class RaceHistoryAnalyzer
{
    /**
     * 集計処理
     */
    public function summary(ViewHorseData $horse, string $kaisaiDate) : HistorySummary
    {
        // 前走からの間隔
        $intervalDay = $this->intervalPreRace($horse, $kaisaiDate);
        // 距離別スコア配列
        $distanceScoreArray = $this->getDistanceScoreArray($horse);
        // 馬場別スコア配列
        $trackScoreArray = $this->getTrackScoreArray($horse);
        // 好走した中で一番高いクラス等級
        $highestRank = $this->searchHighestRank($horse);

        // 距離別スコア配列をkey、value形式に変換
        $rtnDistanceScoreArray = [];
        foreach($distanceScoreArray as $key => $val) {
            $rtnDistanceScoreArray[] = ["key" => $key, "value" => $val];
        }
        // 馬場別スコアのkeyを名称に変換
        $rtnTrackScoreArray = [];
        foreach($trackScoreArray as $key => $val) {
            $name = "etc";
            if(array_key_exists($key, config("const.BAMEI_NAME"))) {
                $name = config("const.BAMEI_NAME")[$key];
            }
            $rtnTrackScoreArray[] = ["key" => $name, "value" => $val];
        }

        $rtnScore = new HistorySummary();
        $rtnScore->intervalDay = $intervalDay;
        $rtnScore->highestRank = $this->getClassName($highestRank);
        $rtnScore->distanceScores = $rtnDistanceScoreArray;
        $rtnScore->trackScores = $rtnTrackScoreArray;

        return $rtnScore;
    }

    /**
     * 前走からの日数を計算
     */
    private function intervalPreRace(ViewHorseData $horse, string $kaisaiDate): int
    {
        if(count($horse->recodeArray) > 0) {
            foreach($horse->recodeArray as $recode)
            {
                if(!is_numeric($recode->rankNo)) continue;
                return $daysAgo = (strtotime($kaisaiDate) - strtotime($recode->date)) / 86400;
            }
        }
        return 0;
    }

    /**
     * 基準スコア(着差から算出）
     */
    private function baseScore(HorseHistory $recode): int {
        $score = 0;

        if($recode->rankNo == "1") $score = 95;
        if($recode->rankNo == "2") $score = 85;
        if($recode->rankNo == "3") $score = 75;
        if($recode->rankNo == "4") $score = 65;
        if($recode->rankNo == "5") $score = 55;

        // 着差で加点
        if($score != 100 && $recode->difference !== ""){
            $diff = floatval($recode->difference);

            if($diff == 0) $score += 5;
            if($diff == 0.1) $score += 3;
            if($diff == 0.2) $score += 1;
        }
        // 上り順位で加点
        if(is_numeric($recode->agariRank)) {
            if($recode->agariRank == 1) $score += 5;
            if($recode->agariRank == 2) $score += 3;
            if($recode->agariRank == 3) $score += 1;
        }

        return $score;
    }

    /**
     * 距離別スコア配列
     */
    private function getDistanceScoreArray(ViewHorseData $horse): array
    {
        $scores = [];
        foreach($horse->recodeArray as $recode)
        {
            if($recode->groundType != 1 && $recode->groundType != 2) continue;

            $score = $this->baseScore($recode);
            if($score == 0) continue;

            if(array_key_exists($recode->distance, $scores)){
                $scores[$recode->distance]["cnt"]  += 1;
                $scores[$recode->distance]["score"] += $score;
            } else {
                $scores[$recode->distance] = ["cnt" => 1, "score" => $score];
            }
        }

        $rtnArr = [];
        foreach($scores as $key => $score)
        {
            $rtnArr[$key] = floor($score["score"] / $score["cnt"]);
        }
        arsort($rtnArr);
        return $rtnArr;
    }

    /**
     * 競馬場スコア配列
     */
    private function getTrackScoreArray(ViewHorseData $horse): array
    {
        $scores = [];
        foreach($horse->recodeArray as $recode)
        {
            // 馬場コード
            $babaCode = "";
            foreach(config("const.BAMEI_NAME") as $code => $val){
                if(strpos($recode->baba, $val) !== false){
                    $babaCode = $code;
                    break;
                }
            }
            if($babaCode == "") continue;

            $score = $this->baseScore($recode);
            if($score == 0) continue;

            if(array_key_exists($babaCode, $scores)){
                $scores[$babaCode]["cnt"]  += 1;
                $scores[$babaCode]["score"] += $score;
            } else {
                $scores[$babaCode] = ["cnt" => 1, "score" => $score];
            }
        }

        $rtnArr = [];
        foreach($scores as $key => $score)
        {
            $rtnArr[$key] = floor($score["score"] / $score["cnt"]);
        }
        arsort($rtnArr);
        return $rtnArr;
    }

    /**
     * 好走した中で一番高いクラス等級
     */
    private function searchHighestRank(ViewHorseData $horse)
    {
        $classRankArray = config("const.RACE_CLASS_RANK");
        $highestRank = $classRankArray[array_key_last($classRankArray)];
        $searchArr = [
            "(GI)" => $classRankArray["G1"],
            "(GII)" => $classRankArray["G2"],
            "(GIII)" => $classRankArray["G3"],
            "(L)" => $classRankArray["リステッド"],
            "(OP)" => $classRankArray["オープン"],
            "3勝クラス" => $classRankArray["３勝クラス"],
            "2勝クラス" => $classRankArray["２勝クラス"],
            "1勝クラス" => $classRankArray["１勝クラス"]
        ];

        foreach($horse->recodeArray as $recode) {
            // 3着以内または着差が0.1以下
            if(!is_numeric($recode->rankNo)) continue;
            if($recode->rankNo > 3 && ($recode->difference == "" || $recode->difference > 0.2)) continue;

            foreach($searchArr as $r_nm => $rank) {
                if(strpos($recode->raceName, $r_nm) !== false) {
                    $highestRank = min($rank, $highestRank);
                    break;
                }
            }
        }

        return $highestRank;
    }


    /**
     * クラス等級から名称を取得
     */
    private function getClassName($rank) : string
    {
        $keys = array_keys(config("const.RACE_CLASS_RANK"));
        if(count($keys) >= $rank) {
            return $keys[$rank - 1];
        }
        return $keys[count($keys) - 1];
    }



}
