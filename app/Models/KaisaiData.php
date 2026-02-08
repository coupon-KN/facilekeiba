<?php
namespace App\Models;


/**
 * 開催情報
 */
class KaisaiData extends BaseModel
{
    /** 開催ID */
    public $id;

    /** 名前 */
    public $name;

    /** 馬場コード */
    public $babaCode;

    /** 馬場名 */
    public $babaName;

    /** レース数回 */
    public $raceCount;

    protected function setArrayJson($key, $val) {}
    protected function setAnotherJson($key, $val) { return false; }

}
