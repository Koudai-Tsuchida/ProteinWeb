<?php
session_start();
if(isset($_SESSION['user_id']) !== TRUE){
    header('Location: proteinWeb.login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$user_name= $_SESSION['user_name'];

$item_id ='';
$host = 'localhost';
$username = 'codecamp32171';
$password = 'AUUQSAKA';
$dbname = 'codecamp32171';
$charset='utf8';
$date=date('Y-m-d H-s-i');
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
$new_img='';
$img_dir='./img/';
$data=array();
$new_img_filename='';
$error=array();



try{
    
    $dbh =  new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        if(isset($_POST['item_id'])){
            $item_id =$_POST['item_id'];
        }
        if(ctype_digit($item_id) !== TRUE){
            $error[]='不正なアクセスです';
        }else {
            $sql = 'SELECT * FROM ec_item_master WHERE item_id = ?';
             $stmt=$dbh->prepare($sql);
             $stmt->bindValue(1, $item_id, PDO::PARAM_INT);
             $stmt->execute();
             $data = $stmt->fetchAll();
             if(count($data) === 0){
                $error[]='不正なアクセスです。';
             }
        }
        if(count($error)===0){
            $sql = 'SELECT * FROM carts WHERE user_id = ? AND item_id = ?';
             $stmt=$dbh->prepare($sql);
             $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
             $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
             $stmt->execute();
             $data = $stmt->fetchAll();
             if(count($data) === 0){
                 $sql='INSERT INTO carts  (user_id ,item_id,amount,create_datetime, update_datetime) VALUES(?, ?, 1, NOW(), NOW())';
                 $stmt=$dbh->prepare($sql);
                 $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
                 $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
                 $stmt->execute();
             }else{
                 $sql='UPDATE carts SET amount=amount+1, update_datetime=NOW()  WHERE user_id= ? AND item_id=? ';
                 $stmt=$dbh->prepare($sql);
                 $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
                 $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
                 $stmt->execute();
             }
            
             
        }
    }

    try{
            $sql = 'SELECT ec_item_master.item_id, name, price, img, ec_item_stock.stock, locate, status FROM ec_item_master
            INNER JOIN ec_item_stock
            ON ec_item_master.item_id = ec_item_stock.item_id
            WHERE status=1';
            $stmt=$dbh->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll();
           
            }catch (PDOException $e) {
            $error[]= '商品一覧の取得できませんでした。理由：'.$e->getMessage();
            }

    
}catch (PDOException $e) {
    $error[] = '接続エラー:'.$e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf8">
        <title>ログイン</title>
        <style>
            .header-logo{
             border:1px solid;
             display:flex;
            }
           
            img{
                height:100px;
            }
            .logout{
              text-align:right;
            }
            .logo{
            font-size:20px;
            }
            
            .topic{
            font-size:50px;
            text-align:right;
            margin:0 auto;
            }
            
            .protein{
               font-size:25px; 
               font:bold;
            }
           
            .cart{
               height:75px;
            }
           .shopping{
               text-align:right;
            }
            
        #flex {
           width:1300px;
            
        }
            #flex .drink {
                border: solid 1px;
                width: 300px;
                height: 300px;
                float: left; 
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
            
            #submit {
                clear: both;
            } 
        </style>
    </head>
    
    
    
    <body>
        <div class="header-logo">
            <img src="protein.jpeg">
            <p class="topic">proteinWeb</p>
            <div class="logout">
                <p class="logo">ようこそ、<?php print $user_name; ?>さん <a href="proteinWeb.logout.php">ログアウト</a> <a href ="proteinWeb.shoppingcart.php"><img class="cart" src ="cart_hover.png"></a></p>
                <p class="shopping"><a href = "proteinWeb.shoppingcart.php">買い物かご</a></p>
            </div>
        </div>
        
        <main>
            <p class="protein">今日のオススメのプロテイン</p>
            <?php foreach ($error as $value){ ?>
            <p><?php print $value; ?></p>
            <?php } ?>
            <form method ="post">
                <?php foreach ($data as $read){ ?>
                    <div id="flex">
                        <div class="drink">
                            <span>商品名:<?php print htmlspecialchars ($read['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span>産地:<?php print htmlspecialchars ($read['locate'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class=img_size>
                            <img src ="<?php print $img_dir.htmlspecialchars($read['img'], ENT_QUOTES, 'UTF-8'); ?>"></span>
                            <span>価格:<?php print htmlspecialchars ($read['price'], ENT_QUOTES, 'UTF-8'); ?>円</span>
                            <span><?php if ($read['stock'] <= 0){ ?>
                            <p>売り切れ</p>
                             <?php } else{ ?>
                            <div id="submit">
                                <button type="submit" name="item_id" value="<?php print $read['item_id']; ?>">カートに入れる。</button>
                            </div>
                            <?php } ?>
                            </span>
                            
                            
                        </div>
                    </div>
                <?php } ?>
           </form>
        </main>
        
    </body>
</html>