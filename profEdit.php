<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('　プロフィール編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');
// ユーザー情報取得
$dbFormData = getUser($_SESSION['user_id']);
if (!empty($_POST)) {
    debug('POST送信があります');
    debug('POST情報：' . print_r($_POST, true));
    debug('ファイル情報:' . print_r($_FILES, true));

    $name = (!empty($_POST['user_name'])) ? $_POST['user_name'] : '';
    $birthday = (!empty($_POST['birthday'])) ? $_POST['birthday'] : NULL;
    $zip = (!empty($_POST['zip'])) ? $_POST['zip'] : 0;
    $addr = (!empty($_POST['addr'])) ? $_POST['addr'] : '';
    $tel = (!empty($_POST['tel'])) ? $_POST['tel'] : '';
    $email = (!empty($_POST['email'])) ? $_POST['email'] : '';
    $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
    $pic = (empty($_FILES['pic']['name']) && !empty(getFormData('pic'))) ? getFormData('pic') : $pic;
    // 名前最大文字数チェック
    if ($name !== $dbFormData['user_name']) {
        validMaxLen($name, 'user_name');
    }
    // 生年月日形式チェック
    if ($birthday !== $dbFormData['birthday']) {
        validBirtday($birthday, 'birthday');
    }
    // 郵便番号形式チェック
    if ($zip !== $dbFormData['zip']) {
        validZip($zip, 'zip');
    }
    // 住所最大文字数チェック
    if ($addr !== $dbFormData['addr']) {
        validMaxLen($addr, 'addr');
    }
    // 電話番号形式チェック
    if ($tel !== $dbFormData['tel']) {
        validTel($tel, 'tel');
    }
    // emailチェック
    if ($email !== $dbFormData['email']) {
        validRequired($email, 'email');
        if (empty($err_msg['email'])) {
            validEmail($email, 'email');
            validEmailDup($email, 'email');
            validMaxLen($email, 'email');
        }
    }

    if (empty($err_msg)) {
        debug('バリデーションOKです');

        try {
            $dbh = dbConnect();
            $sql = 'UPDATE users SET user_name = :name, birthday = :birthday, zip = :zip, addr = :addr, tel = :tel, pic = :pic, email = :email WHERE id = :u_id AND delete_flg = 0';
            $data = array('name' => $name, ':birthday' => $birthday, ':zip' => $zip, ':addr' => $addr, ':tel' => $tel, ':pic' => $pic, ':email' => $email, ':u_id' => $_SESSION['user_id']);

            $stmt = queryPost($dbh, $sql, $data);
            if ($stmt) {
                debug('登録or編集成功');
                $_SESSION['msg_success'] = SUC06;
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

?>

<?php
$title = 'プロフィール編集';
require('head.php');
?>
<body class="page-2colum page-logined">
    <?php
    require('header.php');
    ?>
    <div id="contents" class="site-width">
        <section id="main">
            <form action="" method="post" class="form" enctype="multipart/form-data">
                <h2 class="page-title">プロフィール編集</h2>
                <div class="area-msg">
                    <?php echo getErrMsg('common'); ?>
                </div>

                ニックネーム
                <label for="" class="<?php if(!empty(getErrMsg('user_name'))) echo 'err'; ?>">
                    <input type="text" name="user_name" value="<?php echo getFormData('user_name'); ?>">
                </label>
                <div class="area-msg">
                    <?php echo getErrMsg('user_name'); ?>
                </div>

                生年月日
                <label for="" class="<?php if(!empty(getErrMsg('birthday'))) echo 'err'; ?>">
                    <input type="date" name="birthday" id="js-valid-birthday" value="<?php echo getFormData('birthday'); ?>">
                </label>
                <div class="area-msg">
                    <?php echo getErrMsg('birthday'); ?>
                </div>

                郵便番号 ※ハイフンなしで入力してください
                <label for="" class="<?php if(!empty(getErrMsg('zip'))) echo 'err'; ?>">
                    <input type="text" name="zip" value="<?php echo !empty(getFormData('zip')) ? getFormData('zip') : ''; ?>">
                </label>
                <div class="area-msg">
                    <?php echo getErrMsg('zip'); ?>
                </div>

                住所
                <label for="" class="<?php if(!empty(getErrMsg('addr'))) echo 'err'; ?>">
                    <input type="text" name="addr" value="<?php echo getFormData('addr'); ?>">
                </label>
                <div class="area-msg">
                    <?php echo getErrMsg('addr'); ?>
                </div>

                電話番号 ※ハイフンなしで入力してください
                <label for="" class="<?php if(!empty(getErrMsg('tel'))) echo 'err'; ?>">
                    <input type="text" name="tel" value="<?php echo getFormData('tel'); ?>">
                </label>
                <div class="area-msg">
                    <?php echo getErrMsg('tel'); ?>
                </div>

                メールアドレス <span class="msg-required">必須</span>
                <label for="" class="<?php if(!empty(getErrMsg('email'))) echo 'err'; ?>">
                    <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
                </label>
                <div class="area-msg">
                    <?php echo getErrMsg('email'); ?>
                </div>

                <div style="overflow: hidden;">
                    <div class="imgDrop-container">
                        プロフィール画像
                        <label class="area-drop js-dropContainer <?php if(!empty(getErrMsg('pic'))) echo 'err'; ?>">
                            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                            <input type="file" name="pic" class="file-input">
                            <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img">
                            画像をドラッグ&ドロップ
                        </label>
                    </div>
                </div>
                <div class="area-msg">
                    <?php echo getErrMsg('pic'); ?>
                </div>
                <div class="btn-container">
                    <input class="btn" type="submit" name="submit" value="編集する">
                </div>
                <a href="mypage.php">&lt&lt マイページへ戻る</a>
            </form>
        </section>
        <?php
       require('sidebar.php');
        ?>
    </div>
    <?php
    require('footer.php')
    ?>