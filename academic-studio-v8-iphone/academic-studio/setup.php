<?php
session_start();
const DB_HOST='localhost'; const DB_USER='root'; const DB_PASS=''; const DB_NAME='academic_studio';
$msg=''; $ok=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $confirm=trim($_POST['confirm']??'');
  if($confirm!=='RESET') $msg='اكتب RESET للتأكيد على حذف قاعدة البيانات القديمة وإنشاء قاعدة جديدة.';
  else {
    try{
      $pdo=new PDO('mysql:host='.DB_HOST.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
      $pdo->exec('DROP DATABASE IF EXISTS `'.DB_NAME.'`');
      $pdo->exec('CREATE DATABASE `'.DB_NAME.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
      $pdo->exec('USE `'.DB_NAME.'`');
      $schema=file_get_contents(__DIR__.'/schema.sql');
      $schema=preg_replace('/^CREATE DATABASE.*?;\s*/mi','',$schema);
      $schema=preg_replace('/^USE `academic_studio`;\s*/mi','',$schema);
      foreach(array_filter(array_map('trim',preg_split('/;\s*(?:\r?\n|$)/',$schema))) as $sql) if($sql) $pdo->exec($sql);
      $st=$pdo->prepare("INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,'admin')");
      $st->execute(['مدير النظام','admin@akadimi.local',password_hash('Admin@123',PASSWORD_DEFAULT)]);
      $ok=true; $msg='تم حذف قاعدة البيانات القديمة وإنشاء قاعدة بيانات جديدة كاملة بنجاح. يمكنك الآن تسجيل الدخول.';
    }catch(Throwable $e){$msg='حدث خطأ: '.$e->getMessage();}
  }
}
?><!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>تهيئة استوديو أكاديمي</title><style>body{font-family:Arial;background:#f4f6f8;padding:30px}.box{max-width:620px;margin:60px auto;background:#fff;padding:30px;border-radius:18px;box-shadow:0 10px 35px #0001}input,button{width:100%;padding:13px;margin-top:10px;border-radius:10px;border:1px solid #ccd3d8}button{background:#102f3b;color:#fff;border:0;font-weight:bold;cursor:pointer}.warn{background:#fff3cd;padding:15px;border-radius:10px}.ok{background:#d1e7dd;padding:15px;border-radius:10px}.bad{background:#f8d7da;padding:15px;border-radius:10px}a{display:block;margin-top:15px}</style></head><body><div class="box"><h1>استوديو أكاديمي</h1><h2>تهيئة قاعدة البيانات</h2><div class="warn">⚠️ هذا الإجراء يحذف قاعدة <b>academic_studio</b> القديمة بالكامل وينشئ قاعدة جديدة. استخدمه فقط لأنك طلبت البدء من الصفر.</div><?php if($msg):?><p class="<?= $ok?'ok':'bad'?>"><?=htmlspecialchars($msg,ENT_QUOTES,'UTF-8')?></p><?php endif;?><?php if(!$ok):?><form method="post"><label>اكتب RESET للتأكيد</label><input name="confirm" autocomplete="off" placeholder="RESET"><button>حذف القديمة وإنشاء قاعدة جديدة</button></form><?php else:?><a href="login.php">الانتقال إلى تسجيل الدخول</a><?php endif;?><p>بيانات الدخول بعد التهيئة: <b>admin@akadimi.local</b> / <b>Admin@123</b></p></div></body></html>
