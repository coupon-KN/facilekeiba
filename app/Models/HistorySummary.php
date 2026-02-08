<?php
namespace App\Models;

class HistorySummary
{
    /** 前走からの間隔 */
    public $intervalDay;

    /** 好走した中で一番高いクラス等級 */
    public $highestRank;

    /** 距離別スコア配列 */
    public $distanceScores;

    /**  馬場別スコア配列 */
    public $trackScores;

}