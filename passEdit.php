<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('　パスワード編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');
// ユーザーパスワード取得
$userPass = getUserPass($_SESSION['user_id']);
// ユーザーデータ取得
$userData = getUser($_SESSION['user_id']);
debug(print_r($userData, true));
if (!empty($_POST)) {
    debug('POST送信があります');
    debug('POST情報：' . print_r($_POST, true));
    //未入力チェック
    validRequired($_POST['pass_old'], 'pass_old');
    validRequired($_POST['pass_new'], 'pass_new');
    validRequired($_POST['pass_new_re'], 'pass_new_re');

    if (empty($err_msg)) {
        debug('未入力チェックOKです');
        $pass_old = $_POST['pass_old'];
        $pass_new = $_POST['pass_new'];
        $pass_new_re = $_POST['pass_new_re'];
        //パスワード形式チェック
        validPass($pass_old, 'pass_old');
        validPass($pass_new, 'pass_new');

        if (empty($err_msg)) {
            debug('パスワード形式チェックOKです');
            // 再入力チェック
            validMatch($pass_new, $pass_new_re, 'pass_new_re');
            // 古いパスワードがあっているかチェック
            if (!password_verify($pass_old, $userPass['password'])) {
                $err_msg['pass_old'] = MSG14;
            }
            // 古いパスワードと新しいパスワードが同じでないかチェック
            if ($pass_old === $pass_new) {
                $err_msg['pass_old'] = MSG13;
            }

            if (empty($err_msg)) {
                debug('バリデーションチェックOKです');

                try {
                    $dbh = dbConnect();
                    $sql = 'UPDATE users SET password = :pass WHERE id = :u_id AND delete_flg = 0';
                    $data = array(':pass' => password_hash($pass_new, PASSWORD_DEFAULT), ':u_id' => $userData['id']);

                    $stmt = queryPost($dbh, $sql, $data);
                    if ($stmt) {
                        debug('パスワードを変更しました');
                        // メール送信
                        $username = (!empty($userData['name'])) ? $userData['name'] : '名無し';
                        $to = $userData['email'];
                        $from = 'naooooo@gmail.com';
                        $subject = 'パスワード変更通知 | FREECLIMBING';
                        $comment = <<<EOT
{$username}さん
パスワードが変更されました。

//////////////////////////////
FREECLIMBINGカスタマーセンター
E-mail {$from}
//////////////////////////////
EOT;

                        sendMail($to, $from, $subject, $comment);

                        $_SESSION['msg_success'] = SUC01;
                        debug('セッション変数の中身：' . print_r($_SESSION, true));
                        debug('マイページへ遷移します');
                        header('Location:mypage.php');
                        debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
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
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$title = 'パスワード編集';
require('head.php');
?>
<?php
require('header.php');
?>
<div class="contents site-width">
    <section id="login">
        <h2 class="title">パスワード変更</h2>
        <form action="" method="post" class="form">
            <div class="area-msg">
                <?php echo getErrMsg('common'); ?>
            </div>
            古いパスワード
            <label for="">
                <input type="password" name="pass_old" value="<?php echo getFormData('pass_old'); ?>" class="js-form-required <?php if (!empty(getErrMsg('pass_old'))) echo 'err'; ?>">
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('pass_old'); ?>
            </div>
            新しいパスワード
            <label for="">
                <input type="password" name="pass_new" value="<?php echo getFormData('pass_new'); ?>" class="js-form-required <?php if (!empty(getErrMsg('pass_new'))) echo 'err'; ?>">
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('pass_new'); ?>
            </div>
            新しいパスワード（再入力）
            <label for="">
                <input type="password" name="pass_new_re" value="<?php echo getFormData('pass_new_re'); ?>" class="js-form-required <?php if (!empty(getErrMsg('pass_new_re'))) echo 'err'; ?>">
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('pass_new_re'); ?>
            </div>
            <div class="btn">
                <input type="submit" name="submit" value="変更" class="js-disabled-submit" disabled="disabled">
            </div>
            <a href="mypage.php">&lt&lt マイページへ戻る</a>
        </form>
    </section>
</div>
<?php
require('footer.php')
?>