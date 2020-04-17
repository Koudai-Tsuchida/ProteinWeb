<?php
session_start();
if(isset($_SESSION['user_id']) !== TRUE){
    header('Location: proteinWeb.login.php');
    exit;
}
$name="";
$locate="";
$host = 'localhost';
$username = 'codecamp32171';
$password = 'AUUQSAKA';
$dbname = 'codecamp32171';
$charset='utf8';
$error=array();

if(isset($_GET['name']) === TRUE && $_GET['name'] !==""){
    $name=$_GET['name'];
}
if(isset($_GET['locate']) === TRUE && $_GET['locate'] !==""){
    $locate=$_GET['locate'];
}


$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

$dbh =  new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

try{
        $sql = 'SELECT ec_item_master.item_id, name, price, img, ec_item_stock.stock, locate, status FROM ec_item_master
        INNER JOIN ec_item_stock
        ON ec_item_master.item_id = ec_item_stock.item_id';
        if($name !==''){
            $sql=$sql.' WHERE name=?';
        }
        if($locate !==''){
            $sql=$sql.' AND locate=?';
            
        }
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
        <title>ログイン</title>
        <style>
            body{
                 margin-left:20px;
                
            }
            header{
                border-bottom:1px solid ;
            }
            .header-logo{
                border-bottom:1px solid;
                display:flex;
            }
            .flame{
                margin-top:30px;
                border:1px solid;
               
            }
            img{
                height:100px;
            }
           .profile{
               text-align:right;
           }

            
            .topic{
                text:bold;
                text-align:left;
            }
        </style>
    </head>
    
    
    
    <body>
  
    <div class="flame">
    <div class="header-logo">
    <img src="protein.jpeg">
    <div class="profile">
    <p>ようこそ、○○さん</p>
    </div>
    <div class="logout">
    <p><a href="proteinWeb.logout.php">ログアウト</a></p>
    </div>
    </div>
    
    <main>
        <p>検索</p>
        <div class="search">
            <form method ="get">
            <p>商品名:<input type = "text" name="name" value=""> 地域:<input type = "text" name="locate" value=""> </p>
            <input type="submit" value="検索">
            </form>
        </div>
    </main>
    </body>
</html>