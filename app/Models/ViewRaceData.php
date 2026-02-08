<?php
namespace App\Models;


/**
 * 表示用レース情報
 */
class ViewRaceData
{
    /** レースID */
    public $raceId;

    /** レースNo */
    public $raceNo;

    /** 開催馬場コード */
    public $babaCode;

    /** 開催馬場名 */
    public $babaName;

    /** 開催場 */
    public $kaisai;

    /** レース名 */
    public $raceName;

    /** 出走時刻 */
    public $startingTime;

    /** 種類(1:芝、2:ダート、3:障害) */
    public $groundType = 0;

    /** 距離 */
    public $distance = 0;

    /** 向き(1:左、2:右) */
    public $direction = 0;

    /** レース等級 */
    public $raceGrade;

    /** 頭数 */
    public $horseCount;

    /** レース情報 */
    public $raceInfo;

    /** コースメモ */
    public $courseMemo;

    /** コースイメージ */
    public $courseImage;

    /** レースコメント */
    public $raceComment;

    /** 競走馬情報配列 ViewHorseData[] */
    public array $horseArray = [];
}
