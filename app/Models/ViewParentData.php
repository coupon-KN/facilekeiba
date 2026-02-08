<?php
namespace App\Models;

/**
 * 親馬の情報ファイル
 */
class ViewParentData
{
    /** 馬ID */
    public $horseId;

    /** 名前 */
    public $name;

    /** 成績 */
    public $recode;

    /** 勝率 */
    public $winRate;

    /** 複勝率 */
    public $podiumRate;

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
}
