@extends('common.base')
@section('pageTitle', "Chrome拡張機能")


@section('pageContents')
    @include('common.menu-header', ['title' => 'Chrome拡張機能'])

    <div class="p-4">
        <div class="fs-4">JRAレース動画リンク拡張機能のインストール</div>
        <div class="fs-6">本サイトのJRAレース動画リンクから、JRAサイトのレース動画へジャンプすることが可能となります</div>

        <div class="fs-5 mt-3">手順１</div>
        <div class="ps-4 fs-6">
            {{ Form::open(['route' => 'extension.download', 'method' => 'get', 'name' => 'frmExDl']) }}
                拡張機能のZIPファイルを
                <a href="javascript:frmExDl.submit()">ダウンロード</a>
                する
            {{ Form::close() }}
        </div>

        <div class="fs-5 mt-3">手順２</div>
        <div class="ps-4 fs-6">
            ダウンロードしたファイル解凍する
        </div>

        <div class="fs-5 mt-3">手順３</div>
        <div class="ps-4 fs-6">
            <div>１．Chromeのメニューから [拡張機能 ＞ 拡張機能を管理]をクリックする</div>
            <div>２．画面右上のデベロッパーモードをONにする</div>
            <div>３．画面左上の「パッケージ化されていない拡張機能を読み込む」ボタンを押下する</div>
            <div>４．解凍したファイル（manifest.json、content.js）が格納されているフォルダを選択する</div>
        </div>

        <div class="fs-5 mt-3">確認</div>
        <div class="ps-4 fs-6">
            <div>下記のリンクを押下してJRAのレース動画へ遷移すれば完了です</div>
            <a href="{{str_replace('{raceid}', '202305050812', config('const.JRA_MOVIE_PAGE'))}}" target="_target">確認する</a>
        </div>

    </div>
@endsection


@section('pageJs')
@endsection


@section('pageCss')
<style>
    .passive {
        display: none;
    }
</style>
@endsection
