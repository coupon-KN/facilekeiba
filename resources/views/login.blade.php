@extends('common.base')
@section('pageTitle', 'facilekeiba')

@section('pageJs')
@endsection

@section('pageContents')
    <div class="w-100" style="position: relative;">
        <header>
            <img src="/img/pcbg_icon.png" alt="" />
            <b>facile keiba</b>
            <img src="/img/pcbg_icon.png" alt="" />
        </header>
        <div class="mx-auto" style="width:300px;">
            @if(!is_null($system_msg))
                <div class="mt-3 text-success">{{ $system_msg }}</div>
            @endif

            {{ Form::open(['route' => 'login.login', 'method' => 'post']) }}
            <div class="pt-4">
                <label class="form-label w-100">ID
                    <input type="text" name="user_id" class="form-control" />
                </label>
                <label class="form-label w-100">パスワード
                    <input type="password" name="password" class="form-control" />
                </label>
            </div>
            <div class="pt-4">
                <button type="submit" class="btn btn-primary btn-lg w-100">ログイン</button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection


@section('pageCss')
<style>
    header {
        height: 70px;
        background-color: #c3c3c3;
        text-align: center;
        font-family: "Nico Moji";
    }
    header img {
        width: 60px;
        height: 60px;
    }

    header b {
        font-size: 42px;
        color: white;
        vertical-align: middle;
    }
</style>
@endsection
