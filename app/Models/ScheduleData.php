<?php
namespace App\Models;
use App\Models\KaisaiData;

/**
 * 開催情報
 */
class ScheduleData extends BaseModel
{
    /** 開催日 */
    public $date;

    /** 曜日index */
    public $dayOfWeek;

    /** 中央競馬 as KaisaiData */
    public array $centralArray = [];

    /** 地方競馬 as KaisaiData */
    public array $regionArray = [];

    protected function setArrayJson($key, $val){
        if($key == "centralArray" || $key == "regionArray"){
            foreach($val as $row) {
                $info = new KaisaiData();
                $info->setJsonData($row);
                $this->{$key}[] = $info;
            }
        }
    }
    protected function setAnotherJson($key, $val) { return false; }

}
