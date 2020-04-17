<?php
session_start();
if(isset($_SESSION['user_id']) !== TRUE){
    header('Location: proteinWeb.login.php');
    exit;
}

$name='';
$locate="";
$new_img="";
$process_kind="";
$additon="";
$host = 'localhost';
$username = 'codecamp32171';
$password = 'AUUQSAKA';
$dbname = 'codecamp32171';
$charset='utf8';
$error=array();
$update_stock="";
$result_msg = "";
$rows='';
$stock='';
$status='';
$additon='';
$price="";
$date=date('Y-m-d H-s-i');

$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
$img_dir='./img/';
$data=array();
$new_img_filename='';

$dbh =  new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

if (isset($_POST['process_kind']) ) {
$process_kind = $_POST['process_kind'];
}
      
if ($process_kind === 'insert_item') {
  $result_msg = '商品を追加しました';
} else if ($process_kind === 'update_stock') {
  $result_msg = '在庫数を更新しました';
} else if ($process_kind === 'change_status') {
  $result_msg = 'ステータスを更新しました';
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if($process_kind === 'insert_item'){
        
        $name=$_POST['name'];
        $price=$_POST['price'];
        $locate=$_POST['locate'];
        $status=$_POST['status'];
        $stock=$_POST['stock'];
        
        $name=mb_convert_kana($name, "s");
        $name=trim($name);
        if(isset($name) !== TRUE || $name === ''){
            $error[]='名前を入力してください。';
        }
        if(isset($price) !== TRUE || $price === ''){
            $error[]='値段を入力してください。';
        }
        if(preg_match('/^[0-9]+$/',$price) !==1 ){
            $error[]='正の整数を入力してください';
        }
        
        if(isset($locate) !== TRUE || $locate === ''){
            $error[]='産地を入力してください。';
        }
       
        if($status !== "0" && $status !== "1"){
            $error[]='ステータスを入力してください。';
        }
        
            if(is_uploaded_file($_FILES['new_img']['tmp_name']) === TRUE){
                $extension = pathinfo($_FILES['new_img']['name'], PATHINFO_EXTENSION);
                if($extension === 'png' || $extension === 'jpeg' || $extension === 'jpg'){
                    $new_img_filename = sha1(uniqid(mt_rand(),true)).'.' .$extension;
                    if(is_file($img_dir . $new_img_filename) !== TRUE){
                        if(move_uploaded_file($_FILES['new_img']['tmp_name'], $img_dir . $new_img_filename) !== TRUE){
                            $error[] = 'ファイルアップロードに失敗しました';
                           }
                        }else{
                            $error[] = 'ファイルアップロードに失敗しました。再度お試しください。';
                        }
                    }else{
                        $error[] = 'ファイル形式が異なります。画像ファイルはpngとjpegのみとなっております。';
                    }
                }else{
                    $error[] = 'ファイルを選択してください。';
                }
            
            
                
                    if(count($error) === 0){
                        $dbh->beginTransaction();
                    try{
                            $sql='INSERT INTO ec_item_master(name, price, img, status, create_datetime, update_datetime) VALUES(?,?,?,?,?,?)';
                            $stmt=$dbh->prepare($sql);
                            $stmt->bindValue(1, $name, PDO::PARAM_STR);
                            $stmt->bindValue(2, $price, PDO::PARAM_INT);
                            $stmt->bindValue(3, $new_img_filename, PDO::PARAM_STR);
                            $stmt->bindValue(4, $status, PDO::PARAM_INT);
                            $stmt->bindValue(5, $date, PDO::PARAM_STR);
                            $stmt->bindValue(6, $date, PDO::PARAM_STR);
                            $stmt->execute();
                            $item_id =$dbh->lastInsertid('item_id');
                            $sql ='INSERT INTO ec_item_stock(item_id, stock,locate, create_datetime, update_datetime) VALUES(?,?,?,?,?)';
                            $stmt = $dbh->prepare($sql);
                            $stmt->bindValue(1, $item_id,PDO::PARAM_INT);
                            $stmt->bindValue(2, $stock,PDO::PARAM_INT);
                            $stmt->bindValue(3, $locate,PDO::PARAM_STR);
                            $stmt->bindValue(4, $date, PDO::PARAM_STR);
                            $stmt->bindValue(5, $date, PDO::PARAM_STR);
                            $stmt->execute();
                            $dbh->commit();
                        }catch (PDOException $e){
                            $dbh->rollback();
                            echo '追加できませんでした。理由：'.$e->getMessage();
                            throw $e;
                            }
                    }
            }else if($process_kind === "update_stock"){
                $item_id=$_POST['item_id'];
                $update_stock = $_POST['update_stock'];
                $update_stock = mb_convert_kana($update_stock, "s");
                $update_stock = trim($update_stock);
                if(preg_match('/^[0-9]+$/',$update_stock) !== 1){
                $error[]='正の整数を入力してください。';
                }
            if(count($error) === 0){
                try{
                    $sql = 'UPDATE ec_item_stock SET stock=? WHERE item_id = ?';
                    $stmt=$dbh->prepare($sql);
                    $stmt->bindValue(1, $update_stock, PDO::PARAM_INT);
                    $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
                    $stmt->execute();
                    }catch (PDOException $e) {
                        echo '更新に失敗しました。理由:'.$e->getMessage();
                        throw $e;
                    }
            }
            }else if($process_kind === "change_status"){
                $item_id = $_POST['item_id'];
                $change_status = $_POST['change_status'];
                try{
                    $sql = 'UPDATE ec_item_master SET status=?, update_datetime=NOW() WHERE item_id=?';
                    $stmt=$dbh->prepare($sql);
                    $stmt->bindValue(1, $change_status, PDO::PARAM_INT);
                    $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
                    $stmt->execute();
                }catch (PDOException $e){
                    echo 'ステータスの更新に失敗しました。理由:'.$e->getMessage();
                    throw $e;
                }
            }
}
            try{
                $sql = 'SELECT ec_item_master.item_id, name, price, img, ec_item_stock.stock, locate, status FROM ec_item_master
                INNER JOIN ec_item_stock
                ON ec_item_master.item_id = ec_item_stock.item_id';
                $stmt=$dbh->prepare($sql);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                foreach ($rows as $row){
                $data[] = $row;
          }
          }catch (PDOException $e) {
          echo '商品一覧の取得できませんでした。理由：'.$e->getMessage();
          throw $e;
          }
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>プロテイン追加</title>
        <style>
            img{
                height:125px;
            }
        </style>
    </head>
    
    <body>
        <?php if ( count($error) ===0) { ?>
        <p><?php print $result_msg; ?></p>
        <?php } ?>
        <h1>プロテイン追加</h1>
        <?php if(count($error) !== 0) { ?>
          <?php foreach ($error as $value){ ?>
          <p><?php print $value; ?> </p>
          <?php }} ?>
        <form method="post" enctype="multipart/form-data">
            <div><label>商品名：<input type="text" name="name" value=""></label></div>
            <div><label>値段:<input type="text" name="price" value=""></label></div>
            <div><label>在庫数:<input type="text" name="stock" value=""></label></div>
            <div><label>産地 : <input type="text" name="locate" value=""></label></div>
            <div><label><input type="file" name="new_img" value="ファイルを選択"></label></div>
            <div><label><select name="status">
            <option value="1">公開</option>
            <option value="0">非公開</option>
            </label></div>
            <input type="hidden" name="process_kind" value="insert_item">
            <div><label><input type="submit" name="addition" value="商品追加"></label></div>
        </form>
        
        <h2>商品情報変更</h2>
        <p>商品一覧</p>
        <table>
            <tr>
                <td>商品画像</td>
                <td>商品名</td>
                <td>価格</td>
                <td>原産地</td>
                <td>在庫数</td>
                <td>ステータス</td>
            </tr>
            <?php foreach($data as $read){ ?>
            <tr>
                <td><img src ="<?php print $img_dir . htmlspecialchars($read['img'],ENT_QUOTES,'UTF-8'); ?>"></td>
                <td><?php print htmlspecialchars ($read['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php print htmlspecialchars ($read['price'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php print htmlspecialchars ($read['locate'], ENT_QUOTES, 'UTF-8'); ?></td>
                
                <td>
                    <form method = "post">
                        <input type="hidden" name="item_id" value="<?php print $read["item_id"]; ?>"><input type="text" name="update_stock" value="<?php print $read['stock']; ?>">個&nbsp;&nbsp;<input type ="submit" value="変更" >
                        <input type="hidden" name="process_kind" value="update_stock">
                    </form>
                </td>
                <td>
                    <form method="post">
                        <?php if($read['status'] === 1){ ?>
                        <input type="hidden" name="change_status" value="0">
                        <input type="submit" value="公開→非公開">
                        <?php } else { ?>
                        <input type="hidden" name="change_status" value="1">
                        <input type="submit" value="非公開→公開">
                        <?php } ?>
                        <input type="hidden" name="item_id" value="<?php print $read['item_id'] ?>">
                        <input type="hidden" name="process_kind" value="change_status">
                            
                    </form>
                </td>
            </tr>
            <?php } ?>
            
        </table>
    </body>
</html>