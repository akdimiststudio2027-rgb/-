<?php
session_start();
const DB_HOST='localhost';
const DB_NAME='academic_studio';
const DB_USER='root';
const DB_PASS='';
const APP_NAME='استوديو أكاديمي';
date_default_timezone_set('Asia/Baghdad');
function db(){
 static $pdo=null; if($pdo instanceof PDO) return $pdo;
 $opts=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false];
 try{$pdo=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,$opts); return $pdo;}
 catch(PDOException $e){http_response_code(500);exit('<div style="font-family:Arial;padding:30px;direction:rtl"><h2>تعذر الاتصال بقاعدة البيانات</h2><p>'.htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8').'</p><p>شغّل MySQL ثم افتح setup.php لتهيئة القاعدة إذا كانت غير موجودة.</p></div>');}
}
function auth(){return isset($_SESSION['user']);}
function require_auth(){if(!auth()){header('Location: login.php');exit;}}
function is_admin(){return auth() && ($_SESSION['user']['role']??'staff')==='admin';}
function require_admin(){if(!is_admin()){http_response_code(403);exit('غير مصرح لك بهذه العملية');}}
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function csrf(){if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(24));return $_SESSION['csrf'];}
function check_csrf(){if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(419);exit('طلب غير صالح');}}
function flash($msg,$type='ok'){$_SESSION['flash']=[$msg,$type];}
function take_flash(){$f=$_SESSION['flash']??null;unset($_SESSION['flash']);return $f;}
function redirect($url){header('Location: '.$url);exit;}
function wa_link($number,$text=''){ $n=preg_replace('/\D+/','',(string)$number); if(!$n)return '#'; return 'https://wa.me/'.$n.($text?'?text='.rawurlencode($text):''); }
function money($n,$currency='د.ع'){return number_format((float)$n,0).' '.$currency;}
function sql_backup(PDO $pdo){
    $tables=$pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $out="-- استوديو أكاديمي\n-- Backup: ".date('Y-m-d H:i:s')."\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n";
    foreach($tables as $table){
        $create=$pdo->query('SHOW CREATE TABLE `'.$table.'`')->fetch(PDO::FETCH_NUM)[1];
        $out.="\nDROP TABLE IF EXISTS `{$table}`;\n{$create};\n";
        $rows=$pdo->query('SELECT * FROM `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as $row){$vals=[];foreach($row as $v){$vals[]=$v===null?'NULL':$pdo->quote((string)$v);} $out.="INSERT INTO `{$table}` (`".implode('`,`',array_keys($row))."`) VALUES (".implode(',',$vals).");\n";}
    }
    return $out."\nSET FOREIGN_KEY_CHECKS=1;\n";
}
