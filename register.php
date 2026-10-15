<?php require 'config.php';
$err='';$type=$_POST['user_type']??'student';if(!in_array($type,SELF_REGISTER))$type='student';
if($_SERVER['REQUEST_METHOD']=='POST'){chk();
 $n=trim($_POST['name']);$em=strtolower(trim($_POST['email']));$p=$_POST['password'];$pid=trim($_POST['person_id']??'');
 $label=$type==='student'?'roll number':'employee ID';
 if(!$n||!filter_var($em,FILTER_VALIDATE_EMAIL)||strlen($p)<6)$err='Enter a name, valid email and a password of 6+ characters.';
 elseif(!$pid||strlen($pid)>30)$err="Enter your $label.";
 elseif($db->users->findOne(['email'=>$em]))$err='Email already registered.';
 else{$db->users->insertOne(['name'=>$n,'email'=>$em,'password'=>password_hash($p,PASSWORD_DEFAULT),'role'=>$type,'person_id'=>$pid,'created_at'=>new MongoDB\BSON\UTCDateTime()]);flash('Registered as '.ROLE_LABELS[$type].'! Please login.');header('Location: login.php?type='.$type);exit;}}
$title='Register'; require 'includes/header.php'; ?>
<div class="auth-wrap"><div class="card auth-card"><div class="auth-icon"><i class="bi bi-person-plus"></i></div>
<h4>Create your account</h4><p class="sub">Join CampusSafe to report incidents and stay informed.</p>
<?php if($err): ?><div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-triangle-fill me-2"></i><div><?=e($err)?></div></div><?php endif; ?>
<form method="post"><input type="hidden" name="t" value="<?=csrf()?>">
<div class="mb-3"><label class="form-label">Register as</label><div class="type-toggle" role="radiogroup" aria-label="Account type">
<input type="radio" class="btn-check" name="user_type" id="t-student" value="student" <?=$type==='student'?'checked':''?>><label for="t-student"><i class="bi bi-mortarboard"></i> Student</label>
<input type="radio" class="btn-check" name="user_type" id="t-staff" value="staff" <?=$type==='staff'?'checked':''?>><label for="t-staff"><i class="bi bi-person-badge"></i> Staff</label></div></div>
<div class="mb-3"><label class="form-label" for="name">Full name</label><div class="input-icon"><i class="bi bi-person"></i><input id="name" class="form-control" name="name" placeholder="Your full name" autocomplete="name" value="<?=e($_POST['name']??'')?>" required autofocus></div></div>
<div class="mb-3"><label class="form-label" for="person_id" id="pid-label"><?=$type==='student'?'Roll number':'Employee ID'?></label><div class="input-icon"><i class="bi bi-hash"></i><input id="person_id" class="form-control" name="person_id" maxlength="30" placeholder="<?=$type==='student'?'e.g. 21CS104':'e.g. EMP1024'?>" value="<?=e($_POST['person_id']??'')?>" required></div></div>
<div class="mb-3"><label class="form-label" for="email">College email</label><div class="input-icon"><i class="bi bi-envelope"></i><input id="email" class="form-control" type="email" name="email" placeholder="you@campus.edu" autocomplete="email" value="<?=e($_POST['email']??'')?>" required></div></div>
<div class="mb-4"><label class="form-label" for="password">Password</label><div class="input-icon"><i class="bi bi-lock"></i><input id="password" class="form-control" type="password" name="password" placeholder="At least 6 characters" autocomplete="new-password" minlength="6" required></div><div class="hint">Use 6 or more characters.</div></div>
<button class="btn btn-danger btn-lg w-100">Create account</button></form>
<div class="auth-foot">Already registered? <a href="login.php">Sign in</a></div></div></div>
<script>
document.querySelectorAll('input[name=user_type]').forEach(r=>r.addEventListener('change',()=>{const s=document.getElementById('t-student').checked;
document.getElementById('pid-label').textContent=s?'Roll number':'Employee ID';document.getElementById('person_id').placeholder=s?'e.g. 21CS104':'e.g. EMP1024';}));
</script>
<?php require 'includes/footer.php'; ?>
