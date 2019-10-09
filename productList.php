<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('　商品一覧ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// GET送信の値をそれぞれ変数に格納
$currentPageNum = (!empty($_GET['p'])) ? (int) $_GET['p'] : 1;
$c_id = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
$b_id = (!empty($_GET['b_id'])) ? $_GET['b_id'] : '';
$sort = (isset($_GET['sort'])) ? $_GET['sort'] : '';
$keyword = (isset($_GET['keyword'])) ? $_GET['keyword'] : '';

// 1ページあたりのコンテンツ表示数
$listSpan = 20;
// 現在のページのコンテンツの最初の番号
$currentPageMinNum = ($currentPageNum - 1)  * $listSpan;
// 商品情報取得
$ProductData = getProductData($c_id, $b_id, $sort, $keyword, $currentPageMinNum);
// 改ざんチェック
if ((empty($ProductData['data']) && $ProductData['totalPage'] < $currentPageNum) || empty($currentPageNum)) {
    debug('不正な値が入力されました');
    debug('商品一覧ページへ遷移します');
    header('Location:productList.php');
}
?>

<?php
$title = '商品一覧';
require('head.php');
?>
<body class="page-2colum">
    <?php
    require('header.php');
    ?>
    <div id="contents" class="site-width">
        <?php
        require('sidebar.php');
        ?>
        <section id="main">
            <div class="search-title">
                <div class="search-left">
                    <?php if (!empty($ProductData['data'])) { ?>
                        <span class="total-num"><?php echo sanitize($ProductData['total']); ?></span>件の商品が見つかりました
                    <?php } else { ?>
                        商品が出品されていません
                    <?php } ?>
                </div>
                <div class="search-right">
                    <?php if (!empty($ProductData['data'])) { ?>
                        <span class="num"><?php echo sanitize($currentPageMinNum + 1); ?></span> - <span class="num"><?php echo ($currentPageMinNum + count($ProductData['data'])); ?></-span>件 / <span class="num"><?php echo sanitize($ProductData['total']); ?></span>件中
                    <?php } ?>
                </div>
            </div>
            <div class="panel-list">
                <?php foreach ($ProductData['data'] as $key => $val) : ?>
                    <a href="productDetail.php?p_id=<?php echo $val['id']; ?>" class="panel">
                        <div class="panel-head">
                            <img src="<?php echo sanitize(showImg($val['pic1'])); ?>" alt="">
                        </div>
                        <div class="panel-body">
                            <?php echo sanitize($val['name']);
                                echo '<br>';
                                echo '￥' . number_format(sanitize($val['price']));
                                echo '<br>';
                                echo '<i class="fas fa-heart"></i> ' . countLike($val['id']);
                                ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php pageNation($currentPageNum, $ProductData); ?>
        </section>
    </div>
    <?php 
    require('footer.php'); 
    ?>