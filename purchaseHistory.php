<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('　購入履歴ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');
// 購入商品データ取得
$productData = getPurchaseProduct($_SESSION['user_id']);
debug('取得した商品情報：' . print_r($productData, true));

debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$title = '購入履歴';
require('head.php');
?>
<?php
require('header.php');
?>
<div class="contents site-width">
    <section id="purchaseHistory">
        <h2 class="title">購入履歴</h2>
        <div class="purchase-product">
            <?php foreach ($productData as $key => $val) : ?>
                <div class="purchasePanel-head">
                    <p>
                        <?php
                            echo '購入日<br>';
                            echo sanitize(date('Y-m-d', strtotime($val['create_date'])));
                            ?>
                    </p>
                    <p>
                        <?php
                            echo '金額<br>';
                            echo '￥' . number_format(sanitize($val['price']));
                            ?>
                    </p>
                </div>
                <div class="purchasePanel-body">
                    <a href="msg.php?m_id=<?php echo $val['id']; ?>">
                        <div class="purchasePanel-img">
                            <img src="<?php echo sanitize($val['pic1']); ?>" alt="">
                        </div>
                        <p>
                            <?php
                                echo '<span>' . sanitize($val['name']) . '</span>';
                                echo '<br>';
                                echo 'サイズ：' . sanitize($val['size']);
                                echo '<br>';
                                echo '出品者：' . sanitize($val['user_name']);
                                ?>
                        </p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="mypage.php">&lt&ltマイページへ戻る</a>
    </section>
</div>

<?php
require('footer.php');
?>