<?php

require_once 'vendor/autoload.php';

use Carbon\Carbon;

$dateString = '2021年2月18日';

try {
    $date = Carbon::createFromFormat('Y年m月d日', $dateString);
    echo "変換された日付: " . $date->format('Y-m-d') . "\n";
} catch (Exception $e) {
    echo "日付変換エラー: " . $e->getMessage() . "\n";
}

?>