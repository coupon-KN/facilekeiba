<?php
namespace App\Netkeiba;
use Illuminate\Support\Facades\Storage;


/**
 * 競走馬メモ管理クラス
 */
class ClsHorseMemo
{
    private $MEMO_DIR = 'memo/';

    /**
     * 競走馬メモを取得
     */
    public function GetMemo($userNo, $horseId): string {
        $targetPath = $this->MEMO_DIR . $userNo;
        // ユーザーフォルダがあるか？
        if (!Storage::disk('public')->exists($targetPath)) return "";
        // ファイルがあるか？
        $targetPath .= "/" . substr($horseId, 0, 4) . ".json";
        if (!Storage::disk('public')->exists($targetPath)) return "";
        //　読み込み
        $contents = Storage::disk('public')->get($targetPath);
        $json = json_decode($contents, true);

        if(!array_key_exists($horseId, $json)) return "";

        return $json[$horseId];
    }

    /**
     * レースメモを取得
     */
    public function GetRaceMemo($userNo, $raceId): string {
        $targetPath = $this->MEMO_DIR . $userNo;
        // ユーザーフォルダがあるか？
        if (!Storage::disk('public')->exists($targetPath)) return "";
        // ファイルがあるか？
        $targetPath .= "/race.json";
        if (!Storage::disk('public')->exists($targetPath)) return "";
        //　読み込み
        $contents = Storage::disk('public')->get($targetPath);
        $json = json_decode($contents, true);

        if(!array_key_exists($raceId, $json)) return "";

        return $json[$raceId];
    }

    /**
     * 競走馬のメモを書き込み
     */
    public function SetMemo($userNo, $horseId, $memo) {
        $fileJson = [];
        $targetPath = $this->MEMO_DIR . $userNo;
        // ユーザーフォルダがあるか？
        if (!Storage::disk('public')->exists($targetPath)) {
            Storage::disk('public')->makeDirectory($targetPath);
        }
        // ファイルがあるか？
        $targetPath .= "/" . substr($horseId, 0, 4) . ".json";
        if (Storage::disk('public')->exists($targetPath)) {
            $contents = Storage::disk('public')->get($targetPath);
            $fileJson = json_decode($contents, true);
        }
        $fileJson[$horseId] = $memo;

        $contents = json_encode($fileJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put($targetPath,  $contents);
    }

    /**
     * レースメモを書き込み
     */
    public function SetRaceMemo($userNo, $raceId, $memo) {
        $fileJson = [];
        $targetPath = $this->MEMO_DIR . $userNo;
        // ユーザーフォルダがあるか？
        if (!Storage::disk('public')->exists($targetPath)) {
            Storage::disk('public')->makeDirectory($targetPath);
        }
        // ファイルがあるか？
        $targetPath .= "/race.json";
        if (Storage::disk('public')->exists($targetPath)) {
            $contents = Storage::disk('public')->get($targetPath);
            $fileJson = json_decode($contents, true);
        }
        $fileJson[$raceId] = $memo;

        $contents = json_encode($fileJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put($targetPath,  $contents);
    }
}
