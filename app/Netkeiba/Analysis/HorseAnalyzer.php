<?php
namespace App\Netkeiba\Analysis;
use App\Models\AnalysisScore;
use App\Models\HorseData;
use App\Models\HorseHistory;


class HorseAnalyzer
{
    private int $version = 2;


    /**
     * 競走馬能力分析
     */
    public function analyze(HorseData $horse): bool
    {
        if($horse == null) return false;
        if($horse->analysisScore != null) {
            if($horse->analysisScore->version == $this->version) return false;
        }

        // タイプ判定
        $type = $this->typeAnalyze($horse->recodeArray);
        // 距離適性を判定
        $distance = $this->distanceAnalyze($horse->recodeArray, $type);

        // 計算結果を格納
        if($horse->analysisScore == null) {
            $horse->analysisScore = new AnalysisScore();
        }
        $horse->analysisScore->version = $this->version;
        $horse->analysisScore->speed = $type["speed"];
        $horse->analysisScore->power = $type["power"];
        $horse->analysisScore->stamina = $type["stamina"];

        $horse->analysisScore->sprint = $distance["sprint"];
        $horse->analysisScore->mile   = $distance["mile"];
        $horse->analysisScore->middle = $distance["middle"];
        $horse->analysisScore->long   = $distance["long"];
        return true;
    }

    /**
     * 競走馬タイプ分析
     */
    private function typeAnalyze(array $histories): array
    {
        $total = ["speed" => 0.0, "power" => 0.0, "stamina" => 0.0];
        $weightSum = 0.0;

        foreach ($histories as $h) {
            $raceScore = $this->raceAnalyze($h);
            $importance = $this->raceImportance($h);

            foreach ($total as $k => $_) {
                $total[$k] += $raceScore[$k] * $importance;
            }
            $weightSum += $importance;
        }

        if ($weightSum > 0) {
            foreach ($total as $k => $v) {
                $total[$k] = round($v / $weightSum, 3);
            }
        }

        return $total;
    }

    /**
     * 1レース能力評価
     */
    private function raceAnalyze(HorseHistory $h): array
    {
        $base = $this->distanceBaseScore($h->distance);
        $gradeW = $this->gradeWeight($h->raceGrade);
        $paceW = $this->paceModifier($h->paceKbn);
        $agariW = $this->agariModifier($h->agariRank);
        $resultW = $this->resultModifier($h->rankNo, (float)$h->difference);

        return [
            "speed" => round($base["speed"] * $paceW["speed"] * $agariW * $gradeW * $resultW, 3),
            "power" => round($base["power"] * $paceW["power"] * $gradeW * $resultW, 3),
            "stamina" => round($base["stamina"] * $paceW["stamina"] * $gradeW * $resultW, 3),
        ];
    }

    /**
     * 距離ベーススコア
     */
    private function distanceBaseScore(int $distance): array
    {
        if ($distance <= 1400)
        {
            return ["speed" => 3.0, "power" => 1.0, "stamina" => 0.5];
        }
        elseif ($distance <= 1800)
        {
            return ["speed" => 2.0, "power" => 2.0, "stamina" => 1.0];
        }
        elseif ($distance <= 2400)
        {
            return ["speed" => 1.0, "power" => 2.5, "stamina" => 2.0];
        }
        else {
            return ["speed" => 0.5, "power" => 2.0, "stamina" => 3.5];
        }
    }

    /**
     * レースグレード別重み(地方は未対応)
     */
    private function gradeWeight(string $grade): float
    {
        return match ($grade) {
            "G1" => 2.0,
            "G2" => 1.8,
            "G3" => 1.6,
            "リステッド" => 1.4,
            "オープン" => 1.3,
            "３勝クラス" => 1.2,
            "２勝クラス" => 1.1,
            "１勝クラス" => 1.0,
            "未勝利" => 0.7,
            default => 1.0
        };
    }

    /**
     * ペース別の重み
     */
    private function paceModifier(?string $paceKbn): array
    {
        return match ($paceKbn) {
            "H" => ["speed" => 1.2, "power" => 1.1, "stamina" => 0.9],
            "M" => ["speed" => 1.0, "power" => 1.0, "stamina" => 1.0],
            "S" => ["speed" => 0.9, "power" => 1.0, "stamina" => 1.2],
            default => ["speed" => 1.0, "power" => 1.0, "stamina" => 1.0],
        };
    }

    /**
     * 上がり別の重み
     */
    private function agariModifier(?int $agariRank): float
    {
        if ($agariRank === null) return 1.0;
        if ($agariRank <= 3) return 1.3;
        return 1.0;
    }

    /**
     * 着順別の重み
     */
    private function resultModifier(string $rankNo, float $difference): float
    {
        if(!is_numeric($rankNo)) return 0.85;

        $rank = is_numeric($rankNo) ? (int)$rankNo : null;
        if ($rank === 1) return 1.3;
        if ($rank <= 3) return 1.1;
        if ($difference <= 0.5) return 1.0;
        return 0.85;
    }

    /**
     * 親能力評価用の重要度
     */
    private function raceImportance(HorseHistory $h): float
    {
        $grade = $this->gradeWeight($h->raceGrade);
        $distance = ($h->distance <= 1200 || $h->distance >= 2400) ? 1.1 : 1.0;
        $winBonus = ($h->rankNo === 1) ? 1.2 : 1.0;

        return $grade * $distance * $winBonus;
    }


    /**
     * 距離的の分析
     */
    public function distanceAnalyze(array $histories, array $type): array
    {
        $totalScore = [
            "sprint" => 0.0,
            "mile"   => 0.0,
            "middle" => 0.0,
            "long"   => 0.0,
        ];

        $weightSum = [
            "sprint" => 0.0,
            "mile"   => 0.0,
            "middle" => 0.0,
            "long"   => 0.0,
        ];

        foreach ($histories as $h) {
            // 距離区分
            $kbn = $this->getDistanceKbn($h->distance);

            // タイプから距離に
            $distanceScore = $this->typeToDistanceScore($type);

            // レース等級による重み
            $importance = $this->gradeWeight($h->raceGrade);
            // 着順による加算
            $importance *= ($h->rankNo <= 3) ? 1.1 : 0.9;

            $totalScore[$kbn] = $distanceScore[$kbn] * $importance;
            $weightSum[$kbn] += $importance;
        }

        // 正規化（0〜1）
        foreach ($totalScore as $k => $v) {
            if ($weightSum[$k] > 0) {
                $totalScore[$k] = round($v / $weightSum[$k], 3);
            }
        }

        return $totalScore;
    }

    /**
     * 距離区分の取得
     */
    protected function getDistanceKbn(int $distance): string
    {
        if ($distance <= 1400) return "sprint";
        if ($distance <= 1800) return "mile";
        if ($distance <= 2400) return "middle";
        return "long";
    }

    /**
     * 能力タイプから距離適性スコアに変換
     */
    protected function typeToDistanceScore(array $type): array
    {
        return [
            "sprint" => $type["speed"] * 1.0 + $type["power"] * 0.4,
            "mile"   => $type["speed"] * 0.7 + $type["power"] * 0.6,
            "middle" => $type["power"] * 0.8 + $type["stamina"] * 0.6,
            "long"   => $type["stamina"] * 1.0 + $type["power"] * 0.4,
        ];
    }


}