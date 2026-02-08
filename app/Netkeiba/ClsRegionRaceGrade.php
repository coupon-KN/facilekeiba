<?php
namespace App\Netkeiba;
use Illuminate\Support\Facades\Storage;


/**
 * 地方競馬グレード管理クラス
 */
class ClsRegionRaceGrade
{
    private static $instance = null;
    private static $dataArray = [];


    /**
     * インスタンスの取得
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new ClsRegionRaceGrade();
        }
        return self::$instance;
    }

    /**
     * レースグレードの取得
     */
    public function getRaceGrade($raceDate, $babaCode, $raceNo) : String {
        $year = date('Y', strtotime($raceDate));
        $ymd = date('Ymd', strtotime($raceDate));

        if(!Storage::disk('public')->exists("grade/" . $year . ".json")) {
            return "";
        }
        if(!array_key_exists($year, $this::$dataArray)) {
            $this::$dataArray[$year] = json_decode(Storage::disk('public')->get("grade/" . $year . ".json"), true);
        }

        if(array_key_exists($ymd, $this::$dataArray[$year])) {
            if(array_key_exists($babaCode, $this::$dataArray[$year][$ymd])) {
                if(count($this::$dataArray[$year][$ymd][$babaCode]) >= $raceNo) {
                    return $this::$dataArray[$year][$ymd][$babaCode][$raceNo-1];
                }
            }
        }

        return "";
    }

}
