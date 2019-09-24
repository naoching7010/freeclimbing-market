<?php
//==========================================
//ログ
//==========================================
//エラーをログに記録する
ini_set('log_errors', 'On');
//ログの出力先
ini_set('error_log', 'php.log');

//==========================================
//デバッグ
//==========================================
$debug_flg = true;
function debug($str)
{
    global $debug_flg;
    if ($debug_flg) {
        error_log('デバッグ：' . $str);
    }
}

//==========================================
//セッション準備・セッションの有効期限を伸ばす
//==========================================
//セッションファイルの置き場を変更する
session_save_path("/var/tmp/");
//ガーベージコレクションが削除するセッションの有効期限を設定
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える
session_regenerate_id();

//==========================================
//画面表示処理開始ログ吐き出し関数
//==========================================
function debugLogStart()
{
    debug('<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<画面表示処理開始');
    debug('セッションID：' . session_id());
    debug('セッション変数の中身：' . print_r($_SESSION, true));
    debug('現在日時タイムスタンプ' . time());
    if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
        debug('ログイン期限日時タイムスタンプ：' . ($_SESSION['login_date'] + $_SESSION['login_limit']));
    }
}
// エラーメッセージを格納する配列
$err_msg = array();

//==========================================
//メッセージ定数
//==========================================
//エラーメッセージ定数
define('MSG01', '入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03', 'すでに登録されているメールアドレスです');
define('MSG04', '文字以上で入力してください');
define('MSG05', '文字以下で入力してください');
define('MSG06', '半角英数字で入力してください');
define('MSG07', 'パスワードとパスワード（再入力）の内容が違います');
define('MSG08', 'エラーが発生しました。しばらくしてからもう一度お試しください');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '正しくありません');
define('MSG11', '選択してください');
define('MSG12', '半角数字で入力してください');
define('MSG13', '古いパスワードと新しいパスワードが同じです');
define('MSG14', '古いパスワードが違います');
define('MSG15', '登録されていないメールアドレスです');
define('MSG16', '文字で入力して下さい');
define('MSG17', '認証キーが正しくありません');
define('MSG18', '認証キーの有効期限が切れています');
define('MSG19', '郵便番号の形式が違います');
define('MSG20', '電話番号の形式が違います');
define('MSG21', '日付が正しくありません');
define('MSG22', 'メッセージがまだありません');
//サクセスメッセージ定数
define('SUC01', 'パスワードを変更しました');
define('SUC02', '認証キーを送信しました');
define('SUC03', 'パスワードを再発行しました');
define('SUC04', '購入しました！出品者と連絡を取りましょう！');
define('SUC05', '商品を出品しました');
define('SUC06', 'プロフィールを編集しました');
//==========================================
//バリデーション
//==========================================
//未入力チェック
function validRequired($str, $key)
{
    global $err_msg;
    if (empty($str)) {
        $err_msg[$key] = MSG01;
    }
}
//Email形式チェック
function validEmail($str, $key)
{
    global $err_msg;
    if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
        $err_msg[$key] = MSG02;
    }
}
//Email重複チェック
function validEmailDup($str, $key)
{
    global $err_msg;
    //db接続
    try {
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $str);

        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (array_shift($result)) {
            $err_msg[$key] = MSG03;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG08;
    }
}
//最小文字数チェック
function validMinLen($str, $key, $min = 6)
{
    global $err_msg;
    if (mb_strlen($str) < $min) {
        $err_msg[$key] = $min . MSG04;
    }
}
//最大文字数チェック
function validMaxLen($str, $key, $max = 255)
{
    global $err_msg;
    if (mb_strlen($str) > $max) {
        $err_msg[$key] = $max . MSG05;
    }
}
//半角チェック
function validHalf($str, $key)
{
    global $err_msg;
    if (!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
        $err_msg[$key] = MSG06;
    }
}
//文字列が同じかチェック
function validMatch($str1, $str2, $key)
{
    global $err_msg;
    if ($str1 !== $str2) {
        $err_msg[$key] = MSG07;
    }
}
// パスワードのバリデーションチェックをまとめた関数
function validPass($str, $key)
{
    validMinLen($str, $key);
    validMaxLen($str, $key);
    validHalf($str, $key);
}
// セレクトボックスの値チェック
function validSelect($str, $key)
{
    global $err_msg;
    if (!preg_match("/^[0-9]+$/", $str)) {
        $err_msg[$key] = MSG10;
    }
}
// セレクトボックスの未選択
function validRequiredSelect($str, $key)
{
    global $err_msg;
    if ($str === 0) {
        $err_msg[$key] = MSG11;
    }
}
// 半角数字チェック
function validNumber($str, $key)
{
    global $err_msg;
    if (!preg_match("/^[0-9]+$/", $str)) {
        $err_msg[$key] = MSG12;
    }
}
//文字数チェック
function validLength($str, $key, $len = 8)
{
    global $err_msg;
    if (mb_strlen($str) !== $len) {
        $err_msg[$key] = $len . MSG16;
    }
}
// 郵便番号形式チェック
function validZip($str, $key)
{
    global $err_msg;
    if (!empty($str) && !preg_match("/^\d{7}$/", $str)) {
        $err_msg[$key] = MSG19;
    }
}
// 電話番号形式チェック
function validTel($str, $key)
{
    debug($str);
    global $err_msg;
    if (!empty($str) && !preg_match("/^(0{1}\d{9,10})$/", $str)) {
        debug($str);
        $err_msg[$key] = MSG20;
    }
}
// 生年月日形式チェック
function validBirtday($str, $key)
{
    global $err_msg;
    if ($str !== NULL && !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $str) || $str > date('Y-m-d')) {
        $err_msg[$key] = MSG21;
    }
}
//==========================================
//DB
//==========================================
//DB接続関数
function dbConnect()
{
    $dsn = 'mysql:dbname=freeclimbing;host=localhost;charset=utf8';
    $username = 'root';
    $password = 'root';
    $option = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );

    $dbh = new PDO($dsn, $username, $password, $option);
    return $dbh;
}
// SQLを実行する関数
function queryPost($dbh, $sql, $data)
{
    $stmt = $dbh->prepare($sql);
    if (!$stmt->execute($data)) {
        debug('クエリ失敗');
        debug('失敗したSQL：' . print_r($stmt, true));
        return false;
    }
    debug('クエリ成功');
    return $stmt;
}
//==========================================
//データ取得
//==========================================
//ユーザー情報取得
function getUser($u_id)
{
    debug('ユーザー情報を取得します');

    try {
        $dbh = dbConnect();
        $sql = 'SELECT id, user_name, birthday, zip, addr, tel, pic, email FROM users WHERE id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);

        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
// ユーザーのパスワードを取得
function getUserPass($u_id)
{
    debug('ユーザー情報を取得します');

    try {
        $dbh = dbConnect();
        $sql = 'SELECT password FROM users WHERE id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);

        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
// 検索された商品情報を取得
function getProductData($c_id, $b_id, $sort, $keyword, $currentPageMinNum, $listSpan = 20)
{
    debug('商品情報を取得します');
    try {
        $dbh = dbConnect();
        $data = array();
        if(empty($keyword)){ //キーワード検索でない場合
            $sql = 'SELECT id, name, price, pic1 FROM product WHERE sale_flg=0 AND delete_flg = 0';
            if (!empty($c_id)) { //カテゴリーが指定されていれば
                $sql .= ' AND category_id = :c_id';
                $data[':c_id'] = $c_id;
            }
            if (!empty($b_id)) { //ブランドが指定されていれば
                $sql .= ' AND brand_id = :b_id';
                $data[':b_id'] = $b_id;
            }
            if (!empty($sort)) { //表示順が指定されていれば
                switch ($sort) {
                    case 1:
                        $sql .= ' ORDER BY price ASC';
                        break;

                    case 2:
                        $sql .= ' ORDER BY price DESC';
                        break;
                }
            }
        }else{ //キーワード検索された場合
            $sql = 'SELECT p.id, p.name, p.price, p.pic1 FROM product AS p INNER JOIN category AS c ON p.category_id = c.id INNER JOIN brand AS b ON p.category_id = b.id WHERE p.sale_flg=0 AND p.delete_flg = 0 AND c.delete_flg = 0 AND b.delete_flg = 0 AND concat(p.name, p.size, p.comment, c.name, b.name) LIKE :keyword';
            $data[':keyword'] = '%'.$keyword.'%';
        }

        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            // DBから取ってきたデータの数を格納
            $result['total'] = count($stmt->fetchAll());
            // 全データを表示するためのページ数を格納
            $result['totalPage'] = (int) ceil($result['total'] / $listSpan);
            if (empty($result['totalPage'])) { // 取得したデータの数が0個だった場合、ページ数を1とする
                $result['totalPage'] = 1;
            }

            $sql .= ' LIMIT ' . $listSpan . ' OFFSET ' . $currentPageMinNum;

            $stmt = queryPost($dbh, $sql, $data);
            if ($stmt) {
                $result['data'] = $stmt->fetchAll();
                return $result;
            } else {
                false;
            }
        } else {
            false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
//商品情報取得
function getProductOne($p_id)
{
    debug('商品情報を取得します');

    try {
        $dbh = dbConnect();
        $sql = 'SELECT p.name AS p_name, p.category_id, p.brand_id, p.size, p.comment, p.price, p.postage_flg, p.pic1, p.pic2, p.pic3, p.sale_user, b.name AS b_name, c.name AS c_name, u.user_name FROM product AS p INNER JOIN brand AS b ON p.brand_id = b.id INNER JOIN category AS c ON p.category_id = c.id INNER JOIN users AS u ON p.sale_user = u.id  WHERE p.id = :p_id AND p.delete_flg = 0';
        $data = array(':p_id' => $p_id);

        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
//各ユーザーが出品している商品を取得
function getSaleProduct($u_id)
{
    debug('出品商品情報を取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT id, pic1 FROM product WHERE sale_user = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);

        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
//各ユーザーがお気に入り登録している商品情報を取得
function getLikeProduct($u_id)
{
    debug('お気に入りに登録している商品情報を取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT l.product_id, p.pic1 FROM `like` AS l LEFT JOIN product AS p ON l.product_id = p.id WHERE l.user_id = :u_id AND l.delete_flg = 0 AND p.delete_flg = 0';
        $data = array(':u_id' => $u_id);

        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
//新着アイテム情報取得
function getNewArriveProduct($category_id, $listSpan = 5)
{
    try {
        $dbh = dbConnect();
        $sql = 'SELECT id, name, price, pic1 FROM product WHERE category_id = :c_id AND sale_flg = 0 AND delete_flg = 0 ORDER BY create_date DESC LIMIT ' . $listSpan;
        $data = array(':c_id' => $category_id);

        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
//カテゴリーデータ取得
function getCategoryData()
{
    debug('カテゴリーデータを取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM category WHERE delete_flg = 0';
        $data = array();

        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
//ブランドデータ取得
function getBrandData()
{
    debug('ブランドデータを取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM brand WHERE delete_flg = 0';
        $data = array();

        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
//DBデータ取得または送信データを取得
function getFormData($key, $method_flg = true)
{

    if ($method_flg) {
        $method = $_POST;
    } else {
        $method = $_GET;
    }
    global $dbFormData;
    if (!empty($dbFormData)) {
        global $err_msg;
        if (!empty($err_msg[$key])) {
            if (isset($method[$key])) {
                return sanitize($method[$key]);
            } else { // こんなことはありえないが念のため記述
                return sanitize($dbFormData[$key]);
            }
        } else {
            if (isset($method[$key]) && $method[$key] !== $dbFormData[$key]) {
                return sanitize($method[$key]);
            } else {
                return sanitize($dbFormData[$key]);
            }
        }
    } else {
        if (isset($method[$key])) {
            return sanitize($method[$key]);
        }
    }
}
//掲示板情報の取得
function getBordData($m_id)
{
    debug('掲示板情報を取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT sale_user, buy_user, product_id, create_date FROM bord WHERE id = :m_id AND delete_flg = 0';
        $data = array(':m_id' => $m_id);

        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
//指定された掲示板のメッセージ情報を取得
function getMsgData($m_id)
{
    debug('メッセージ情報を取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT msg, to_user, from_user, send_date FROM message WHERE bord_id = :b_id AND delete_flg = 0';
        $data = array(':b_id' => $m_id);

        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
// マイページに表示する掲示板情報の取得
function getBordAndMsg($u_id)
{
    debug('掲示板情報とメッセージ情報を取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT id, sale_user, buy_user FROM bord WHERE sale_user = :u_id OR buy_user = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);

        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            $result = $stmt->fetchAll();
            foreach ($result as $key => $val) {
                $sql = 'SELECT * FROM message WHERE bord_id = :b_id AND delete_flg = 0 ORDER BY send_date DESC';
                $data = array(':b_id' => $val['id']);
                $stmt = queryPost($dbh, $sql, $data);
                if ($stmt) {
                    $result[$key]['msg'] = $stmt->fetchAll();
                }
            }

            return $result;
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
// 各製品の取引コメントを取得
function getTradeMsg($p_id)
{
    debug('コメント情報を取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT t.from_user, t.comment, u.id, u.user_name, u.pic FROM tradeMsg AS t INNER JOIN users AS u ON t.from_user = u.id WHERE product_id = :p_id AND t.delete_flg = 0 AND u.delete_flg = 0 ORDER BY t.send_date ASC';
        $data = array(':p_id' => $p_id);

        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
//購入履歴の取得
function getPurchaseProduct($u_id)
{
    debug('購入履歴の取得をします');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT b.id, b.create_date, p.name, p.size, p.price, p.pic1, u.user_name FROM bord AS b INNER JOIN product AS p ON b.product_id = p.id INNER JOIN users AS u ON b.sale_user = u.id WHERE b.buy_user = :u_id AND b.delete_flg = 0 AND p.delete_flg = 0 AND u.delete_flg = 0 ORDER BY b.create_date DESC';
        $data = array(':u_id' => $u_id);

        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
//==========================================
//メッセージ
//==========================================
// エラーメッセージを取得
function getErrMsg($key)
{
    global $err_msg;
    if (!empty($err_msg[$key])) {
        return $err_msg[$key];
    }
}
//サクセスメッセージ表示
function getSessionFlash($key)
{
    if (!empty($_SESSION[$key])) {
        echo $_SESSION[$key];
    }
    unset($_SESSION[$key]);
}
//==========================================
//その他
//==========================================
//サニタイズ
function sanitize($str)
{
    return nl2br(htmlspecialchars($str, ENT_QUOTES));
}
//nl2brの逆の動きをする
function reverseNl2br($str)
{
    return preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/i', "", $str);
}
//画像アップロード
function uploadImg($file, $key)
{
    debug('画像をアップロードします');
    debug('FILE情報：' . print_r($file, true));

    if (isset($file['error']) && is_int($file['error'])) {
        try {
            switch ($file['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_NO_FILE:
                    throw new RuntimeException('ファイルが選択されていません');
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('ファイルサイズが大きすぎます');
                    break;
                default:
                    throw new RuntimeException('その他のエラーが発生しました');
            }

            //拡張し取得
            $type = @exif_imagetype($file['tmp_name']);
            if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
                throw new RuntimeException('画像形式が未対応です');
            }

            $path = 'uploads/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);

            if (!move_uploaded_file($file['tmp_name'], $path)) {
                throw new RuntimeException('ファイル保存時にエラーが発生しました');
            }

            chmod($path, 0644);

            debug('ファイルは正常にアップロードされました');
            debug('ファイルパス：' . $path);
            return $path;
        } catch (RuntimeException $e) {
            error_log('エラー発生；' . $e->getMessage());
            global $err_msg;
            $err_msg[$key] = $e->getMessage();
        }
    }
}
//メール送信
function sendMail($to, $from, $subject, $comment)
{
    debug('メールを送信します');

    mb_language('Japanese');
    mb_internal_encoding('UTF-8');

    if (mb_send_mail($to, $subject, $comment, 'From:' . $from)) {
        debug('メールの送信に成功しました');
    } else {
        debug('メールの送信に失敗しました');
    }
}
//認証キー発行
function makeRandKey($len = 8)
{
    $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $key = '';
    for ($i = 0; $i < $len; $i++) {
        $key .= $str[mt_rand(0, 61)];
    }
    return $key;
}
function showImg($str)
{
    if (empty($str)) {
        return 'img/sample-img.png';
    }
    return $str;
}
function showProfImg($str)
{
    if (empty($str)) {
        return 'img/avatar-unknown.png';
    }
    return $str;
}
//ページネーション
function pageNation($currentPageNum, $productData, $viewNum = 5)
{
    $totalPage = $productData['totalPage'];

    if ($currentPageNum === 1 && $totalPage >= $viewNum) {
        $minPageNum = 1;
        $maxPageNum = $currentPageNum + 4;
    } elseif ($currentPageNum === 2 && $totalPage >= $viewNum) {
        $minPageNum = $currentPageNum - 1;
        $maxPageNum = $currentPageNum + 3;
    } elseif ($currentPageNum === ($totalPage - 1) && $totalPage >= $viewNum) {
        $minPageNum = $currentPageNum - 3;
        $maxPageNum = $currentPageNum + 1;
    } elseif ($currentPageNum === $totalPage && $totalPage >= $viewNum) {
        $minPageNum = $currentPageNum - 4;
        $maxPageNum = $currentPageNum;
    } elseif ($totalPage < $viewNum) {
        $minPageNum = 1;
        $maxPageNum = $totalPage;
    } else {
        $minPageNum = $currentPageNum - 2;
        $maxPageNum = $currentPageNum + 2;
    }

    if (!empty($productData['data'])) {
        echo '<div id="pagenation">';
        echo '<ul>';
        if ($currentPageNum !== 1 && $totalPage > $viewNum) {
            echo '<li><a href="productList.php?p=1' . appendGetParam(array('p'), false) . '">&lt</a></li>';
        }
        for ($i = $minPageNum; $i <= $maxPageNum; $i++) {
            echo '<li><a class="';
            if ($i === $currentPageNum) {
                echo 'active';
            }
            echo '" href="productList.php?p=' . $i . appendGetParam(array('p'), false) . '">' . $i . '</a></li>';
        }
        if ($currentPageNum !== $totalPage && $totalPage > $viewNum) {
            echo '<li><a href="productList.php?p=' . appendGetParam(array('p'), false) . $totalPage . '">&gt</a></li>';
        }
        echo '</ul>';
        echo '</div>';
    }
}
// GETパラメータ付与
// $arr_del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array(), $paramFlg = true)
{
    if ($paramFlg) {
        $str = '?';
    } else {
        $str = '&';
    }
    if (!empty($_GET)) {
        foreach ($_GET as $key => $val) {
            if (!in_array($key, $arr_del_key, true)) {
                $str .= $key . '=' . $val . '&';
            }
        }
        $str = mb_substr($str, 0, -1, 'UTF-8');
        return $str;
    }
}
//各製品のいいね数を取得
function countLike($p_id)
{
    try {
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM `like` WHERE product_id = :p_id AND delete_flg = 0';
        $data = array(':p_id' => $p_id);

        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return array_shift($result);
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
// ログインチェック
function isLogin()
{
    if (!empty($_SESSION['login_date'])) {
        debug('ログイン済みユーザーです');
        if ($_SESSION['login_date'] + $_SESSION['login_limit'] > time()) {
            debug('ログイン期限内です');
            return true;
        } else {
            debug('ログイン期限内オーバーです');
            return false;
        }
    } else {
        debug('未ログインユーザーです');
        return false;
    }
}
// お気に入り登録チェック
function isLike($p_id, $u_id)
{
    try {
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM `like` WHERE user_id = :u_id AND product_id = :p_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id, ':p_id' => $p_id);

        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (array_shift($result)) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
// 送料が含まれているかどうか判定
function judgPostAge($postAgeFlg)
{
    if (!$postAgeFlg) {
        return '出品者負担';
    } else {
        return '購入者負担';
    }
}
//生年月日から年齢を計算
function culcAge($date)
{
    $birthday = str_replace('-', "", $date);
    return (int) ((date('Ymd') - $birthday) / 10000);
}
// セッションを削除
function deleteSession(){
    $_SESSION = array();
    if (isset($_COOKIE['PHPSESSID'])) {
        setcookie('PHPSESSID', '', time() - 1800, '/');
    }
    session_destroy();
}