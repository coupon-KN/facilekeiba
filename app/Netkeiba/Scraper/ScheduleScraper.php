<?php
namespace App\Netkeiba\Scraper;
use App\Models\KaisaiData;
use App\Models\ScheduleData;
use Illuminate\Support\Facades\Storage;
use phpQuery;


/**
 * スケジュール情報スクレイピングクラス
 */
class ScheduleScraper
{
    /**
     * 指定日の情報をスクレイピング
     */
    public function Scraping($selDate) : ScheduleData
    {
        $rtnObj = new ScheduleData();

        $rtnObj->date = date("Y-m-d", strtotime($selDate));
        $rtnObj->dayOfWeek = date("w", strtotime($selDate));
        // 中央競馬のスクレイピングを実行
        $rtnObj->centralArray = $this->_scrapingCentral($rtnObj->date);
        // 地方競馬のスクレイピングを実行
        $rtnObj->regionArray = $this->_scrapingRegion($rtnObj->date);

        return $rtnObj;
    }

    /**
     * Netkeibaから中央競馬の開催情報をスクレイピング
     */
    private function _scrapingCentral($strYmd) : array
    {
        $rtnArray = [];

        try {
            $link = "https://race.netkeiba.com/top/race_list_sub.html?kaisai_date=";
            $html = file_get_contents($link . date("Ymd", strtotime($strYmd)));

            $doc = phpQuery::newDocument($html);
            $cnt = count($doc->find("p.RaceList_DataTitle"));
            if($cnt > 0){
                for($i=0; $i<$cnt; $i++) {
                    $element = $doc->find("p.RaceList_DataTitle:eq(" . $i . ")");
                    $text = trim($element->text());

                    $babaName = "";
                    $babaCode = "";
                    foreach(config("const.BAMEI_NAME") as $key => $val){
                        if(strpos($text, $val) !== false){
                            $babaCode = $key;
                            $babaName = $val;
                            break;
                        }
                    }

                    $kaisai = new KaisaiData();
                    $kai = preg_replace("/[^0-9]/", "", explode($babaName, $text)[0]);
                    $hi = preg_replace("/[^0-9]/", "", explode($babaName, $text)[1]);
                    $kaisai->id = substr($strYmd, 0, 4) . $babaCode . substr("00" . $kai, -2) . substr("00" . $hi, -2);
                    $kaisai->name = sprintf("%d回 %s %d日目", $kai, $babaName, $hi);
                    $kaisai->babaCode = $babaCode;
                    $kaisai->babaName = config("const.BAMEI_NAME")[$babaCode];
                    $kaisai->raceCount = 12;
                    $rtnArray[] = $kaisai;
                }
            }
        }
        catch(\Exception $err){
            return [];
        }

        return $rtnArray;
    }

    /**
     * Netkeibaから地方競馬の開催情報をスクレイピング
     */
    private function _scrapingRegion($strYmd) : array
    {
        $rtnArray = [];

        try {
            $link = "https://nar.netkeiba.com/top/race_list_sub.html?kaisai_date=";
            $html = file_get_contents($link . date("Ymd", strtotime($strYmd)));

            $doc = phpQuery::newDocument($html);
            $cnt = count($doc->find("p.RaceList_DataTitle"));
            if($cnt > 0){
                for($i=0; $i<$cnt; $i++) {
                    $element = $doc->find("p.RaceList_DataTitle:eq(" . $i . ")");
                    $text = trim($element->text());

                    $babaName = "";
                    $babaCode = "";
                    foreach(config("const.BAMEI_NAME") as $key => $val){
                        if(strpos($text, $val) !== false){
                            $babaCode = $key;
                            $babaName = $val;
                            break;
                        }
                    }

                    $kaisai = new KaisaiData();
                    $kaisai->id = substr($strYmd, 0, 4) . $babaCode . date("md", strtotime($strYmd));
                    $kaisai->name = $babaName . " " . date("n月j日", strtotime($strYmd)) . "(" . config("const.WEEK_SHORT_NAME")[date("w", strtotime($strYmd))] . ")";
                    $kaisai->babaCode = $babaCode;
                    $kaisai->babaName = config("const.BAMEI_NAME")[$babaCode];
                    $kaisai->raceCount = count($doc->find(".RaceList_Data:eq(" . $i . ") .RaceList_DataItem")->elements);
                    $rtnArray[] = $kaisai;
                }
            }
        }
        catch(\Exception $err){
            return [];
        }

        return $rtnArray;
    }

}