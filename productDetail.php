<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('　商品詳細ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
$u_id = (!empty($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
$productData = getProductOne($p_id);
debug('取得した情報：' . print_r($productData, true));
// 改ざんチェック
if (empty($productData)) {
    debug('不正な値が入力されました');
    debug('ホームページに移動します');
    header('Location:index.php');
}
debug('取得した情報：' . print_r($productData, true));

//購入ボタンを押した場合
if (!empty($_POST['buy'])) {
    debug('POST送信があります');
    debug('POST情報:' . print_r($_POST, true));
    // ログイン認証
    require('auth.php');

    try {
        $dbh = dbConnect();
        $sql = 'INSERT INTO bord (sale_user, buy_user, product_id, create_date) VALUES (:sale_user, :buy_user, :p_id, :date)';
        $data = array(':sale_user' => $productData['sale_user'], 'buy_user' => $_SESSION['user_id'], 'p_id' => $p_id, ':date' => date('Y-m-d H:i;:s'));

        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            debug('掲示板を作成しました');
            // 最後にDB登録された掲示板IDを変数に格納
            $m_id = $dbh->lastInsertId();
            $dbh = dbConnect();
            $sql = 'UPDATE product SET sale_flg = 1 WHERE id = :p_id AND delete_flg = 0';
            $data = array(':p_id' => $p_id);

            $stmt = queryPost($dbh, $sql, $data);
            if ($stmt) {
                $_SESSION['msg_success'] = SUC04;
                debug('購入処理完了です');
                debug('掲示板へ遷移します');
                header('Location:msg.php?m_id=' . $m_id);
                debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
                exit();
            }
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
// コメント情報の取得
$tradeMsgData = getTradeMsg($p_id);
debug('取得したコメント情報：' . print_r($tradeMsgData, true));
//コメントボタンを押した場合
if (!empty($_POST['submit'])) {
    debug('POST送信があります');
    debug('POST情報：' . print_r($_POST, true));
    // ログイン認証
    require('auth.php');

    $comment = $_POST['comment'];
    // 最大文字数チェック
    validMaxLen($comment, 'comment');
    if (empty($err_msg)) {
        try {
            $dbh = dbConnect();
            $sql = 'INSERT INTO tradeMsg (product_id, from_user, comment, send_date, create_date) VALUES (:p_id, :u_id, :comment, :send_date, :create_date)';
            $data = array(':p_id' => $p_id, ':u_id' => $_SESSION['user_id'], ':comment' => $comment, ':send_date' => date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));

            $stmt = queryPost($dbh, $sql, $data);
            if ($stmt) {
                debug('送信完了');
                debug('商品詳細画面に遷移します');
                header('Location:productDetail.php?p_id=' . $p_id);
                debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
                exit();
            }
        } catch (Exception $e) {
            error_log('エラー発生：' . $e->getMessage());
        }
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$title = '商品詳細';
require('head.php');
?>
<?php
require('header.php');
?>
<div class="contents site-width">
    <section id="product-detail">
        <div id="product-detail-topwrapper">
            <div id="product-title">
                <?php echo sanitize($productData['p_name']);
                ?>
            </div>
            <div id="product-img">
                <div id="main-img" class="js-switch-mainimg">
                    <img src="<?php echo sanitize(showImg($productData['pic1'])); ?>" alt="" class="prev-mainimg">
                </div>
                <div id="sub-img">
                    <div class="img-panel js-switch-subimg">
                        <img src="<?php echo sanitize(showImg($productData['pic1'])); ?>" alt="" class="prev-subimg">
                    </div>
                    <div class="img-panel js-switch-subimg">
                        <img src="<?php echo sanitize(showImg($productData['pic2'])); ?>" alt="" class="prev-subimg">
                    </div>
                    <div class="img-panel js-switch-subimg">
                        <img src="<?php echo sanitize(showImg($productData['pic3'])); ?>" alt="" class="prev-subimg">
                    </div>
                </div>
            </div>
            <div id="product-info">
                <table>
                    <tr>
                        <th>出品者</th>
                        <td><?php echo sanitize($productData['user_name']); ?></td>
                    </tr>
                    <tr>
                        <th>カテゴリー</th>
                        <td><?php echo sanitize($productData['c_name']); ?></td>
                    </tr>
                    <tr>
                        <th>ブランド</th>
                        <td><?php echo sanitize($productData['b_name']); ?></td>
                    </tr>
                    <tr>
                        <th>サイズ</th>
                        <td><?php echo sanitize($productData['size']); ?></td>
                    </tr>
                    <tr>
                        <th>送料</th>
                        <td><?php
                            echo judgPostAge($productData['postage_flg']);
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div id="product-discription">
            <p>
                <?php echo sanitize($productData['comment']); ?>
            </p>
        </div>
        <div id="product-buy">
            <p>￥<?php echo sanitize(number_format($productData['price'])); ?></p>
            <div id="like">
                <i id="js-like-product" class="fas fa-heart fa-3x fav js-like-animation <?php if (isLike($p_id, $_SESSION['user_id'])) echo 'active'; ?>" data-productid="<?php echo $p_id; ?>"></i>
                <i class="fas fa-heart fa-3x fav2 js-like-animation2 <?php if (isLike($p_id, $_SESSION['user_id'])) echo 'active'; ?>"></i>
            </div>
            <form action="" method="post" class="btn" style="clear: left;">
                <input type="submit" name="buy" value="購入する" style="height: 60px; <?php if ($productData['sale_user'] === $_SESSION['user_id']) echo 'display: none;' ?>">
            </form>
            <a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">&lt 前のページへ戻る</a>
        </div>
        <div id="trade-comment-wrapper">
            <div id="trade-comment" style="height: 250px;">
                <?php
                if (!empty($tradeMsgData)) {
                    foreach ($tradeMsgData as $key => $val) {
                        if ($val['from_user'] !== $u_id) {
                            ?>
                            <div class="msg-left">
                                <img src="<?php echo showProfImg(sanitize($val['pic'])); ?>" alt=""><br>
                                <p>
                                    <?php echo sanitize($val['comment']); ?>
                                </p>
                                <span><?php echo sanitize($val['user_name']) ?></span>
                            </div>
                        <?php } else { ?>
                            <div class="msg-right">
                                <img src="<?php echo showProfImg(sanitize($val['pic'])); ?>" alt="" style="float: right;">
                                <p>
                                    <?php echo sanitize($val['comment']); ?>
                                </p>
                                <span><?php echo sanitize($val['user_name']) ?></span>
                            </div>
                    <?php
                            }
                        }
                    } else { ?>
                    <div>
                        <p style="text-align: center; margin-top: 50px; font-size: 20px;">コメントがありません</p>
                    </div>
                <?php } ?>
            </div>
            <form action="" method="post" class="form send-msg" style="padding: 0;">
                <label for="">
                    <textarea name="comment" class="auto-resize js-form-required" placeholder="コメントを入力"></textarea>
                </label>
                <div class="btn" style="width: 300px; margin: 30px auto;">
                    <input type="submit" name="submit" value="コメントする" class="js-disabled-submit" disabled="disabled">
                </div>
            </form>
        </div>

    </section>
</div>
<?php
require('footer.php')
?>