<?php
namespace App\Netkeiba;
use App\Models\ViewRaceData;
use App\Models\ViewHorseData;
use App\Models\HorseHistory;


/**
 * 中央競馬の採点ロジックのクラス
 */
class ClsScoringCentral
{

    /**
     * 採点処理
     */
    public function Scoring(ViewRaceData $raceData, string $kaisaiDate)
    {
        $returnArr = [];

        foreach($raceData->horseArray as $horse)
        {
            // 前走からの間隔
            $intervalDay = $this->intervalPreRace($horse, $kaisaiDate);
            // 距離別スコア配列
            $distanceScoreArray = $this->getDistanceScoreArray($raceData, $horse);
            // 馬場別スコア配列
            $trackScoreArray = $this->getTrackScoreArray($horse);
            // 好走した中で一番高いクラス等級
            $highestRank = $this->searchHighestRank($horse);

            // 今回の点数を計算
            $totalScore = $this->calcScore($raceData, $intervalDay, $distanceScoreArray, $trackScoreArray, $highestRank);
            // 穴馬候補
            $anomalyScore = $this->AnomalyScoring($raceData, $horse, $kaisaiDate);

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

            $returnArr[] = [
                "id" => $horse->horseId,
                "waku" => $horse->waku,
                "umaban" => $horse->umaban,
                "name" => $horse->name,

                "interval_day" => $intervalDay,
                "highest_rank" => $this->getClassName($highestRank),
                "distance_scores" => $rtnDistanceScoreArray,
                "track_scores" => $rtnTrackScoreArray,

                "score" => $totalScore,
                "anomaly" => floor($anomalyScore)
            ];
        }

        return $returnArr;
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
        $score = 40;
        if($recode->difference !== ""){
            $diff = floatval($recode->difference);

            $agariScore = 1;
            if($diff < -1){
                $diff = abs($diff * 10);
                $score = 100 + $diff * 10;
                $agariScore = 2;
            }elseif($diff < 0) {
                $diff = $diff * -10;
                $score = 100 + $diff * 5;
            }elseif($diff == 0.0) {
                $score = 100;
            }elseif($diff > 0.0) {
                $diff = $diff * 10;
                $score = 100 - $diff * 10;
            }

            // 上り順位で加点
            if(is_numeric($recode->agariRank)) {
                if($recode->agariRank == 1) $agariScore = $agariScore * 25;
                if($recode->agariRank == 2) $agariScore = $agariScore * 15;
                if($recode->agariRank == 3) $agariScore = $agariScore * 10;
            }else{
                $agariScore = 0;
            }

            $score += $agariScore;
        }
        
        return max(40, $score);
    }

    /**
     * 距離別スコア配列
     * 芝、ダート違いだとスコアを8掛け
     */
    private function getDistanceScoreArray(ViewRaceData $raceData, ViewHorseData $horse): array
    {
        // 障害は対象外
        if($raceData->groundType != 1 && $raceData->groundType != 2) return [];

        $scores = [];
        foreach($horse->recodeArray as $recode)
        {
            if($recode->groundType != 1 && $recode->groundType != 2) continue;

            $score = $this->baseScore($recode);
            $score *= ($raceData->groundType == $recode->groundType) ? 1.0 : 0.8;

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
     * 採点処理
     */
    private function calcScore(ViewRaceData $raceData,
        int $intervalDay,
        array $distanceScoreArray,
        array $trackScoreArray,
        string $highestRank
    ): float
    {
        $d_score = 0;
        $t_score = 0;

        // 距離適性
        $cnt = 0;
        foreach($distanceScoreArray as $key => $val) {
            if($key == $raceData->distance) {
                $d_score += $val;
                $cnt += 1;
            }
            elseif(abs($key - $raceData->distance) <= 200) {
                $d_score += $val * 0.8;
                $cnt += 1;
            }
        }
        if($d_score > 0) {
            $d_score = $d_score / $cnt;
        }

        // 競馬場適性
        foreach($trackScoreArray as $key => $val) {
            if($key == $raceData->babaCode) {
                $t_score = $val;
            }
        }

        // 今回のレース等級
        $raceGrade = config("const.RACE_CLASS_RANK")[array_key_last(config("const.RACE_CLASS_RANK"))];
        if(array_key_exists($raceData->raceGrade, config("const.RACE_CLASS_RANK"))){
            $raceGrade = config("const.RACE_CLASS_RANK")[$raceData->raceGrade];
        }
        // クラス加点
        $gradeDiff = $raceGrade - $highestRank;
        $addScore = 0;
        if( $gradeDiff > 0) $addScore = 10 * $gradeDiff;
        elseif( $gradeDiff == 0) $addScore = 0;
        else $addScore = -5 * $gradeDiff;

        // 総合点
        $totalScore = $d_score * 0.8 + $t_score * 0.2 + $addScore;
        if($intervalDay > 90)
        {   // 休み明けは少し減点
            $totalScore *= 0.95;
        }
        return floor($totalScore);
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





    /**
     * 穴馬度スコア
     */
    private function AnomalyScoring(ViewRaceData $raceData, ViewHorseData $horse, string $kaisaiDate): float
    {
        $score = 0.0;

        // 過去レースデータから追加加点
        foreach($horse->recodeArray as $recode)
        {
            if(strtotime($recode->date) < strtotime($kaisaiDate . " -1 year")) continue;

            // 人気と結果のギャップ
            $score += $this->popVsResultScore($recode);
            // 人気薄での好走
            $score += $this->underdogScore($recode);
            // オッズ値による補正
            $score += $this->oddsEffect(floatval($recode->odds));
            // 末脚評価
            $score += $this->agariScore($recode);
            // コース適性
            $score += $this->courseSpecialist($raceData, $recode);
        }

        return $score;
    }
    private function agariScore(HorseHistory $recode): float {
        if (!is_numeric($recode->agariRank)) return 0.0;
        if ($recode->agariRank == 1) return 10.0;
        if ($recode->agariRank <= 3) return 6.0;
        return 0.0;
    }
    private function courseSpecialist(ViewRaceData $raceData, HorseHistory $recode): float {
        $score = 0.0;
        $babaCode = "";
        foreach(config("const.BAMEI_NAME") as $code => $val){
            if(strpos($recode->baba, $val) !== false){
                $babaCode = $code;
                break;
            }
        }
        // 競馬場が同じ
        if ($raceData->babaCode == $babaCode) $score += 2.0;
        // 馬場状態
        if ($raceData->groundType == $recode->groundType) $score += 2.0;
        // 距離適性
        $diff = abs($raceData->distance - $recode->distance);
        if ($diff == 0) $score += 3.0;
        elseif ($diff <= 200) $score += 2.0;
        elseif ($diff <= 400) $score += 1.0;

        // 人気 vs 着順 ギャップ評価
        if (is_numeric($recode->rankNo) && is_numeric($recode->ninki)) {
            $gap = $recode->ninki - $recode->rankNo;

            if ($gap >= 5 && $recode->rankNo <= 3) {
                // 人気より大幅に上の着順、しかも好走 → 穴馬実績大
                $score += 5.0;
            } elseif ($gap >= 3 && $recode->rankNo <= 5) {
                // 掲示板以内ならそこそこ評価
                $score += 3.0;
            } elseif ($gap < 0) {
                // 人気先行で凡走 → むしろ減点（過剰人気馬）
                $score -= 2.0;
            }
        }
        return $score;
    }
    private function popVsResultScore(HorseHistory $recode): float {
        if (!is_numeric($recode->rankNo) || !is_numeric($recode->ninki)) return 0.0;

        $diff = $recode->ninki - $recode->rankNo; // 人気より着順が上ならプラス
        if ($diff >= 5) return 12.0;   // 大きく人気を裏切った好走
        if ($diff >= 3) return 8.0;    // 少し人気以上に走った
        if ($diff >= 1) return 4.0;
        return 0.0;
    }
    private function underdogScore(HorseHistory $recode): float {
        if (!is_numeric($recode->rankNo) || !is_numeric($recode->ninki)) return 0.0;

        if ($recode->ninki >= 8 && $recode->rankNo <= 5) {
            return 10.0; // 中穴で善戦
        }
        if ($recode->ninki >= 12 && $recode->rankNo <= 3) {
            return 15.0; // 大穴で激走
        }
        return 0.0;
    }
    private function oddsEffect(float $odds): float {
        if ($odds >= 2.0 && $odds <= 15.0) return 2.0;  // 妥当な範囲 → 少しプラス
        if ($odds > 15.0 && $odds <= 50.0) return 5.0;  // 中穴ゾーン
        if ($odds > 50.0 && $odds <= 100.0) return 3.0; // 大穴ゾーン（ちょい補正）
        if ($odds > 100.0) return -3.0;                 // 極端な大穴は信頼度↓
        return 0.0;
    }

}
