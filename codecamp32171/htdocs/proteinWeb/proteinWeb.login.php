<?php
$user_name="";
$passwd="";

$host = 'localhost';
$username = 'codecamp32171';
$password = 'AUUQSAKA';
$dbname = 'codecamp32171';
$charset='utf8';
$error=array();

$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

try{
    $dbh =  new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        if(isset($_POST['user_name']) === TRUE){
            $user_name=$_POST['user_name'];
        }
        if(isset($_POST['passwd']) === TRUE){
            $passwd=$_POST['passwd'];
        }
        if($user_name === ''){
            $error[]='ユーザー名を入力してください';
        }else if(preg_match('/^[0-9a-zA-Z]+$/',$user_name) !== 1){
            $error[]='ユーザー名が間違っています。';
        }
        if($passwd === ''){
            $error[]='パスワードを入力してください';
        }else if(preg_match('/^[0-9a-zA-Z]+$/',$passwd) !== 1){
            $error[]='パスワードが間違っています。';
        }
        
        if(count($error) === 0){
            try{
                $sql = 'SELECT * FROM users WHERE user_name=? AND password=?';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1, $user_name, PDO::PARAM_STR);
                $stmt->bindValue(2, $passwd, PDO::PARAM_STR);
                $stmt->execute();
                $data=$stmt->fetchAll();
                if(count($data) === 0){
                    $error[]='ユーザー名またはパスワードが登録されていません。';
                }else{
                    session_start();
                    $_SESSION['user_id']=$data[0]['id'];
                    $_SESSION['user_name']=$data[0]['user_name'];
                    if($user_name === 'admin'){
                      header('Location: proteinWeb.addition.php');
                    }else{
                      header('Location: proteinWeb.home.php');
                    }
                    exit;
                }
            }catch(PDOException $e) {
                $error[]='ログインできませんでした。'. $e->getMessage();
            }
            
        }
    } 
}catch(PDOException $e) {
    $error[]='予期せぬエラーが発生しました。' . $e->getMessage();
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
                
            }
            .flame{
                margin-top:30px;
                border:1px solid;
               
            }
            .rogo{
                display:flex;
                
            }
            img{
                height:100px;
            }
            .topic{
            font-size:50px;
           margin:0 auto;
            }
            
            
            main{
                text-align:center;
            }
            
        </style>
    </head>
    
    
    
<body>
    <header>
    <h1>ログイン画面</h1>
    </header>
    <div class="flame">
        <div class="header-logo">
            <div class="rogo">
                <img src="protein.jpeg">
                <p class="topic">proteinWeb</p>
            </div>
        </div>
        
        <main>
            <?php foreach ($error as $value){ ?>
            <p><?php print $value; ?></p>
            <?php } ?>
            <form action ="" method="post">
                <p>ユーザー名<input type="text" id="user_name" name="user_name" value=""></p>
                <p>パスワード<input type="password" id="passwd" name="passwd" value=""></p>
                <p><input type="submit" value="ログイン"></p>
            </form>
            <p><a href="http://codecamp32171.lesson8.codecamp.jp//proteinWeb/proteinWeb.register.php"><input type="submit" value="新規作成"></a></p>
        </main>
    </div>
</body>
</html>