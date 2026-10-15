<?php require 'config.php';
$err='';$type=$_POST['user_type']??($_GET['type']??'student');if(!isset(ROLE_LABELS[$type]))$type='student';
if($_SERVER['REQUEST_METHOD']=='POST'){chk();
 $u=$db->users->findOne(['email'=>strtolower(trim($_POST['email']??''))]);
 if(!$u||!password_verify($_POST['password']??'',$u['password']))$err='Invalid email or password.';
 elseif($u['role']!==$type)$err='This account is registered as '.ROLE_LABELS[$u['role']].'. Please select the correct login type.';
 else{session_regenerate_id(true);$_SESSION['user']=['id'=>(string)$u['_id'],'name'=>$u['name'],'role'=>$u['role']];header('Location: dashboard.php');exit;}}
$title='Login'; require 'includes/header.php';
$icons=['student'=>'bi-mortarboard','staff'=>'bi-person-badge','security'=>'bi-shield-check','admin'=>'bi-person-gear']; ?>
<div class="auth-wrap"><div class="card auth-card"><div class="auth-icon"><i class="bi bi-shield-lock"></i></div>
<h4>Welcome back</h4><p class="sub">Sign in to report and track campus incidents.</p>
<?php if($err): ?><div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-triangle-fill me-2"></i><div><?=e($err)?></div></div><?php endif; ?>
<form method="post"><input type="hidden" name="t" value="<?=csrf()?>">
<div class="mb-3"><label class="form-label">Login as</label><div class="type-toggle" role="radiogroup" aria-label="Login type">
<?php foreach(ROLE_LABELS as $k=>$l): ?><input type="radio" class="btn-check" name="user_type" id="t-<?=$k?>" value="<?=$k?>" <?=$type===$k?'checked':''?>><label for="t-<?=$k?>"><i class="bi <?=$icons[$k]?>"></i> <?=$l?></label>
<?php endforeach; ?></div>
<div class="hint">Security Officer and Admin accounts are created by the campus admin.</div></div>
<div class="mb-3"><label class="form-label" for="email">Email</label><div class="input-icon"><i class="bi bi-envelope"></i><input id="email" class="form-control" type="email" name="email" value="<?=e($_POST['email']??'')?>" placeholder="you@campus.edu" autocomplete="email" required autofocus></div></div>
<div class="mb-4"><label class="form-label" for="password">Password</label><div class="input-icon"><i class="bi bi-lock"></i><input id="password" class="form-control" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required></div></div>
<button class="btn btn-danger btn-lg w-100">Sign in</button></form>
<div class="auth-foot">New student or staff? <a href="register.php">Create an account</a></div></div></div>
<?php require 'includes/footer.php'; ?>
