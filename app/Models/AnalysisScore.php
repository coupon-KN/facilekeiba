<?php
namespace App\Models;

class AnalysisScore extends BaseModel
{
    /** バージョン */
    public $version = 0;

    /** タイプ：スピード(比率) */
    public $speed;

    /** タイプ：パワー(比率) */
    public $power;

    /** タイプ：スタミナ(比率) */
    public $stamina;

    /** スプリント(1200m) */
    public $sprint;

    /** マイル(1600m) */
    public $mile;

    /** 中距離(2000m) */
    public $middle;

    /** 長距離(2400m) */
    public $long;

    public function getTypeString(){
        $scores = [
            "スピード" => $this->speed,
            "パワー"   => $this->power,
            "スタミナ" => $this->stamina
        ];
        $arr = $this->overRateScores($scores, 0.8);
        return implode("、", $arr);
    }

    public function getDistanceString(){
        $scores = [
            "スプリント" => $this->sprint,
            "マイル"     => $this->mile,
            "中距離"     => $this->middle,
            "長距離"     => $this->long,
        ];

        $arr = $this->overRateScores($scores, 0.75);
        return implode("、", $arr);
    }

    private function overRateScores($scores, $rate) {
        arsort($scores);
        $max = reset($scores);
        if($max == 0) return [];

        $rtnArray = [];
        foreach ($scores as $k => $v) {
            if ($v >= $max * $rate) {
                $rtnArray[] = $k;
            }
        }
        if(count($rtnArray) > 2){
            $rtnArray = array_splice($rtnArray, 0, 2);
        }
        return $rtnArray;
    }
 
    protected function setArrayJson($key, $val) {}
    protected function setAnotherJson($key, $val) { return false; }
}