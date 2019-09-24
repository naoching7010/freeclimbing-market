<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('　認証キーh発行ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if (!empty($_POST)) {
    debug('POST送信がありました');
    debug('POST情報：' . print_r($_POST, true));
    //未入力チェック
    validRequired($_POST['email'], 'email');

    if (empty($err_msg)) {
        debug('未入力チェックOKです');
        $email = $_POST['email'];
        //email形式チェック
        validEmail($email, 'email');

        if (empty($err_msg)) {
            debug('バリデーションOKです');

            try {
                $dbh = dbConnect();
                $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
                $data = array(':email' => $email);

                $stmt = queryPost($dbh, $sql, $data);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if (array_shift($result)) {
                    debug('メールアドレス登録あり');
                    debug('認証キーを生成します');
                    $auth_key = makeRandKey();
                    // メール送信
                    $to = $email;
                    $from = 'naooooo@gmail.com';
                    $subject = '【パスワード再発行のご案内】| FREECLIMBING';
                    $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証器ーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://localhost:8888/portfolio02/passRemindRecieve.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります

認証キーを再発行されたい場合は下記ページより再度再発行をお願いいたします。
http://localhost:8888/portfolio02/passRemindSend.php

//////////////////////////////////////////
FREECLIMBINGカスタマーセンター
E-mail naooooo@gamil.com
//////////////////////////////////////////
EOT;
                    sendMail($to, $from, $subject, $comment);

                    $_SESSION['auth_key'] = $auth_key;
                    $_SESSION['auth_limit'] = time() + (60 * 30);
                    $_SESSION['auth_email'] = $email;

                    $_SESSION['msg_success'] = SUC02;
                    debug('セッション変数の中身：' . print_r($_SESSION, true));

                    debug('認証キー入力画面に遷移します');
                    header('Location:passRemindReceive.php');
                    debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
                    exit();
                } else {
                    $err_msg['email'] = MSG15;
                }
            } catch (Exception $e) {
                error_log('エラー発生：' . $e->getMessage());
            }
        }
    }
}
debug('画面処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$title = '認証キー発行';
require('head.php');
?>
<?php
require('header.php');
?>
<div class="contents site-width">
    <section id="login">
        <h2 class="title">認証キー発行</h2>
        <form action="" method="post" class="form">
            <p>
                ご入力されたメールアドレス宛にパスワード再発行のための認証キーをお送りします。<br>
            </p>
            <div class="area-msg">
                <?php echo getErrMsg('common'); ?>
            </div>
            メールアドレス
            <label for="">
                <input type="text" name="email" value="<?php echo getFormData('email'); ?>" class="js-form-required <?php if (!empty(getErrMsg('email'))) echo 'err'; ?>">
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('email'); ?>
            </div>
            <div class="btn">
                <input type="submit" name="submit" value="送信" class="js-disabled-submit" disabled="disabled">
            </div>
            <a href="login.php">&lt&lt ログイン画面に戻る</a>
        </form>
    </section>
</div>
<?php
require('footer.php')
?>