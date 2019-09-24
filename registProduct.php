<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('　商品登録・編集ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');
// GETデータを格納
$p_id = !empty($_GET['p_id']) ? $_GET['p_id'] : '';
// 新規登録画面か編集画面か判別用フラグ
$editFlg = (!empty($p_id)) ? true : false;
if (!empty($p_id)) {
    debug('GET送信があります');
    debug('GET情報：' . print_r($_GET, true));
    // 商品データ取得
    $dbFormData = getProductOne($p_id);
    debug('取得した情報：' . print_r($dbFormData, true));
    //データが改ざんされていないかチェック
    if (empty($dbFormData)) {
        debug('不正な値が入力されました');
        debug('マイページへ遷移します');
        header('Location:mypage.php');
        exit();
    }
}
// カテゴリーデータ取得
$categoryData = getCategoryData();
// ブランドデータ取得
$brandData = getBrandData();

if (!empty($_POST)) {
    debug('POST送信があります');
    debug('POST情報：' . print_r($_POST, true));

    $name = $_POST['p_name'];
    $category_id = (int) $_POST['category_id'];
    $brand_id = (int) $_POST['brand_id'];
    $size = $_POST['size'];
    $comment = $_POST['comment'];
    $price = (int) $_POST['price'];
    $postage = $_POST['postage_flg'];

    debug('FILE情報：' . print_r($_FILES, true));
    // 画像をアップロードし、パスを格納
    $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'], 'pic1') : '';
    // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
    $pic1 = (empty($pic1) && !empty(getFormData('pic1'))) ? getFormData('pic1') : $pic1;
    $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'], 'pic2') : '';
    $pic2 = (empty($pic2) && !empty(getFormData('pic2'))) ? getFormData('pic2') : $pic2;
    $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'], 'pic3') : '';
    $pic3 = (empty($pic3) && !empty(getFormData('pic3'))) ? getFormData('pic3') : $pic3;

    if (empty($dbFormData)) {
        //商品名未入力チェック
        validRequired($name, 'name');
        //最大文字数チェック
        validMaxLen($name, 'name');
        //セレクトボックス未入力チェック
        validRequiredSelect($category_id, 'category');
        //セレクトボックス値チェック
        validSelect($category_id, 'category');
        //セレクトボックス未入力チェック
        validRequiredSelect($brand_id, 'brand');
        //セレクトボックス値チェック
        validSelect($brand_id, 'brand');
        //サイズ未入力チェック
        validRequired($size, 'size');
        //最大文字数チェック
        validMaxLen($size, 'size');
        //コメント未入力チェック
        validRequired($comment, 'comment');
        //最大文字数チェック
        validMaxLen($comment, 'comment', 200);
        //価格未入力チェック
        validRequired($price, 'price');
        //半角数字チェック
        validNumber($price, 'price');
        //セレクトボックス値チェック
        validRequiredSelect($postage, 'postage');
        //画像1未入力チェック
        validRequired($pic1, 'pic1');
    } else {
        //商品名バリデーションチェック
        if ($name !== $dbFormData['p_name']) {
            validRequired($name, 'name');
            validMaxLen($name, 'name');
        }
        // カテゴリーバリデーションチェック
        if ($category_id !== $dbFormData['category_id']) {
            validRequiredSelect($category_id, 'category');
            validSelect($category_id, 'category');
        }
        // ブランドバリデーションチェック
        if ($brand_id !== $dbFormData['brand_id']) {
            validRequiredSelect($brand_id, 'brand');
            validSelect($brand_id, 'brand');
        }
        // サイズバリデーションチェック
        if ($size !== $dbFormData['size']) {
            validRequired($size, 'size');
            validMaxLen($size, 'size');
        }
        // 詳細バリデーションチェック
        if ($comment !== $dbFormData['comment']) {
            validRequired($comment, 'comment');
            validMaxLen($comment, 'comment', 200);
        }
        // 価格バリデーションチェック
        if ($price !== $dbFormData['price']) {
            validRequired($price, 'price');
            validNumber($price, 'price');
        }
        // 送料バリデーションチェック
        if ($postage !== $dbFormData['postage_flg']) {
            validRequiredSelect($postage, 'postage');
        }
    }
    if (empty($err_msg)) {
        try {
            $dbh = dbConnect();
            if ($editFlg) { //編集画面の場合
                $sql = 'UPDATE product SET name = :name, category_id = :c_id, brand_id = :b_id, size = :size, comment = :comment, price = :price, postage_flg = :postage, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3, sale_user = :u_id WHERE id = :p_id AND delete_flg = 0';
                $data = array(':name' => $name, ':c_id' => $category_id, 'b_id' => $brand_id, ':size' => $size, ':comment' => $comment, ':price' => $price, ':postage' => $postage, 'pic1' => $pic1, 'pic2' => $pic2, 'pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
            } else { //登録画面の場合
                $sql = 'INSERT INTO product (name, category_id, brand_id, size, comment, price,postage_flg, pic1, pic2, pic3, sale_user, create_date) VALUES (:name, :c_id, :b_id, :size, :comment, :price, :postage, :pic1, :pic2, :pic3,:u_id, :date)';
                $data = array(':name' => $name, ':c_id' => $category_id, 'b_id' => $brand_id, ':size' => $size, ':comment' => $comment, ':price' => $price, ':postage' => $postage, 'pic1' => $pic1, 'pic2' => $pic2, 'pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
            }

            $stmt = queryPost($dbh, $sql, $data);
            if ($stmt) {
                debug('DB登録or編集しました');
                $_SESSION['msg_success'] = SUC05;
                debug('マイページへ遷移します');
                header('Location:mypage.php');
                debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
                exit();
            } else {
                $err_msg['common'] = MSG08;
            }
        } catch (Exception $e) {
            error_log('エラー発生：' . $e->getMessage());
        }
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$title = '商品登録';
require('head.php');
?>
<?php
require('header.php');
?>
<div class="contents site-width">
    <div id="regist-item">
        <h2 class="title"><?php echo (!empty($editFlg)) ? '商品編集' : '商品登録'; ?></h2>
        <form action="" method="post" class="form" enctype="multipart/form-data">
            <div class="area-msg">
                <?php echo getErrMsg('common') ?>
            </div>
            商品名 <span class="msg-required">必須</span>
            <label for="">
                <input type="text" name="p_name" class="<?php if(!empty(getErrMsg('name'))) echo 'err'; ?>" value="<?php echo getFormData('p_name'); ?>">
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('name'); ?>
            </div>
            カテゴリー <span class="msg-required">必須</span>
            <label class="selectbox" for="">
                <span class="select-icon"></span>
                <select name="category_id" id="">
                    <option value="0" <?php if (getFormData('category_id') == 0) echo 'selected'; ?>>
                        選択してください
                    </option>
                    <?php foreach ($categoryData as $key => $value) : ?>
                        <option value="<?php echo $value['id']; ?>" <?php if (getFormData('category_id') == $value['id']) echo 'selected'; ?>>
                            <?php echo $value['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('category'); ?>
            </div>
            ブランド <span class="msg-required">必須</span>
            <label class="selectbox" for="">
                <span class="select-icon"></span>
                <select name="brand_id" id="">
                    <option value="0" <?php if (getFormData('brand_id') == 0) echo 'selected'; ?>>
                        選択してください
                    </option>
                    <?php foreach ($brandData as $key => $value) : ?>
                        <option value="<?php echo $value['id']; ?>" <?php if (getFormData('brand_id') == $value['id']) echo 'selected'; ?>>
                            <?php echo $value['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('brand'); ?>
            </div>
            サイズ <span class="msg-required">必須</span>
            <label for="" style="width: 30%;">
                <input type="text" name="size" class="<?php if(!empty(getErrMsg('size'))) echo 'err'; ?>" value="<?php echo getFormData('size'); ?>">
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('size'); ?>
            </div>
            詳細 <span class="msg-required">必須</span>
            <label for="">
                <textarea name="comment" rows="12" class="js-counter <?php if(!empty(getErrMsg('comment'))) echo 'err'; ?>"><?php echo reverseNl2br(getFormData('comment')); ?></textarea>
                <div id="comment-msgArea">
                    <div class="area-msg">
                        <?php echo getErrMsg('comment'); ?>
                    </div>
                    <div class="counter">
                        <span class="js-show-counter">0</span>/200
                    </div>
                </div>
            </label>
            金額 <span class="msg-required">必須</span>
            <label for="" style="width: 30%;">
                <input type="text" name="price" class="<?php if(!empty(getErrMsg('price'))) echo 'err'; ?>" value="<?php echo (!empty(getFormData('price'))) ? getFormData('price') : 0; ?>">
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('price'); ?>
            </div>
            送料 <span class="msg-required">必須</span>
            <label for="">
                <select name="postage_flg" id="">
                    <option value="0" <?php if ((int) getFormData('postage_flg') === 0) echo 'selected'; ?>>
                        出品者負担
                    </option>
                    <option value="1" <?php if ((int) getFormData('postage_flg') === 1) echo 'selected'; ?>>
                        購入者負担
                    </option>
                </select>
            </label>
            <div class="area-msg">
                <?php echo getErrMsg('postage'); ?>
            </div>
            <div id="img-regist">
                <div class="img-panel">
                    画像1 <span class="msg-required">必須</span>
                    <label class="drop-container js-dropContainer <?php echo (empty(getErrMsg('pic1'))) ? 'drop-container' : 'err'; ?>"">
                        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                        <input type="file" name="pic1" class="file-input">
                        <img src="<?php echo getFormData('pic1'); ?>" alt="" class="prev-img">
                        画像をドラッグ&ドロップ
                    </label>
                </div>
                <div class="img-panel">
                    画像2
                    <label class="drop-container js-dropContainer">
                        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                        <input type="file" name="pic2" class="file-input">
                        <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img">
                        画像をドラッグ&ドロップ
                    </label>
                </div>
                <div class="img-panel">
                    画像3
                    <label class="drop-container js-dropContainer">
                        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                        <input type="file" name="pic3" class="file-input">
                        <img src="<?php echo getFormData('pic3'); ?>" alt="" class="prev-img">
                        画像をドラッグ&ドロップ
                    </label>
                </div>
            </div>
            <div class="area-msg">
                <?php echo getErrMsg('pic1'); ?>
            </div>
            <div class="btn">
                <input type="submit" name="submit" value="<?php echo (!empty($editFlg)) ? '編集する' : '出品する'; ?>">
            </div>
            <a href="mypage.php">&lt&lt マイページへ戻る</a>
        </form>
    </div>
    <?php
    require('sidebar.php');
    ?>
</div>
<?php
require('footer.php')
?>