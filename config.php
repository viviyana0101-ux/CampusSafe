<?php
session_start();
require __DIR__.'/vendor/autoload.php';
$db = (new MongoDB\Client(getenv('MONGO_URI') ?: 'mongodb://127.0.0.1:27017'))->campus_emergency;
const CATS=['Fire','Medical','Theft','Harassment','Accident','Infrastructure','Other'];
const SEVS=['Low','Medium','High','Critical'];
const STATS=['Reported','In Progress','Resolved','Closed'];
const ROLE_LABELS=['student'=>'Student','staff'=>'Staff','security'=>'Security Officer','admin'=>'Admin'];
const SELF_REGISTER=['student','staff']; // security & admin accounts are created by the admin only
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES);}
function user(){return $_SESSION['user']??null;}
function role(){return user()['role']??'';}
function is_security(){return role()==='security';}   // allocates incidents + updates progress
function is_admin(){return role()==='admin';}         // manages users, view-only on incidents
function seeall(){return in_array(role(),['security','admin']);}          // can view every incident
function canreport(){return in_array(role(),['student','staff','security']);} // admin does not report
function need($roles=null){if(!user()){header('Location: login.php');exit;}if($roles&&!in_array(user()['role'],$roles)){http_response_code(403);exit('Access denied');}}
function csrf(){return $_SESSION['t']??($_SESSION['t']=bin2hex(random_bytes(16)));}
function chk(){if(!hash_equals(csrf(),$_POST['t']??'')){http_response_code(400);exit('Invalid CSRF token');}}
function flash($m=null){if($m){$_SESSION['f']=$m;return;}$f=$_SESSION['f']??null;unset($_SESSION['f']);return $f;}
function oid($s){try{return new MongoDB\BSON\ObjectId((string)$s);}catch(Throwable $x){return null;}}
function ts($d){return $d instanceof MongoDB\BSON\UTCDateTime?$d->toDateTime()->setTimezone(new DateTimeZone('Asia/Kolkata'))->format('d M Y, H:i'):'';}
function badge($v){return '<span class="pill pill-'.e(str_replace(' ','',$v)).'">'.e($v).'</span>';}
function initials($n){$p=preg_split('/\s+/',trim((string)$n));$r='';foreach(array_slice($p,0,2) as $w)$r.=mb_strtoupper(mb_substr($w,0,1));return $r?:'?';}
function sevclass($v){return 'sev-'.strtolower((string)$v);}
function assignee($i){return !empty($i['assigned_to'])?'<i class="bi bi-person-check me-1 text-muted"></i>'.e($i['assigned_to']):'<span class="text-muted small">Unassigned</span>';}
function latest($i){$p=isset($i['progress'])?$i['progress']->getArrayCopy():[];$l=end($p);return $l?['status'=>$l['status']??'','note'=>$l['note']??'','by'=>$l['by']??'','at'=>$l['at']??null]:null;}
function latest_line($i){$l=latest($i);return $l&&$l['note']!==''?'<div class="row-sub"><i class="bi bi-chat-left-text me-1"></i>'.e($l['by']).': '.e(mb_strimwidth($l['note'],0,60,'...')).'</div>':'';}
