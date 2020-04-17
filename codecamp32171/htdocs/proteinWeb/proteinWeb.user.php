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
$img_dir='./img/';
$data=array();
$new_img_filename='';



$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;


$dbh =  new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


try{
                $sql = 'SELECT user_name,create_datetime FROM users';
                $stmt=$dbh->prepare($sql);
                $stmt->execute();
                $data = $stmt->fetchAll();
               }catch (PDOException $e) {
          echo '商品一覧の取得できませんでした。理由：'.$e->getMessage();
          throw $e;
          }

?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <title>ユーザー管理</title>
        <meta charset ="utf8">
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
    <p class="logo"> <a href="proteinWeb.logout.php">ログアウト</a></p>
    <p><a href ="proteinWeb.addition.php">商品管理ページに戻る</a></p>
    
    
    </div>
    </div>
        <main>
    <ul>
        <?php foreach ($data as $read){?>
       <li><?php print $read['user_name'] . ' '  . $read['create_datetime']; ?></li>
       <?php } ?>
   </ul>
        </main>
    </div>
    </body>
</html>