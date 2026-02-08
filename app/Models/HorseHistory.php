<?php
namespace App\Models;

/**
 * レース履歴情報格納クラス
 */
class HorseHistory extends BaseModel
{
    /** 日付 */
    public $date;

    /** 開催馬場 */
    public $baba;

    /** 天気 */
    public $tenki;

    /** レースNo */
    public $raceNo;

    /** レース名 */
    public $raceName;

    /** レースグレード */
    public $raceGrade = "";

    /** レースURL */
    public $raceUrl;

    /** 頭数 */
    public $horseCount;

    /** 枠番 */
    public $waku;

    /** 馬番 */
    public $umaban;

    /** オッズ */
    public $odds;

    /** 人気 */
    public $ninki;

    /** 着順 */
    public $rankNo;

    /** 騎手 */
    public $jockey;

    /** 斤量 */
    public $kinryo;

    /** 距離 */
    public $distance;

    /** 種類(1:芝、2:ダート、3:障害) */
    public $groundType;

    /** 種類略称(1:芝、2:ダ、3:障) */
    public $groundShortName;

    /** 馬場状態 */
    public $condition;

    /** タイム */
    public $time;

    /** 着差 */
    public $difference;

    /** 通過 */
    public $pointTime;

    /** ペース(前半3ハロン) */
    public $firstPace;

    /** ペース(後半3ハロン) */
    public $latterPace;

    /** ペース区分(S,M,H) */
    public $paceKbn;

    /** 上り3ハロン */
    public $agari600m;

    /** 上りランク */
    public $agariRank;

    /** 馬体重 */
    public $weight;

    /** 勝ち馬 */
    public $winHorse;

    /** 脚質 */
    public $kyakushitsu;

    protected function setArrayJson($key, $val) {}
    protected function setAnotherJson($key, $val) { return false; }

}
