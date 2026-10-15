<?php require 'config.php'; need();
$id=oid($_GET['id']??'');$inc=$id?$db->incidents->findOne(['_id'=>$id]):null;
if(!$inc)exit('Not found');
if(!seeall()&&$inc['reporter_id']!==user()['id']){http_response_code(403);exit('Access denied');}
$now=fn()=>new MongoDB\BSON\UTCDateTime();
if($_SERVER['REQUEST_METHOD']=='POST'){chk();$a=$_POST['action']??'';
 if($a=='assign'&&is_security()){ // security officer allocates the incident to a person (name only, no login needed)
  $who=trim($_POST['assignee']??'');$note=trim($_POST['note']??'');
  if(mb_strlen($who)<2||mb_strlen($who)>60)flash('Enter the name of the person to allocate (2-60 characters).');
  else{$set=['assigned_to'=>$who,'assigned_by'=>user()['name'],'assigned_at'=>$now(),'updated_at'=>$now()];
   $entry=['type'=>'assign','status'=>$inc['status'],'note'=>'Allocated to '.$who.($note?': '.$note:''),'by'=>user()['name'],'at'=>$now()];
   if($inc['status']==='Reported'){$set['status']='In Progress';$entry['status']='In Progress';}
   $db->incidents->updateOne(['_id'=>$id],['$set'=>$set,'$push'=>['progress'=>$entry]]);flash('Incident allocated to '.$who.'.');}}
 elseif($a=='status'&&is_security()&&in_array($_POST['status']??'',STATS)){ // progress update with a note
  $ns=$_POST['status'];$note=trim($_POST['note']??'');
  if($ns!=='Reported'&&empty($inc['assigned_to']))flash('Allocate this incident to someone before updating its progress.');
  else{$db->incidents->updateOne(['_id'=>$id],['$set'=>['status'=>$ns,'updated_at'=>$now()],'$push'=>['progress'=>['type'=>'status','status'=>$ns,'note'=>$note,'by'=>user()['name'],'at'=>$now()]]]);flash('Progress updated.');}}
 elseif($a=='comment'&&!is_admin()&&trim($_POST['text']??'')){$db->incidents->updateOne(['_id'=>$id],['$push'=>['comments'=>['by'=>user()['name'],'text'=>trim($_POST['text']),'at'=>$now()]]]);flash('Comment added.');}
 elseif($a=='delete'&&is_admin()){$db->incidents->deleteOne(['_id'=>$id]);flash('Incident deleted.');header('Location: incidents.php');exit;}
 header('Location: incident_view.php?id='.$id);exit;}
$title=$inc['title']; require 'includes/header.php';
$canEdit=is_security()||($inc['reporter_id']===user()['id']&&$inc['status']==='Reported');
$log=isset($inc['progress'])?array_reverse($inc['progress']->getArrayCopy()):[];
$names=[];if(is_security()){foreach($db->users->find(['role'=>'security']) as $x)$names[]=$x['name'];foreach($db->incidents->distinct('assigned_to') as $x)if($x)$names[]=$x;$names=array_values(array_unique($names));} ?>
<a href="incidents.php" class="breadcrumb-link"><i class="bi bi-arrow-left"></i> All incidents</a>
<div class="row g-4"><div class="col-lg-8"><div class="card p-4 p-lg-5 mb-4">
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3"><div><div class="section-label mb-1">Incident</div><h4 class="mb-0"><?=e($inc['title'])?></h4></div><div class="d-flex gap-2 flex-wrap"><?=badge($inc['severity'])?> <?=badge($inc['status'])?></div></div>
<div class="meta-grid"><div class="meta-item"><div class="m-label">Category</div><div class="m-value"><i class="bi bi-tag me-1 text-muted"></i><?=e($inc['category'])?></div></div>
<div class="meta-item"><div class="m-label">Location</div><div class="m-value"><i class="bi bi-geo-alt me-1 text-muted"></i><?=e($inc['location'])?></div></div>
<div class="meta-item"><div class="m-label">Reported by</div><div class="m-value"><?=e($inc['reporter_name'])?></div></div>
<div class="meta-item"><div class="m-label">Reported on</div><div class="m-value"><?=ts($inc['created_at'])?></div></div></div>
<div class="section-label mb-2">Description</div><p class="description mb-0"><?=nl2br(e($inc['description']))?></p>
<?php if($canEdit||is_admin()): ?><div class="d-flex gap-2 mt-4 pt-3 border-top"><?php if($canEdit): ?><a class="btn btn-sm btn-outline-dark" href="incident_form.php?id=<?=$inc['_id']?>"><i class="bi bi-pencil me-1"></i> Edit</a><?php endif; ?>
<?php if(is_admin()): ?><form method="post" class="d-inline" onsubmit="return confirm('Delete this incident permanently?')"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i> Delete</button></form><?php endif; ?></div><?php endif; ?></div>

<div class="card p-4 mb-4"><div class="card-title-row"><h6>Progress log</h6><span class="role-tag"><?=count($log)+1?></span></div>
<ul class="timeline"><?php foreach($log as $l): ?><li><span class="avatar"><i class="bi <?=($l['type']??'')==='assign'?'bi-person-check':'bi-arrow-repeat'?>"></i></span><div class="bubble"><span class="who"><?=e($l['by'])?></span><span class="when"><?=ts($l['at'])?></span><div class="mt-1"><?=badge($l['status'])?> <?=e($l['note']??'')?></div></div></li><?php endforeach; ?>
<li><span class="avatar"><i class="bi bi-flag"></i></span><div class="bubble"><span class="who"><?=e($inc['reporter_name'])?></span><span class="when"><?=ts($inc['created_at'])?></span><div class="mt-1"><?=badge('Reported')?> Incident reported. Security notified.</div></div></li></ul></div>

<div class="card p-4"><div class="card-title-row"><h6>Comments</h6><span class="role-tag"><?=count($inc['comments']??[])?></span></div>
<?php if(!count($inc['comments']??[])): ?><div class="empty py-4"><i class="bi bi-chat-dots"></i>No comments yet.</div><?php endif; ?>
<ul class="timeline"><?php foreach($inc['comments']??[] as $c): ?><li><span class="avatar"><?=e(initials($c['by']))?></span><div class="bubble"><span class="who"><?=e($c['by'])?></span><span class="when"><?=ts($c['at'])?></span><div class="mt-1"><?=nl2br(e($c['text']))?></div></div></li><?php endforeach; ?></ul>
<?php if(!is_admin()): ?><form method="post" class="mt-2"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="action" value="comment"><div class="input-group"><input class="form-control" name="text" placeholder="Add a comment..." aria-label="Add a comment" required><button class="btn btn-danger"><i class="bi bi-send me-1"></i>Post</button></div></form><?php endif; ?></div></div>

<div class="col-lg-4"><div class="card p-4 mb-4"><div class="card-title-row"><h6>Progress</h6></div><div class="status-track">
<?php $stIdx=array_search($inc['status'],STATS); foreach(STATS as $k=>$x): ?><div class="st <?=$k<$stIdx?'done':($k==$stIdx?'now':'')?>"><span class="dot"></span><?=$x?></div><?php endforeach; ?></div>
<div class="small text-muted mb-1"><i class="bi bi-person-badge me-1"></i>Allocated to: <strong class="text-dark"><?=e($inc['assigned_to']??'Waiting for security to allocate')?></strong></div>
<?php if(!empty($inc['assigned_by'])): ?><div class="small text-muted"><i class="bi bi-person-check me-1"></i>Allocated by: <strong class="text-dark"><?=e($inc['assigned_by'])?></strong> &middot; <?=ts($inc['assigned_at']??null)?></div><?php endif; ?>
<?php $lt=latest($inc); if($lt): ?><div class="mt-3 pt-3 border-top"><div class="section-label mb-1">Latest update from security</div><div class="small"><?=badge($lt['status'])?> <?=e($lt['note'])?></div><div class="row-sub"><?=e($lt['by'])?> &middot; <?=ts($lt['at'])?></div></div><?php endif; ?></div>
<?php if(is_security()): ?>
<?php if(empty($inc['assigned_to'])): ?><div class="alert alert-warning small"><i class="bi bi-hourglass-split me-1"></i>This incident has not been allocated yet. Allocate it to a person first.</div><?php endif; ?>
<div class="card p-4 mb-4"><div class="card-title-row"><h6>Step 1 &middot; Allocate to</h6></div><form method="post"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="action" value="assign">
<input class="form-control mb-2" name="assignee" list="people" value="<?=e($inc['assigned_to']??'')?>" placeholder="Person name" maxlength="60" required aria-label="Person name"><datalist id="people"><?php foreach($names as $n): ?><option value="<?=e($n)?>"><?php endforeach; ?></datalist>
<input class="form-control mb-3" name="note" placeholder="Instruction (optional)" maxlength="200" aria-label="Instruction"><button class="btn btn-dark w-100"><?=empty($inc['assigned_to'])?'Allocate':'Re-allocate'?></button>
<div class="hint">Only the name is needed. The person does not need a login. Student/staff who reported this will see it.</div></form></div>
<div class="card p-4"><div class="card-title-row"><h6>Step 2 &middot; Update progress</h6></div><form method="post"><input type="hidden" name="t" value="<?=csrf()?>"><input type="hidden" name="action" value="status">
<select name="status" class="form-select mb-2" aria-label="Status"><?php foreach(STATS as $x): ?><option <?=$inc['status']==$x?'selected':''?>><?=$x?></option><?php endforeach; ?></select>
<textarea class="form-control mb-3" name="note" rows="3" maxlength="400" placeholder="Progress note, e.g. Team reached the spot" aria-label="Progress note"></textarea><button class="btn btn-danger w-100">Save progress</button></form></div>
<?php elseif(is_admin()): ?><div class="card p-4"><div class="small text-muted"><i class="bi bi-eye me-1"></i>Admin view: allocation and progress are handled by security officers.</div></div><?php endif; ?></div></div>
<?php require 'includes/footer.php'; ?>
