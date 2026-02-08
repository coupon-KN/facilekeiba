<?php
namespace App\Models;
use App\Models\AnalysisScore;
use App\Models\HorseHistory;


/**
 * 競走馬情報
 */
class HorseData extends BaseModel
{
    /** 馬ID */
    public $horseId;
    /** 名前 */
    public $name;
    /** 生年月日 */
    public $barthDate;
    /** 年齢 */
    public $age;
    /** 成績 */
    public $recode;
    /** 獲得賞金 */
    public $totalPrize;
    /** 勝率 */
    public $winRate;
    /** 複勝率 */
    public $podiumRate;
    /** 脚質(逃げ) */
    public $nigePct = 0;
    /** 脚質(先行) */
    public $senkoPct = 0;
    /** 脚質(差し) */
    public $sashiPct = 0;
    /** 脚質(追込) */
    public $oikomiPct = 0;

    /** 父親のID */
    public $dadId;
    /** 父方の祖父のID */
    public $dadSohuId;
    /** 父方の祖母のID */
    public $dadSoboId;
    /** 母親のID */
    public $mamId;
    /** 母方の祖父のID */
    public $mamSohuId;
    /** 母方の祖母のID */
    public $mamSoboId;

    /** 分析結果 AnalysisScore */
    public $analysisScore = null;

    /** 履歴 HorseHistory */
    public array $recodeArray = [];


    protected function setArrayJson($key, $val) {
        if($key == "recodeArray") {
            foreach($val as $row) {
                $history = new HorseHistory();
                $history->setJsonData($row);
                $this->{$key}[] = $history;
            }
        }
    }

    protected function setAnotherJson($key, $val) {
        if($key == "analysisScore") {
            if($val == null) return false;
            $score = new AnalysisScore();
            $score->setJsonData($val);
            $this->{$key} = $score;
            return true;
        }
        return false;
    }

}
