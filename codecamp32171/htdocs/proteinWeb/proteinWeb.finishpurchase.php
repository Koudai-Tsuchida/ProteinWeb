<?php
session_start();
if(isset($_SESSION['user_id']) !== TRUE){
    header('Location: proteinWeb.login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$user_name= $_SESSION['user_name'];

$host = 'localhost';
$username = 'codecamp32171';
$password = 'AUUQSAKA';
$dbname = 'codecamp32171';
$charset='utf8';
$kind="";
$change_amount="";
$error=array();
$date=date('Y-m-d H-s-i');
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
$new_img='';
$img_dir='./img/';
$data=array();
$new_img_filename='';
$item_id='';
$buy='';
$amount="";
$result_msg="";

try{
    $dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
    $dbh =  new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        try{
            $sql = 'SELECT ec_item_master.item_id, name, price, img,status, carts.amount,carts.user_id, ec_item_stock.stock 
            FROM carts
            INNER JOIN ec_item_master
            ON ec_item_master.item_id = carts.item_id
            INNER JOIN ec_item_stock 
            ON ec_item_stock.item_id = carts.item_id
            WHERE carts.user_id =?';
            $stmt=$dbh->prepare($sql);
            $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll();
        }catch (PDOException $e) {
            $error[]= '商品一覧の取得できませんでした。理由：'.$e->getMessage();
            throw $e;
        }
        $dbh->beginTransaction();
        try{
            foreach ($data as $value){
                if($value['status'] !== 1){
                    $error[]=htmlspecialchars ($value['name'], ENT_QUOTES, 'UTF-8') . 'は販売していません。カートから商品を削除してください。';
                    
                }else if($value['stock'] < $value['amount']){
                    $error[]=htmlspecialchars ($value['name'], ENT_QUOTES, 'UTF-8') . 'は在庫がたりません。商品を減らしてください。';
                }
                if(count($error) === 0){
                    $sql ='UPDATE ec_item_stock SET stock =stock-? ,update_datetime=NOW() WHERE item_id=?';
                    $stmt=$dbh->prepare($sql);
                    $stmt->bindValue(1, $value['amount'], PDO::PARAM_INT);
                    $stmt->bindValue(2, $value['item_id'], PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
            if(count($error) === 0){
                $sql = 'DELETE FROM carts  WHERE user_id=?';
                $stmt=$dbh->prepare($sql);
                $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
                $stmt->execute();
            }
            if(count($error) === 0){
                $dbh->commit();
                $result_msg='購入完了しました。';
            }else{
                $dbh->rollback();
            }
        }catch(PDOException $e) {
            $dbh->rollback();
            $error[]='商品の削除ができませんでした。';
        }
            
            
        
    }
    $total = 0;
        foreach ($data as $row){
            $total += $row['price']*$row['amount'];
        }
}catch (PDOException $e) {
    $error[]= '購入できませんでしでした。理由：'.$e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>購入完了ページ</title>
        <style>
            img{
                height:125px;
            }
            .header-logo{
             border:1px solid;
             display:flex;
            }
           
            img{
                height:100px;
            }
            .logout{
              float:right;
            }
            .logo{
            font-size:20px;
            }
            
            .topic{
            font-size:50px;
            margin:0 auto;
            }
            
            .protein{
               font-size:25px; 
               font:bold;
            }
            #flex {
           width:1300px;
        }
            #flex .drink {
                border: solid 1px;
                width: 300px;
                height: 300px;
                float:left;
                margin: 5px;
                text-align: center;
            }
            #flex span {
                display: block;
                margin: 10px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .img_size {
                 height: 125px;
            }
            
        </style>
    </head>
    
    <body>
        <div class="header-logo">
            <img src="protein.jpeg">
            <p class="topic">proteinWeb</p>
            <div class="logout">
                <p class="logo">ようこそ、<?php print $user_name; ?>さん <a href="proteinWeb.logout.php">ログアウト</a></p>
            </div>
        </div>
        
        <h1>購入完了ページ</h1>
        <?php foreach ($error as $errors){ ?>
        <p><?php print $errors; ?></p>
        <?php } ?>
        
        <?php foreach ($data as $read){ ?>
        <div id ="flex">
            <div class="drink">
                <span><p><?php print htmlspecialchars($read['name'],ENT_QUOTES,'UTF-8'); ?></p></span>
                <span class="img_size">
                    <img src="<?php print $img_dir.htmlspecialchars($read['img'],ENT_QUOTES,'UTF-8'); ?>">
                </span>
                <span><p><?php print htmlspecialchars($read['amount'],ENT_QUOTES,'UTF-8'); ?>個:
                <?php print htmlspecialchars($read['price']*$read['amount'],ENT_QUOTES,'UTF-8'); ?>円</p></span>
                <input type="hidden" name="item_id" value="<?php print $read['item_id']; ?>"><input type="hidden" name="amount" value="<?php print $read['amount']; ?>">
            </div>
        </div>
        <?php } ?>
        <footer>
            <?php if ( count($error) ===0) { ?>
            <p><?php print $result_msg; ?></p>
            <p>合計金額<?php print $total; ?>円</p>
            <?php } ?>
            <p><a href ="proteinWeb.home.php">お買い物を続ける</a></p>
        </footer>
    </body>
</html>