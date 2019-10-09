<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('　パスワード再発行ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if (empty($_SESSION['auth_key'])) {
    debug('認証キーが発行されていません');
    debug('認証キー発行ページへ遷移します');
    header('Location:passRemindSend.php');
    exit();
}

if (!empty($_POST)) {
    debug('POST送信があります');
    debug('POST情報：' . print_r($_SESSION, true));
    //未入力チェック
    validRequired($_POST['auth_key'], 'auth_key');

    if (empty($err_msg)) {
        debug('未入力チェックOKです');
        $auth_key = $_POST['auth_key'];
        //半角チェック
        validHalf($auth_key, 'auth_key');
        //文字数チェック
        validLength($auth_key, 'auth_key');

        if (empty($err_msg)) {
            debug('半角・文字数チェックOKです');
            // 入力された認証キーがあっているかチェック
            if ($auth_key !== $_SESSION['auth_key']) {
                $err_msg['auth_key'] = MSG17;
            }
            // 認証キーの有効時間をオーバーしていないかチェック
            if ($_SESSION['auth_limit'] < time()) {
                $err_msg['auth_key'] = MSG18;
            }

            if (empty($err_msg)) {
                debug('バリデーションチェックOK');
                debug('パスワードを再発行します');
                // 新しいパスワードを発行
                $pass = makeRandKey();

                try {
                    $dbh = dbConnect();
                    $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
                    $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));

                    $stmt = queryPost($dbh, $sql, $data);
                    if ($stmt) {
                        debug('DB登録成功');
                        // メール送信
                        $to = $_SESSION['auth_email'];
                        $from = 'naooooo@gmail.com';
                        $subject = '【パスワード再発行完了】| FREECLIMBING';
                        $comment = <<<EOT
本メールアドレス宛にパスワードの再発行をいたしました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。

ログインページ：http://localhost:8888/portfolio02/login.php
再発行パスワード：{$pass}
※ログイン後パスワードのご変更をお願いいたします。

//////////////////////////////////////////
FREECLIMBINGカスタマーセンター
E-mail naooooo@gamil.com
//////////////////////////////////////////
EOT;

                        sendMail($to, $from, $subject, $comment);

                        session_unset();

                        $_SESSION['msg_success'] = SUC03;
                        debug('セッション変数の中身；' . print_r($_SESSION, true));
                        debug('ログインページへ遷移します');
                        header('Location:login.php');
                        debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
                        exit();
                    } else {
                        $err_msg['common'] = MSG08;
                    }
                } catch (Exception $e) {
                    error_log('エラー発生：' . $e->getMessage());
                }
            }
        }
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$title = 'パスワード再発行';
require('head.php');
?>
    <body class="page-1colum">
    <?php
    require('header.php');
    ?>
    <p class="msg-success js-fade-msg" style="display: none;">
        <?php
        getSessionFlash('msg_success');
        ?>
    </p>
    <div id="contents" class="site-width">
        <section id="main">
            <form action="" method="post" class="form">
                <h2 class="page-title">パスワード再発行</h2>
                <p>
                    受け取られた8桁の認証キーをご入力ください。
                </p>
                <div class="area-msg">
                    <?php echo getErrMsg('common'); ?>
                </div>
                認証キー
                <label for="" class="<?php if (!empty(getErrMsg('auth_key'))) echo 'err'; ?>">
                    <input type="text" name="auth_key" value="<?php echo getFormData('auth_key'); ?>" class="js-form-required">
                </label>
                <div class="area-msg">
                        <?php echo getErrMsg('auth_key'); ?>
                </div>
                <div class="btn-container">
                    <input type="submit" name="submit" value="パスワードを再発行する" class="btn js-disabled-submit" disabled="disabled">
                </div>
                <a href="passRemindSend.php">&lt&lt 認証キー発行画面へ戻る</a>
            </form>
        </section>
    </div>
    <?php
    require('footer.php')
    ?>