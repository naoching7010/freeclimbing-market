<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' ログインページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
// ログイン認証
require('auth.php');

if (!empty($_POST)) { //POST送信があった場合
    debug('POST送信があります');
    debug('POST情報の中身：' . print_r($_POST, true));

    $email = $_POST['email'];
    $pass = $_POST['pass'];

    //Email形式チェック
    validEmail($email, 'email');
    //パスワードチェック
    validPass($pass, 'pass');

    if (empty($err_msg)) {
        debug('バリデーションチェックOKです');

        try {
            debug('db接続します');
            $dbh = dbConnect();
            $sql = 'SELECT password, id FROM users WHERE email = :email AND delete_flg = 0';
            $data = array(':email' => $email);

            $stmt = queryPost($dbh, $sql, $data);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!empty($result) && password_verify($pass, array_shift($result))) {
                debug('入力されたパスワードと登録されているパスワードが一致しました');

                $one_hour = 60 * 60;
                // セッションにユーザーIDをセッションに入れる
                $_SESSION['user_id'] = array_shift($result);
                // 最終ログイン日時タイムスタンプをセッションに入れる
                $_SESSION['login_date'] = time();

                if (empty($_POST['checkbox'])) { // ログイン保持にチェックがなかった場合
                    // ログイン期限を1時間とする
                    $_SESSION['login_limit'] = $one_hour;
                } else { // ログイン保持にチェックがあった場合
                    // ログイン期限を30日とする
                    $_SESSION['login_limit'] = $one_hour * 24 * 30;
                }
                debug('セッション変数の中身：' . print_r($_SESSION, true));
                debug('マイページ遷移します');
                header("Location:mypage.php");
                debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
                exit();
            } else {
                $err_msg['common'] = MSG09;
            }
        } catch (Exception $e) {
            error_log('エラー発生；' . $e->getMessage());
            $err_msg['common'] = MSG08;
        }
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$title = "ログイン";
require('head.php');
?>
<?php
require('header.php');
?>
<p class="msg-success js-fade-msg" style="display: none;">
    <?php
    getSessionFlash('msg_success');
    ?>
</p>
<div class="contents site-width">
    <section id="login">
        <h2 class="title">ログイン</h2>
        <form action="" method="post" class="form">
            <div class="area-msg">
                <?php echo getErrMsg('common'); ?>
            </div>
            メールアドレス
            <label for="">
                <input type="text" name="email" class="js-form-required <?php if (!empty(getErrMsg('email'))) echo 'err'; ?>" value="<?php echo getFormData('email'); ?>">
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('email'); ?>
            </div>
            パスワード
            <label for="">
                <input type="password" name="pass" class="js-form-required <?php if (!empty(getErrMsg('pass'))) echo 'err'; ?>" value="<?php echo getFormData('pass'); ?>" autocomplete="off">
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('pass'); ?>
            </div>
            <label id="login-retention" style="margin-top: 10px;">
                <input type="checkbox" name="checkbox" value="1">ログイン状態を保持する
            </label>
            <div class="btn">
                <input type="submit" name="submit" value="ログイン" class="js-disabled-submit" disabled="disabled">
            </div>
            パスワードを忘れた方は<a href="passRemindSend.php" style="color: #4EA1E8;">コチラ</a>
        </form>
    </section>
</div>
<?php
require('footer.php')
?>