<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('　ホームページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
// カテゴリーデータの取得
$categoryData = getCategoryData();
debug('取得した情報：' . print_r($categoryData, true));
debug('新着アイテムの情報を取得します');
// 各カテゴリのidを要素番号にして、その配列の中に各カテゴリーの製品データを格納する
foreach ($categoryData as $key => $val) {
    $newArriveProduct[$val['id']] = getNewArriveProduct($val['id']);
}
debug('取得した情報' . print_r($newArriveProduct, true));
?>

<?php
$title = 'HOME';
require('head.php');
?>
<body class="page-1colum">
    <?php
    require('header.php');
    ?>
    <!--<div id="img-slider" class="js-img-slider">
        <i class="fas fa-chevron-left slider-nav slide-prev js-slide-prev"></i>
        <i class="fas fa-chevron-right slider-nav slide-next js-slide-next"></i>
        <ul class="slider-container js-slider-container">
            <li class="slide-item slide-item1 js-slide-item">バナー画像1</li>
            <li class="slide-item slide-item2 js-slide-item">バナー画像2</li>
            <li class="slide-item slide-item3 js-slide-item">バナー画像3</li>
        </ul>
        <ul class="current-num-container">
            <li class="js-show-currentNum current-num"><i class="fas fa-circle"></i></li>
            <li class="js-show-currentNum current-num"><i class="fas fa-circle"></i></li>
            <li class="js-show-currentNum current-num"><i class="fas fa-circle"></i></li>
        </ul>
    </div>-->
    <div id="contents" class="site-width">
        <section id="main">
            <?php foreach ($categoryData as $c_key => $c_val) : ?>
                <div class="product_new">
                    <div class="title-container">
                        <h2 class="title"><?php echo sanitize($c_val['name']); ?>新着アイテム</h2>
                    </div>
                    <div class="panel-list">
                        <?php foreach ($newArriveProduct[$c_val['id']] as $p_key => $p_val) : ?>
                            <a href="productDetail.php?p_id=<?php echo $p_val['id']; ?>" class="panel">
                                <div class="panel-head">
                                    <img src="<?php echo sanitize(showImg($p_val['pic1'])); ?>" alt="">
                                </div>
                                <div class="panel-body">
                                    <?php
                                    echo sanitize($p_val['name']);
                                    echo '<br>';
                                    echo number_format(sanitize($p_val['price']));
                                    echo '<br>';
                                    echo '<i class="fas fa-heart"></i> ' . countLike($p_val['id']);
                                    ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </div>
    <?php
    require('footer.php')
    ?>