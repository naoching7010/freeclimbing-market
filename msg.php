<?php 
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('　掲示板　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';
//掲示板情報を取得する
$bordData = getBordData($m_id);
//メッセージ情報を取得する
$msgData = getMsgData($m_id);
//取引商品情報を取得する
$productData = getProductOne($bordData['product_id']);
debug('取得した掲示板情報：'.print_r($bordData, true));
debug('取得したメッセージ情報：'.print_r($msgData, true));
debug('取得した商品情報'.print_r($productData, true));

//相手のユーザIDを取得
$dealUserId = array();
$dealUserId[] = $bordData['sale_user'];
$dealUserId[] = $bordData['buy_user'];
if(($key = array_search($_SESSION['user_id'], $dealUserId)) !== false){
    unset($dealUserId[$key]);
} 
$partnerUserId = array_shift($dealUserId);
//自分のユーザIDを取得
$myUserId = $_SESSION['user_id'];
//相手と自分のユーザ情報を取得
$partnerUserData = getUser($partnerUserId);
$myUserData = getUser($myUserId);
debug('パートナー情報：'.print_r($partnerUserData, true));
debug('マイ情報：'.print_r($myUserData, true));

//改ざんされていないかチェック
if(empty($m_id) || empty($bordData) || empty($productData || $partnerUserData || $myUserData)){
    debug('不正な値が入りました');
    debug('マイページへ遷移します');
    header('Location:mypage.php');
    exit();
}

if(!empty($_POST)){
    debug('POST送信があります');
    debug('POST情報:'.print_r($_POST, true));
    $msg =$_POST['msg']; 
    //最大文字数チェック
    validMaxLen($msg, 'msg');
    if(empty($err_msg)){ //最大文字数をこえてければ
        debug('バリデーションOK');
        try{
            $dbh = dbConnect();
            $sql = 'INSERT INTO message (bord_id, msg, to_user, from_user, send_date, create_date) VALUES (:b_id, :msg, :to_user, :from_user, :send_date, :create_date)';
            $data = array(':b_id' => $m_id, ':msg' => $msg, ':to_user' => $partnerUserId, ':from_user' => $myUserId, ':send_date' => date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));

            $stmt = queryPost($dbh, $sql, $data);
            if($stmt){
                debug('送信完了');
                //自画面へ遷移
                debug('連絡掲示板へ遷移します');
                header('Location:msg.php?m_id='.$m_id);
            }
        }catch(Exception $e){
            error_log('エラー発生：'.$e->getMessage());
        }
    }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$title = '掲示板';
require('head.php');
?>
<body class="page-1colum">
    <?php
    require('header.php');
    ?>
    <p class="msg-success js-fade-msg" style="display: none;">
        <?php
            getSessionFlash('msg_success');
        ?>
    </p>
    <div id="contents" class="site-width">
        <h1 class="page-title" style="border: none;">掲示板</h1>
        <section id="main">
            <div class="trade-info">
                <div class="trade-partner">
                    <h2 class="trade-title">取引相手</h2>
                    <div class="avatar-img">
                        <img src="<?php echo showProfImg(sanitize($partnerUserData['pic'])); ?>" alt="" class="avatar">
                    </div>
                    <div class="avatar-info">
                        <?php 
                        echo sanitize($partnerUserData['user_name']).'　'.sanitize(culcAge($partnerUserData['birthday'])).'歳<br>';
                        if($partnerUserData['zip']){
                        echo '〒'.substr(sanitize($partnerUserData['zip']), 0, 3).'-'.substr(sanitize($partnerUserData['zip']), 3).'<br>';
                        }
                        echo '住所：'.sanitize($partnerUserData['addr']).'<br>';
                        echo 'TEL：'.sanitize($partnerUserData['tel']);
                        ?> 
                    </div>
                    <div class="trade-product">
                        <h2 class="trade-title">取引商品</h2>
                        <div class="product-img">
                            <img class="product" src="<?php echo sanitize($productData['pic1']); ?>" alt="">
                        </div>
                        <div class="product-info">
                            <?php 
                            echo sanitize($productData['p_name']).'<br>';
                            echo '￥'.number_format(sanitize($productData['price'])).'<br>';
                            echo '送料：';
                            echo judgPostAge(sanitize($productData['postage_flg'])).'<br>';
                            echo '取引開始日：'.date('Y-m-d', strtotime(sanitize($bordData['create_date'])));
                            ?>
                        </div>
                    </div>
                </div>
                <div class="msg-container">
                    <div class="msg">
                        <?php 
                        if(!empty($msgData)){
                            foreach ($msgData as $key => $val) {
                                if($val['from_user'] !== $_SESSION['user_id']){
                        ?>
                            <div class="msg-left">
                                <img src="<?php echo showProfImg(sanitize($partnerUserData['pic'])); ?>" alt="">
                                <p>
                                    <?php echo sanitize($val['msg']); ?>
                                </p>
                            </div>
                        <?php }else{ ?>
                            <div class="msg-right">
                                <img src="<?php echo showProfImg(sanitize($myUserData['pic'])); ?>" alt="">
                                <p>
                                    <?php echo sanitize($val['msg']); ?>
                                </p>
                            </div>
                        <?php }
                                }
                        }else{ ?>
                            <div>
                                <p style="text-align: center; margin-top: 50px; font-size: 20px;">メッセージがありません</p>
                            </div>
                        <?php } ?>
                    </div>
                    <form action="" method="post" class="send-msg btn-container">
                        <textarea name="msg" class="auto-resize js-form-required" placeholder="メッセージを入力"></textarea>
                        <input type="submit" name="submit" value="送信" style="width: 150px; float: right;" class="btn js-disabled-submit" disabled="disabled">
                    </form>
                    <a href="mypage.html">&lt マイページへ戻る</a>
                </div>
            </div>
        </section>
    </div>
    <?php
    require('footer.php')
    ?>