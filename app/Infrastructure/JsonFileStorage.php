<?php
namespace App\Infrastructure;
use Illuminate\Support\Facades\Storage;


class JsonFileStorage
{
    private $PATH_SCHED = 'schedule/';
    private $PATH_RACE = 'race/';
    private $PATH_HORSE = 'horse/';
    private $PATH_PARENT = 'parent/';

    public function getSchedule(string $fileNm): array|null
    {
        if ($this->exists($this->PATH_SCHED, $fileNm)) {
            return $this->load($this->PATH_SCHED, $fileNm);
        }
        return null;
    }
    public function saveSchedule(string $fileNm, array $data)
    {
        $this->saveArray($this->PATH_SCHED, $fileNm, $data);
    }

    public function getRace(string $fileNm): array|null
    {
        if ($this->exists($this->PATH_RACE, $fileNm)) {
            return $this->load($this->PATH_RACE, $fileNm);
        }
        return null;
    }
    public function saveRace(string $fileNm, object $data)
    {
        $this->saveObject($this->PATH_RACE, $fileNm, $data);
    }

    public function getHorse(string $horseId): array|null
    {
        $path = $this->PATH_HORSE . substr($horseId, 0, 4) . "/";
        $fileNm = $horseId . ".json";
        if ($this->exists($path, $fileNm)) {
            return $this->load($path, $fileNm);
        }
        return null;
    }
    public function saveHorse(string $horseId, object $data)
    {
        $path = $this->PATH_HORSE . substr($horseId, 0, 4) . "/";
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path);
        }
        $this->saveObject($path, $horseId . ".json", $data);
    }

    
    private function exists(string $path, string $fileNm): bool
    {
        return Storage::disk('public')->exists($path . "/" . $fileNm);
    }

    private function load(string $path, string $fileNm): array
    {
        $contents = Storage::disk('public')->get($path . "/" . $fileNm);
        return json_decode($contents, true);
    }

    private function saveObject(string $path, string $fileNm, object $data): void
    {
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path);
        }
        $contents = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put($path . "/" . $fileNm,  $contents);
    }
    private function saveArray(string $path, string $fileNm, array $data): void
    {
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory($path);
        }
        $contents = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put($path . "/" . $fileNm,  $contents);
    }
    
}