<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' 会員登録ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if (!empty($_POST)) { // POST送信がある場合
    debug('POST送信があります');
    debug('POST情報：' . print_r($_POST, true));

    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_re = $_POST['pass_re'];

    //email形式チェック
    validEmail($email, 'email');
    //email重複チェック
    validEmailDup($email, 'email');
    //パスワードバリデーションチェック
    validPass($pass, 'pass');

    if (empty($err_msg)) {
        debug('Email形式チェック・重複チェックOKです');
        debug('パスワードチェックOKです');

        //パスワードとパスワード再入力があっているかチェック
        validMatch($pass, $pass_re, 'pass_re');

        if (empty($err_msg)) {
            debug('バリデーションOKです');
            debug('データベースに接続します');
            try {
                $dbh = dbConnect();
                $sql = 'INSERT INTO users (email, password, login_time, create_date) VALUES (:email, :pass, :login_time, :create_date)';
                $data = array(':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':login_time' => date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));

                $stmt = queryPost($dbh, $sql, $data);
                if ($stmt) {
                    debug('db登録ができました');

                    //ユーザーIDをセッションにいれる
                    $_SESSION['user_id'] = $dbh->lastInsertId();
                    //最終ログイン日時タイムスタンプをセッションに入れる
                    $_SESSION['login_date'] = time();
                    //ログイン有効期限をセッションにいれる
                    $_SESSION['login_limit'] = 60 * 60;
                    debug('セッション変数の中身：' . print_r($_SESSION, true));

                    debug('マイページへ遷移します');
                    header("Location:mypage.php");
                    debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
                    exit();
                } else {
                    $err_msg['common'] = MSG08;
                }
            } catch (Exception $e) {
                error_log('エラー発生：' . $e->getMessage());
                $err_msg['common'] = MSG08;
            }
        }
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>



<?php
$title = '会員登録';
require('head.php');
?>
<?php
require('header.php');
?>
<div class="contents site-width">
    <section id="login">
        <h2 class="title">新規会員登録</h2>
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
                <input type="password" name="pass" class="js-form-required <?php if (!empty(getErrMsg('pass'))) echo 'err'; ?>" value="<?php echo getFormData('pass'); ?>">
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('pass'); ?>
            </div>
            パスワード（再入力）
            <label for="">
                <input type="password" name="pass_re" class="js-form-required <?php if (!empty(getErrMsg('pass_re'))) echo 'err'; ?>" value="<?php echo getFormData('pass_re'); ?>">
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('pass_re'); ?>
            </div>
            <div class="btn">
                <input type="submit" name="submit" value="登録" class="js-disabled-submit" disabled="disabled">
            </div>
            <a href="index.php">&lt&lt HOMEへ戻る</a>
        </form>
    </section>
</div>
<?php
require('footer.php')
?>