<?php
namespace App\Netkeiba\Service;
use App\Models\HorseData;
use App\Models\HorseHistory;
use App\Netkeiba\Scraper\HorseInfoScraper;
use App\Netkeiba\Analysis\HorseAnalyzer;
use App\Infrastructure\JsonFileStorage;


/**
 * 競走馬情報管理クラス
 */
class HorseInfoService
{
    private JsonFileStorage $storage;

    public function __construct()
    {
        $this->storage = new JsonFileStorage();
    }

    /**
     * スクレイピングをキック
     */
    public function fetch($horseId)
    {
        $scraper = new HorseInfoScraper();

        $hoseData  = $this->GetHorseData($horseId);
        $hoseData = $scraper->Scraping($horseId, $hoseData);

        if($hoseData != null) {
            // 保存
            $this->storage->saveHorse($horseId, $hoseData);
        }
    }

    /**
     * 競走馬情報取得
     */
    public function GetHorseData($horseId) : HorseData | null {
        $json = $this->storage->getHorse($horseId);
        if($json == null) {
            return null;
        }

        $horse = new HorseData();
        $horse->setJsonData($json);

        // 分析
        $analyzer = new HorseAnalyzer();
        if($analyzer->analyze($horse))
        {   // 分析したら保存
            $this->storage->saveHorse($horseId, $horse);
        }

        return $horse;
    }

}
