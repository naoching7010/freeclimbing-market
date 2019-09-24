<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' マイページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

$u_id = $_SESSION['user_id'];
//出品商品の情報を取得する
$saleProductData = getSaleProduct($u_id);
//掲示板とメッセージの情報を取得する
$bordAndMsgData = getBordAndMsg($u_id);
//お気に入りに登録している商品の情報を取得する
$likeProductData = getLikeProduct($u_id);
debug('取得した出品情報：' . print_r($saleProductData, true));
debug('取得した掲示板情報：' . print_r($bordAndMsgData, true));
debug('取得したお気に入り情報：' . print_r($likeProductData, true));

//相手のユーザIDを取り出す
if (!empty($bordAndMsgData)) {
    $dealUserId = array();
    $dealUserId[] = $bordAndMsgData[0]['sale_user'];
    $dealUserId[] = $bordAndMsgData[0]['buy_user'];
    if (($key = array_search($_SESSION['user_id'], $dealUserId)) !== false) {
        unset($dealUserId[$key]);
    }
    $partnerUserId = array_shift($dealUserId);
    //相手のユーザ情報えお取得する
    $papartnerUserData = getUser($partnerUserId);
}
debug('画面処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$title = "マイページ";
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
    <section id="mypage-wrapper">
        <h1 class="title">マイページ</h1>
        <div class="mypage-contents">
            <div class="buy-item">
                <h2 class="title-sub">出品商品一覧</h2>
                <div id="buy-itemList">
                    <?php foreach ($saleProductData as $key => $val) : ?>
                        <a href="registProduct.php?p_id=<?php echo sanitize($val['id']) ?>" class="img-panel" style="display: block;">
                            <img src="<?php echo sanitize($val['pic1']); ?>" alt="">
                            <a>
                            <?php endforeach; ?>
                </div>
            </div>
            <div id="bord">
                <h2 class="title-sub">掲示板一覧</h2>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 130px;">最終更新日時</th>
                            <th style="width: 110px;">取引相手</th>
                            <th>新規メッセージ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bordAndMsgData as $key => $val) {
                            if (!empty($val['msg'])) {
                                $msg = array_shift($val['msg']);
                                ?>
                                <tr class="js-move-bord" data-href="msg.php?m_id=<?php echo sanitize($msg['bord_id']); ?>">
                                    <td style="width: 130px;"><?php echo date('Y-m-d', strtotime(sanitize($msg['send_date']))); ?></td>
                                    <td style="width: 100px;"><?php echo sanitize($papartnerUserData['user_name']); ?></td>
                                    <td><?php echo sanitize($msg['msg']); ?></td>
                                </tr>
                            <?php } else { ?>
                                <tr class="js-move-bord" data-href="msg.php?m_id=<?php echo sanitize($val['id']); ?>">
                                    <td style="width: 130px;"></td>
                                    <td style="width: 100px;"><?php echo sanitize($papartnerUserData['user_name']); ?></td>
                                    <td>まだメッセージがありません</td>
                                </tr>
                        <?php }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div id="like-item">
                <h2 class="title-sub">お気に入り商品一覧</h2>
                <div id="like-itemList">
                    <?php foreach ($likeProductData as $key => $val) : ?>
                        <a href="productDetail.php?p_id=<?php echo sanitize($val['product_id']); ?>" class="img-panel" style="display: block;">
                            <img src="<?php echo sanitize($val['pic1']); ?>" alt="">
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php
    require('sidebar.php');
    ?>
</div>
<?php
require('footer.php')
?>