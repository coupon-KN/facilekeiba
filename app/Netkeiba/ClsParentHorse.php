<?php
namespace App\Netkeiba;
use App\Models\ParentHorse;
use Illuminate\Support\Facades\Storage;
use phpQuery;


/**
 * 親馬情報管理クラス
 */
class ClsParentHorse
{
    private static $instance = null;
    private static $dataArray = [];


    /**
     * コンストラクタ
     */
    private function __construct() {
        $files = Storage::files('public/parent');
        foreach ($files as $path) {
            $filename = str_replace("public/parent/", "", $path);
            $kbn = substr($filename, 0, 3);
            
            $contents = Storage::get($path);
            $this::$dataArray[$kbn] = json_decode($contents, true);
        }
    }

    /**
     * デストラクタ
     */
    public function __destruct() {
        // 書き込み
        foreach ($this::$dataArray as $key => $val) {
            $contents = json_encode($val, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            Storage::disk('public')->put("parent/" . $key . ".json",  $contents);
        }
    }

    /**
     * インスタンスの取得
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new ClsParentHorse();
        }
        return self::$instance;
    }

    /**
     * 馬情報を取得
     */
    public function getHorseData($horseId) : ParentHorse {
        $rtnObj = new ParentHorse();

        $kbn = substr($horseId, 0, 3);
        if(array_key_exists($kbn, $this::$dataArray)){
            if(array_key_exists($horseId, $this::$dataArray[$kbn])){
                $rtnObj->horseId = $horseId;
                $rtnObj->name = $this::$dataArray[$kbn][$horseId]["name"];
                $rtnObj->recode = $this::$dataArray[$kbn][$horseId]["recode"];
                $rtnObj->winRate = $this::$dataArray[$kbn][$horseId]["winRate"];
                $rtnObj->podiumRate = $this::$dataArray[$kbn][$horseId]["podiumRate"];
            }
        }

        try {
            if(empty($rtnObj->horseId)){
                // htmlから取得
                $html = file_get_contents("https://db.netkeiba.com/horse/" . $horseId);
                if(!$html){
                    return new ParentHorse();
                }
                $doc = phpQuery::newDocument($html);
                $rtnObj->horseId = $horseId;
                $rtnObj->name = $doc->find('div.horse_title h1')->text();
                // 名前がない場合は空ページ
                if(empty(trim($rtnObj->name))){
                    return new ParentHorse();
                }
                // 通算成績
                $loop = count($doc->find('div.db_main_deta table.db_prof_table tr')->elements);
                for($j=0; $j<$loop; $j++){
                    $th = $doc->find("div.db_main_deta table.db_prof_table tr:eq(". $j . ") th")->text();
                    if($th == "通算成績"){
                        $strWork = $doc->find("div.db_main_deta table.db_prof_table tr:eq(". $j . ") td a")->text();
                        $rankArr = explode("-",$strWork);
                        $rank1 = intval($rankArr[0]);
                        $rank2 = intval($rankArr[1]);
                        $rank3 = intval($rankArr[2]);
                        $rankEtc = intval($rankArr[3]);
                        $total = $rank1 + $rank2 + $rank3 + $rankEtc;
                        // 成績
                        $rtnObj->recode = sprintf("[%d-%d-%d-%d]", $rank1, $rank2, $rank3, $rankEtc);
                        if($total > 0){
                            $rtnObj->winRate = round($rank1 / $total * 100);
                            $rtnObj->podiumRate = round(($rank1 + $rank2 + $rank3) / $total * 100);
                        }else{
                            $rtnObj->winRate = 0;
                            $rtnObj->podiumRate = 0;
                        }
                        break;
                    }
                }
                // 追加
                $this::$dataArray[$kbn][$horseId] = [
                    "name" => $rtnObj->name,
                    "recode" => $rtnObj->recode,
                    "winRate" => $rtnObj->winRate,
                    "podiumRate" => $rtnObj->podiumRate
                ];
            }
        }
        catch(\Exception $err){
            return new ParentHorse();
        }

        return $rtnObj;
    }

}
