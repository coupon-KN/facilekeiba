<header class="common-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <div class="d-flex flex-row">
                <img src="/img/pcbg_icon.png" alt="" width="32" height="32" />
                <span class="px-2 fs-5">{{$title}}</span>
            </div>

            <div class="navbar-collapse flex-row-reverse">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('calendar.index')}}">レースカレンダー</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('extension')}}">Chrome拡張機能</a>
                    </li>
                    <li class="nav-item dropdown ms-4">
                        <a class="nav-link dropdown-toggle" href="#" id="loginUserDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ session(config('const.SESSION_LOGIN_USER_NAME')) }}さん
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="loginUserDropdownMenuLink">
                            <li>
                                <a class="dropdown-item" href="{{route('passchg')}}">パスワード変更</a>
                            </li>
                            <li>
                                {{ Form::open(['route' => 'logout', 'method' => 'post', 'name' => 'frmLogout']) }}
                                <a class="dropdown-item" href="javascript:frmLogout.submit()">ログアウト</a>
                                {{ Form::close() }}
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>