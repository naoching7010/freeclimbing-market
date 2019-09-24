<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' ログアウト ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

debug('ログアウトします');

//セッションを削除
deleteSession();
debug('セッションID：' . session_id());
debug('セッション変数の中身：' . print_r($_SESSION, true));

debug('ログインページへ遷移します');
header("Location:login.php");
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>