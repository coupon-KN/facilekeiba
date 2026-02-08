<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


/**
 * 拡張機能コントローラ
 */
class ExtensionController extends Controller
{
    /**
     * 初期表示
     */
    function index() {
        return view('extension');
    }

    /**
     * ダウンロード
     */
    function download() {
        $filePath = 'public/archive/JraRaceMovieJumpExtension.zip';
        $fileName = 'JraRaceMovieJumpExtension.zip';
        
        $mimeType = Storage::mimeType($filePath);
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename*=UTF-8\'\''.rawurlencode($fileName)
        ];
        
        return Storage::download($filePath, $fileName, $headers);
    }

}
