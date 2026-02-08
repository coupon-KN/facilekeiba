<?php
namespace App\Models;
use App\Models\HorseData;


/**
 * レース情報
 */
class RaceData extends BaseModel
{
    /** レースID */
    public $raceId;

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

    /** 競走馬情報配列 ShutsubaData[] */
    public array $shutsubaArray = [];

    protected function setArrayJson($key, $val){
        if($key == "shutsubaArray"){
            foreach($val as $row) {
                $shutsuba = new ShutsubaData();
                $shutsuba->setJsonData($row);
                $this->{$key}[] = $shutsuba;
            }
        }
    }
    protected function setAnotherJson($key, $val) { return false; }

    public function GetBabaCode() {
        return substr($this->raceId, 4, 2);
    }
    public function GetBabaName() {
        return config("const.BAMEI_NAME")[$this->GetBabaCode()];
    }
    public function GetRaceNo() {
        return intval(substr($this->raceId, -2));
    }

}
