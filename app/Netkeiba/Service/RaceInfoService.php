<?php
namespace App\Netkeiba\Service;
use App\Models\RaceData;
use App\Models\ShutsubaData;
use App\Netkeiba\Scraper\RaceInfoScraper;
use App\Infrastructure\JsonFileStorage;


/**
 * レース情報管理クラス
 */
class RaceInfoService
{
    private JsonFileStorage $storage;

    public function __construct()
    {
        $this->storage = new JsonFileStorage();
    }

    /**
     * スクレイピングをキック
     */
    public function fetch($raceId)
    {
        $scraper = new RaceInfoScraper();
        $race = $scraper->Scraping($raceId);

        if($race != null) {
            // データに書き込み
            $this->storage->saveRace($raceId . ".json", $race);

            // 競走馬情報を取得
            $srvHorse = new HorseInfoService();
            foreach($race->shutsubaArray as $shutsuba)
            {
                $srvHorse->fetch($shutsuba->horseId);
            }

            // 親の競走馬情報を取得
            $this->getParentHorseData($race);
        }
    }

    /**
     * 親の競走馬情報取得
     */
    private function getParentHorseData(RaceData $race) {
        $srvHorse = new HorseInfoService();

        $prtIds = [];
        foreach($race->shutsubaArray as $shutsuba){
            $horseData = $srvHorse->GetHorseData($shutsuba->horseId);
            if (!in_array($horseData->dadId, $prtIds)) $prtIds[] = $horseData->dadId;
            if (!in_array($horseData->dadSohuId, $prtIds)) $prtIds[] = $horseData->dadSohuId;
            if (!in_array($horseData->dadSoboId, $prtIds)) $prtIds[] = $horseData->dadSoboId;
            if (!in_array($horseData->mamId, $prtIds)) $prtIds[] = $horseData->mamId;
            if (!in_array($horseData->mamSohuId, $prtIds)) $prtIds[] = $horseData->mamSohuId;
            if (!in_array($horseData->mamSoboId, $prtIds)) $prtIds[] = $horseData->mamSoboId;
        }
        if(count($prtIds) > 0) {
            foreach($prtIds as $phorseId)
            {
                $srvHorse->fetch($phorseId);
            }
        }
    }

    /**
     * レース情報取得
     */
    public function GetRaceData($raceId) : RaceData | null {
        $json = $this->storage->getRace($raceId . ".json");
        if($json == null) {
            return null;
        }
        $race = new RaceData();
        $race->setJsonData($json);
        return $race;
    }

}
