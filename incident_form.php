<?php require 'config.php'; need(['student','staff','security']);
$id=oid($_GET['id']??'');$inc=$id?$db->incidents->findOne(['_id'=>$id]):null;
if($id&&!$inc)exit('Not found');
if($inc&&!is_security()&&!($inc['reporter_id']===user()['id']&&$inc['status']==='Reported')){http_response_code(403);exit('You can only edit your own incident while it is still "Reported".');}
$err='';
if($_SERVER['REQUEST_METHOD']=='POST'){chk();
 $d=['title'=>trim($_POST['title']),'category'=>$_POST['category'],'severity'=>$_POST['severity'],'location'=>trim($_POST['location']),'description'=>trim($_POST['description'])];
 if(!$d['title']||!$d['location']||!$d['description']||!in_array($d['category'],CATS)||!in_array($d['severity'],SEVS))$err='Please fill all fields correctly.';
 elseif($inc){$d['updated_at']=new MongoDB\BSON\UTCDateTime();$db->incidents->updateOne(['_id'=>$id],['$set'=>$d]);flash('Incident updated.');header('Location: incident_view.php?id='.$id);exit;}
 else{$d+=['status'=>'Reported','reporter_id'=>user()['id'],'reporter_name'=>user()['name'],'assigned_to'=>null,'comments'=>[],'created_at'=>new MongoDB\BSON\UTCDateTime()];
  $r=$db->incidents->insertOne($d);flash('Incident reported. Security has been notified.');header('Location: incident_view.php?id='.$r->getInsertedId());exit;}}
$v=fn($k)=>e($_POST[$k]??($inc[$k]??''));
$title=$inc?'Edit Incident':'Report Incident'; require 'includes/header.php'; ?>
<div class="row justify-content-center"><div class="col-lg-8">
<a href="<?=$inc?'incident_view.php?id='.$inc['_id']:'incidents.php'?>" class="breadcrumb-link"><i class="bi bi-arrow-left"></i> Back</a>
<div class="card p-4 p-lg-5"><div class="mb-4"><h4 class="mb-1"><?=$title?></h4><p class="text-muted mb-0"><?=$inc?'Update the details of this incident.':'Provide as much detail as you can so responders can act quickly.'?></p></div>
<?php if($err): ?><div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-triangle-fill me-2"></i><div><?=e($err)?></div></div><?php endif; ?>
<form method="post"><input type="hidden" name="t" value="<?=csrf()?>">
<div class="mb-3"><label class="form-label" for="title">Title</label><input id="title" class="form-control" name="title" value="<?=$v('title')?>" placeholder="Short summary of what happened" required></div>
<div class="row"><div class="col-md-4 mb-3"><label class="form-label" for="category">Category</label><select id="category" name="category" class="form-select"><?php foreach(CATS as $x): ?><option <?=$v('category')==$x?'selected':''?>><?=$x?></option><?php endforeach; ?></select></div>
<div class="col-md-4 mb-3"><label class="form-label" for="severity">Severity</label><select id="severity" name="severity" class="form-select"><?php foreach(SEVS as $x): ?><option <?=$v('severity')==$x?'selected':''?>><?=$x?></option><?php endforeach; ?></select></div>
<div class="col-md-4 mb-3"><label class="form-label" for="location">Location</label><div class="input-icon"><i class="bi bi-geo-alt"></i><input id="location" class="form-control" name="location" value="<?=$v('location')?>" placeholder="e.g. Block A, Hostel 2" required></div></div></div>
<div class="mb-4"><label class="form-label" for="description">Description</label><textarea id="description" class="form-control" rows="6" name="description" placeholder="Describe the situation, people involved and any immediate dangers" required><?=$v('description')?></textarea></div>
<div class="d-flex gap-2 justify-content-end border-top pt-3"><a href="<?=$inc?'incident_view.php?id='.$inc['_id']:'incidents.php'?>" class="btn btn-light">Cancel</a><button class="btn btn-danger"><i class="bi bi-<?=$inc?'check2':'send'?> me-1"></i><?=$inc?'Save changes':'Submit report'?></button></div></form></div></div></div>
<?php require 'includes/footer.php'; ?>
