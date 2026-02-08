<?php
namespace App\Models;


/**
 * 表示用出馬情報
 */
class ViewHorseData
{
    /** 馬ID */
    public $horseId;
    /** 枠番 */
    public $waku;
    /** 馬番 */
    public $umaban;
    /** 名前 */
    public $name;
    /** 年齢 */
    public $age;
    /** 斤量 */
    public $kinryo;
    /** 騎手 */
    public $jockey;
    /** 除外 */
    public $isCancel;
    /** 成績 */
    public $recode;
    /** 獲得賞金 */
    public $totalPrize;
    /** 勝率 */
    public $winRate;
    /** 複勝率 */
    public $podiumRate;
    /** 脚質(逃げ) */
    public $nigePct;
    /** 脚質(先行) */
    public $senkoPct;
    /** 脚質(差し) */
    public $sashiPct;
    /** 脚質(追込) */
    public $oikomiPct;
    /** ユーザーコメント */
    public $userComment = "";

    /** 父親の情報 */
    public ViewParentData $dad;
    /** 父方の祖父の情報 */
    public ViewParentData $dadSohu;
    /** 父方の祖母の情報 */
    public ViewParentData $dadSobo;
    /** 母親の情報 */
    public ViewParentData $mam;
    /** 母方の祖父の情報 */
    public ViewParentData $mamSohu;
    /** 母方の祖母の情報 */
    public ViewParentData $mamSobo;

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

    /** タイプ文字列 */
    public $typeString;
    /** 距離文字列 */
    public $distanceString;
    /** 集計結果 HistorySummary */
    public $historySummary;

    /** 履歴 HorseHistory */
    public array $recodeArray = [];
}
