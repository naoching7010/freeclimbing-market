<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' 退会ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

if (!empty($_POST)) {
    debug('POST送信があります');
    debug('POST送信の中身：' . print_r($_POST, true));

    try {
        $dbh = dbConnect();
        $sql = 'UPDATE users SET delete_flg = 1 WHERE id = :u_id';
        $data = array(':u_id' => $_SESSION['user_id']);

        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            debug('退会します');
            //セッション削除
            deleteSession();
            debug('セッションID：' . session_id());
            debug('セッション変数の中身：' . print_r($_SESSION, true));

            debug('ログイン画面に遷移します');
            header('Location:login.php');
            debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
            exit();
        } else {
            $err_msg['common'] = MSG08;
        }
    } catch (Exception $e) {
        error_log('エラー発生；' . $e->getMessage());
        $err_msg['common'] = MSG08;
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$title = '退会';
require('head.php');
?>
<?php
require('header.php');
?>
<div class="contents site-width">
    <section id="withdraw">
        <h2 class="title">退会</h2>
        <form action="" method="post" class="form">
            <div class="area-msg">
                <?php echo getErrMsg('common'); ?>
            </div>
            <div class="btn">
                <input type="submit" name="submit" value="退会する" style="margin: 40px 0;" class="js-show-alert">
            </div>
            <a href="mypage.php">&lt&lt マイページへ戻る</a>
        </form>
    </section>
</div>
<?php
require('footer.php')
?>