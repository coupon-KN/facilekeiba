<?php
namespace App\Netkeiba\Service;
use App\Models\KaisaiData;
use App\Models\ScheduleData;
use App\Netkeiba\Scraper\ScheduleScraper;
use App\Infrastructure\JsonFileStorage;


/**
 * スケジュール情報管理クラス
 */
class ScheduleService
{
    private JsonFileStorage $storage;

    public function __construct()
    {
        $this->storage = new JsonFileStorage();
    }

    /**
     * スクレイピングをキック
     */
    public function fetch($selDate)
    {
        $targetDt = date("Y-m-d", strtotime($selDate));

        $scraper = new ScheduleScraper();
        $schedule = $scraper->Scraping($targetDt);

        // 既存データに書き込み
        $fileArray = $this->GetSchedule(substr($targetDt, 0, 4));
        for($i=0; $i<count($fileArray); $i++) {
            if($fileArray[$i]->date == $targetDt){
                $fileArray[$i] = $schedule;
                break;
            }
        }
        $fileNm = $this->getFileName($selDate);
        $this->storage->saveSchedule($fileNm, $fileArray);
    }

    /**
     * 指定日を含む１月分のスケジュール取得
     */
    public function GetMonthSchedule($date)
    {
        $strYearMonth = date("Y-m", strtotime($date));
        return $this->GetSchedule($strYearMonth);
    }

    /**
     * 指定日のスケジュール情報を取得
     */
    public function GetSchedule($strDate) : array
    {
        // ファイルを取得
        $fileName = $this->getFileName($strDate);
        $schedData = $this->storage->getSchedule($fileName);
        if($schedData == null) {
            // 初期データを作成
            $schedData = $this->createBlankData(substr($strDate, 0, 4));
            $this->storage->saveSchedule($fileName, $schedData);
        }

        // クラスに変換
        $rtnArray = [];
        foreach($schedData as $row) {
            $sche = new ScheduleData();
            $sche->setJsonData($row);

            if(strpos($sche->date, $strDate, 0) !== false) {
                $rtnArray[] = $sche;
            }
        }
        return $rtnArray;
    }

    /**
     * 開催情報クラスの取得
     */
    public function getKaisaiByRaceId($raceId) : KaisaiData {
        $year = substr($raceId, 0, 4);
        $babaCode = substr($raceId, 4, 2);
        $isCentral = in_array($babaCode, config("const.CENTRAL_BAMEI_CODE"));

        $scheArray = $this->GetSchedule($year);
        foreach($scheArray as $sche) {
            $kaisaiArr = $isCentral ? $sche->centralArray : $sche->regionArray;
            foreach($kaisaiArr as $item) {
                if($item->id == $raceId){
                    return $item;
                }
            }
        }
        return null;
    }

    /**
     * レースIDから開催日を取得
     */
    public function GetScheduleDay($raceId) : string {
        // ファイルを取得
        $jsonData = $this->GetSchedule(substr($raceId, 0, 4));
        if ($jsonData == null) {
            return "";
        }
 
        // 中央かどうか
        $isCentral = in_array(substr($raceId, 4, 2), config("const.CENTRAL_BAMEI_CODE"));

        // めっちゃ回して探す
        $rtnArray = [];
        foreach($jsonData as $row) {
            $sche = new ScheduleData();
            $sche->setJsonData($row);

            $scheArray = $isCentral ? $sche->centralArray : $sche->regionArray;
            foreach($scheArray as $kaisai) {
                if(str_starts_with($raceId, $kaisai->id)){
                    return $sche->date;
                }
            }
        }
        return "";
    }

    /**
     * ファイル名を取得
     */
    private function getFileName($date){
        if(strlen($date) > 4){
            return date("Y", strtotime($date)) . ".json";
        }else if(strlen($date) == 4){
            return $date . ".json";
        }else {
            return date("Y") . ".json";
        }
    }

    /**
     * １年分空データを作成
     */
    private function createBlankData($year) : array
    {
        $staDate = $year . "-01-01";
        $endDate = $year . "-12-31";
        $currentDate = $staDate;

        $fileArray = [];
        while($currentDate <= $endDate){
            $schedule = new ScheduleData();
            $schedule->date = date("Y-m-d", strtotime($currentDate));
            $schedule->dayOfWeek = date("w", strtotime($currentDate));
            $fileArray[] = $schedule;

            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }

        return $fileArray;
    }

}
