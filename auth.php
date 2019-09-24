<?php
// $_SESSION['login_date']の中身があった場合、過去にログインしているとした
if (!empty($_SESSION['login_date'])) {
    debug('ログイン済みユーザーです');
    if ($_SESSION['login_date'] + $_SESSION['login_limit'] > time()) { 
        debug('ログイン期限内です');
        $_SESSION['login_date'] = time();
        debug('セッション変数の中身：' . print_r($_SESSION, true));
    
        if(basename($_SERVER['PHP_SELF']) === 'login.php') {
            debug('マイページページへ遷移します');
            header("Location:mypage.php");
            exit();
        }
    } else {
        debug('ログイン期限オーバーです');

        $_SESSION = array();
        if (isset($_COOKIE["PHPSESSID"])) {
            setcookie("PHPSESSID", '', time() - 1800, '/');
        }
        session_destroy();

        debug('セッションID：' . session_id());
        debug('セッション変数の中身：' . print_r($_SESSION, true));
        debug('ログインページへ遷移します');
        header("Location:login.php");
        exit();
    }
} else {
    debug('未ログインユーザーです');
    if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
        debug('ログインページへ遷移します');
        header("Location:login.php");
        exit();
    }
}
