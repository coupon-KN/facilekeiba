@extends('common.base')
@section('pageTitle', 'パスワード再設定')

@section('pageContents')
    @include('common.menu-header', ['title' => 'パスワード再設定'])

    <div class="w-100" style="position: relative;">
        <div class="mx-auto" style="width:300px;">
            {{ Form::open(['route' => 'passchg.reset', 'method' => 'post']) }}
            <div class="pt-4">
                <label class="form-label w-100">現在のパスワード
                    <input type="password" name="now_password" class="form-control" value="{{ old('now_password') }}" />
                </label>
                <label class="form-label w-100">新しいパスワード
                    <input type="password" name="new_password" class="form-control" value="{{ old('new_password') }}" />
                </label>
                <label class="form-label w-100">新しいパスワード(確認)
                    <input type="password" name="re_password" class="form-control" value="{{ old('re_password') }}" />
                </label>
            </div>
            @if(!is_null($err_msg))
                <div class="text-danger">{{ $err_msg }}</div>
            @endif
            <div class="pt-4">
                <button type="submit" class="btn btn-primary btn-lg w-100">再 設 定</button>
            </div>
            {{ Form::close() }}
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
