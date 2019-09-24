<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' Ajax ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if (isset($_POST['productId']) && isset($_SESSION['user_id']) && isLogin()) { // POST送信されていてかつ、ログインしている場合
    $productId = $_POST['productId'];
    try {
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM `like` WHERE user_id = :u_id AND product_id = :p_id AND delete_flg = 0';
        $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $productId);

        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (array_shift($result)) { // お気に入り登録されているとき => $resultの中身が1のとき  
            debug('お気に入り情報を削除します');
            $sql = 'DELETE FROM `like` WHERE user_id = :u_id AND product_id = :p_id AND delete_flg = 0';
            $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $productId);
        } else { // お気に入り登録されていないとき => $resultの中身が0のとき
            debug('お気に入り情報を追加します');
            $sql = 'INSERT INTO `like` (user_id, product_id, create_date) VALUES (:u_id, :p_id, :date)';
            $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $productId, ':date' => date('Y-m-d H:i:s'));
        }

        $stmt = queryPost($dbh, $sql, $data);
        // ログインしているのでtrueを返す
        echo json_encode(array(
            'loginFlg' => true
        ));
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
} elseif (!isLogin()) { // 未ログインの時
    // ログインしていないのでfalseを返す
    echo json_encode(array(
        'loginFlg' => false
    ));
}
