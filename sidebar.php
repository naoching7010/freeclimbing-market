<?php
if (basename($_SERVER['PHP_SELF']) === 'productList.php'){ // 現ページがproductList.phpのとき
    // カテゴリーデータ取得
    $categoryData = getCategoryData();
    // ブランドデータ取得
    $brandData = getBrandData();
?>
<div class="sidebar">
    <form action="" method="get">
        <span class="side-title">カテゴリーから探す</span>
        <div class="selectbox">
            <span class="select-icon"></span>
            <select name="c_id" class="sidebar-select">
                <option value="0" <?php if(empty($c_id)) echo 'selected'; ?>>選択してください</option>
                <?php foreach($categoryData as $key => $val): ?>
                <option value="<?php echo $val['id']; ?>" <?php if($c_id === $val['id']) echo 'selected'; ?>><?php echo $val['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <span class="side-title">ブランドから探す</span>
        <div class="selectbox">
            <span class="select-icon"></span>
            <select name="b_id" class="sidebar-select">
                <option value="0" <?php if(empty($b_id)) echo 'selected'; ?>>選択してください</option>
                <?php foreach($brandData as $key => $val): ?>
                <option value="<?php echo $val['id']; ?>" <?php if($b_id === $val['id']) echo 'selected'; ?>><?php echo $val['name']; ?></option>
                <?php endforeach; ?>
        </select>
        </div>
        <span class="side-title">表示順</span>
        <div class="selectbox">
            <span class="select-icon"></span>
            <select name="sort" class="sidebar-select">
                <option value="0" <?php if(empty($sort)) echo 'selected'; ?>>選択してください</option>
                <option value="1" <?php if($sort === '1') echo 'selected'; ?>>価格の低い順</option>
                <option value="2" <?php if($sort === '2') echo 'selected'; ?>>価格の高い順</option>
            </select>
        </div>
        <div class="btn">
            <input type="submit" value="検索">
        </div>
    </form> 
</div>
<?php
} else{
?>
<div class="sidebar">
    <ul>
        <li><a href="registProduct.php">商品を出品する</a></li>
        <li><a href="purchaseHistory.php">購入履歴を見る</a></li>
        <li><a href="profEdit.php">プロフィール編集</a></li>
        <li><a href="passEdit.php">パスワード変更</a></li>
        <li><a href="withdraw.php">退会する</a></li>
    </ul>
</div>
<?php
}
?>