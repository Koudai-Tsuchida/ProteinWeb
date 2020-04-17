<?php

session_start();
$session_name=session_name();
$_SESSION = array();

if(isset($_COOKIE[$session_name])){
    
    $params = session_get_cookie_params();
    
    setcookie($session_name, '', time()-42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
    );
}
session_destroy();
header('Location:proteinWeb.login.php');
exit;
?>