<?php
namespace App\Models;


/**
 * 出馬情報
 */
class ShutsubaData extends BaseModel
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

    protected function setArrayJson($key, $val) {}
    protected function setAnotherJson($key, $val) { return false; }

}
