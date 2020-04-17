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


try{
    $dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
    
    $dbh =  new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        if(isset($_POST['kind']) === TRUE){
            $kind=$_POST['kind'];
        }
        if($kind === 'update'){
            if(isset($_POST['change_amount']) === TRUE){
                $change_amount=$_POST['change_amount'];
            }
            if(ctype_digit($change_amount) !== TRUE || $change_amount<1){
                $error[]='個数は1以上の数字を指定してください。';
            }
            if(isset($_POST['item_id']) === TRUE){
                $item_id=$_POST['item_id'];
            }
            if(ctype_digit($item_id) !== TRUE || $item_id<1){
                $error[]='不正なアクセスです。';
            }
            if(count($error) === 0){
                try{
                    $sql = 'UPDATE carts SET amount=? WHERE user_id=? AND item_id=?';
                    $stmt=$dbh->prepare($sql);
                    $stmt->bindValue(1, $change_amount, PDO::PARAM_STR);
                    $stmt->bindValue(2, $user_id, PDO::PARAM_INT);
                    $stmt->bindValue(3, $item_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $success_msg[]='数量を変更しました。';
                }catch(PDOException $e) {
                    $error[]='数量の変更ができませんでした。';
                }
            } 
        }else if($kind === 'delete'){
            if(isset($_POST['item_id']) === TRUE){
                $item_id=$_POST['item_id'];
            }
            if(ctype_digit($item_id) !== TRUE || $item_id<1){
                $error[]='不正なアクセスです。';
            }
            if(count($error) === 0){
                try{
                    $sql = 'DELETE FROM carts  WHERE user_id=? AND item_id=?';
                    $stmt=$dbh->prepare($sql);
                    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
                    $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $success_msg[]='商品を削除しました。';
                }catch(PDOException $e) {
                    $error[]='商品の削除ができませんでした。';
                }
            } 
        }
    }
        
    try{
        $sql = 'SELECT ec_item_master.item_id, name, price, img, carts.amount,carts.user_id FROM ec_item_master
        INNER JOIN carts
        ON ec_item_master.item_id = carts.item_id';
        $stmt=$dbh->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        
    }catch (PDOException $e) {
        echo '商品一覧の取得できませんでした。理由：'.$e->getMessage();
        throw $e;
    }
    $total = 0;
        foreach ($data as $row){
            $total += $row['price']*$row['amount'];
        }
}catch (PDOException $e) {
    echo '接続できませんでした。理由：'.$e->getMessage();
    throw $e;
}
          
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>買い物かご</title>
        <style>
            
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
            #flex {
           width:1300px;
            
        }
            #flex .drink {
                border: solid 1px;
                padding-top:10px;
                width: 300px;
                height: 300px;
                float:left;
                margin: 10px;
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
            .topic{
            font-size:50px;
            margin:0 auto;
            }
            
            .protein{
            font-size:25px; 
            font:bold;
            }
            
            footer{
                width:20%;
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
    
    <h1>買い物かご</h1>
    <?php foreach ($error as $value){ ?>
    <p><?php print $value; ?></p>
    <?php } ?>
     <?php foreach ($data as $read){ ?>
    <div id = "flex">
        <div class="drink">
            <img src="<?php print $img_dir.htmlspecialchars($read['img'],ENT_QUOTES,'UTF-8'); ?>">
            <p><?php print htmlspecialchars($read['name'],ENT_QUOTES,'UTF-8'); ?></p>
            <p><?php print htmlspecialchars($read['price'],ENT_QUOTES,'UTF-8'); ?>円</p>
            <form method="post">
                <p>
                    <input type ="text" name="change_amount" value="<?php print htmlspecialchars($read['amount'],ENT_QUOTES,'UTF-8'); ?>"> 
                    <button type="submit" name="kind" value="update">変更する</button>
                </p>
                <p><button type="submit" name="kind" value="delete">削除する</button></p>
                <input type="hidden" name="item_id" value="<?php print $read['item_id']; ?>">
           </form>
       </div>
    </div>
    <?php } ?>
    
    <footer>
        <p>合計金額:<?php print htmlspecialchars($total,ENT_QUOTES,'UTF-8'); ?>円</p>
        
        <form method="post" action = "proteinWeb.finishpurchase.php">
            <p>
                <input type="submit" name="buy" value="購入">
            </p>
        </form>
        
        <p><a href ="proteinWeb.home.php">お買い物を続ける</a></p>
    </footer>
    </body>
</html>