<?php require 'config.php'; need(['admin']); $title='Users';
$err='';
if($_SERVER['REQUEST_METHOD']=='POST'){chk();$a=$_POST['action']??'';
 if($a=='create'){
  $n=trim($_POST['name']??'');$em=strtolower(trim($_POST['email']??''));$p=$_POST['password']??'';$r=$_POST['role']??'';$pid=trim($_POST['person_id']??'');
  if(!$n||!filter_var($em,FILTER_VALIDATE_EMAIL)||strlen($p)<6||!isset(ROLE_LABELS[$r]))$err='Enter a name, valid email, role and a password of 6+ characters.';
  elseif($db->users->findOne(['email'=>$em]))$err='Email already registered.';
  else{$db->users->insertOne(['name'=>$n,'email'=>$em,'password'=>password_hash($p,PASSWORD_DEFAULT),'role'=>$r,'person_id'=>$pid,'created_at'=>new MongoDB\BSON\UTCDateTime()]);flash(ROLE_LABELS[$r].' account created for '.$n.'.');header('Location: users.php');exit;}
 }else{$id=oid($_POST['id']??'');
  if($id&&(string)$id!==user()['id']){
   if($a=='role'&&isset(ROLE_LABELS[$_POST['role']??'']))$db->users->updateOne(['_id'=>$id],['$set'=>['role'=>$_POST['role']]]);
   if($a=='delete')$db->users->deleteOne(['_id'=>$id]);flash('User updated.');}
  header('Location: users.php');exit;}}
require 'includes/header.php'; ?>
<div class="page-head"><div><h3>User management</h3><p>Create accounts, manage roles and access for all users.</p></div></div>
<?php if($err): ?><div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-triangle-fill me-2"></i><div><?=e($err)?></div></div><?php endif; ?>
<div class="card p-4 mb-4"><div class="card-title-row"><h6>Add user</h6></div>
<form method="post" class="row g-2"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="action" value="create">
<div class="col-md-3"><input class="form-control" name="name" placeholder="Full name" value="<?=e($_POST['name']??'')?>" required aria-label="Full name"></div>
<div class="col-md-3"><input class="form-control" type="email" name="email" placeholder="Email" value="<?=e($_POST['email']??'')?>" required aria-label="Email"></div>
<div class="col-6 col-md-2"><select name="role" class="form-select" aria-label="Role"><?php foreach(ROLE_LABELS as $k=>$l): ?><option value="<?=$k?>" <?=($_POST['role']??'')==$k?'selected':''?>><?=$l?></option><?php endforeach; ?></select></div>
<div class="col-6 col-md-2"><input class="form-control" name="person_id" placeholder="Roll / Emp ID" maxlength="30" value="<?=e($_POST['person_id']??'')?>" aria-label="Roll or employee ID"></div>
<div class="col-md-2"><input class="form-control" type="password" name="password" placeholder="Password" minlength="6" required aria-label="Password"></div>
<div class="col-12"><button class="btn btn-dark btn-sm"><i class="bi bi-person-plus me-1"></i>Create user</button></div></form></div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>User</th><th>Role</th><th>Change role</th><th class="text-end">Actions</th></tr></thead><tbody>
<?php foreach($db->users->find([],['sort'=>['created_at'=>-1]]) as $x): $me=(string)$x['_id']===user()['id']; ?><tr><td><div class="d-flex align-items-center gap-3"><span class="avatar"><?=e(initials($x['name']))?></span><div><div class="row-title"><?=e($x['name'])?><?php if($me): ?> <span class="role-tag">You</span><?php endif; ?></div><div class="row-sub"><?=e($x['email'])?><?=!empty($x['person_id'])?' &middot; '.e($x['person_id']):''?></div></div></div></td>
<td><span class="role-tag role-<?=e($x['role'])?>"><?=e(ROLE_LABELS[$x['role']]??$x['role'])?></span></td>
<td><?php if(!$me): ?><form method="post" class="d-flex gap-2"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="action" value="role"><input type="hidden" name="id" value="<?=$x['_id']?>">
<select name="role" class="form-select form-select-sm w-auto" aria-label="Role"><?php foreach(ROLE_LABELS as $k=>$l): ?><option value="<?=$k?>" <?=$x['role']==$k?'selected':''?>><?=$l?></option><?php endforeach; ?></select><button class="btn btn-sm btn-outline-dark">Save</button></form><?php else: ?><span class="text-muted small">&mdash;</span><?php endif; ?></td>
<td class="text-end"><?php if(!$me): ?><form method="post" onsubmit="return confirm('Delete user?')"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$x['_id']?>"><button class="btn btn-sm btn-outline-danger" aria-label="Delete user"><i class="bi bi-trash"></i></button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require 'includes/footer.php'; ?>
