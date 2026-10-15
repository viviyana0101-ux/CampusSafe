<?php require 'config.php'; need(); $title='Dashboard';
$f=seeall()?[]:['reporter_id'=>user()['id']];
$agg=function($k)use($db,$f){$o=[];foreach($db->incidents->aggregate([['$match'=>(object)$f],['$group'=>['_id'=>'$'.$k,'c'=>['$sum'=>1]]]]) as $r)$o[$r['_id']]=$r['c'];return $o;};
$st=$agg('status');$sv=$agg('severity');$ct=$agg('category');
$recent=$db->incidents->find($f,['sort'=>['created_at'=>-1],'limit'=>5]);
$unassigned=seeall()?$db->incidents->countDocuments(['assigned_to'=>null]):0;
$wl=seeall()?iterator_to_array($db->incidents->aggregate([['$match'=>['assigned_to'=>['$ne'=>null]]],['$group'=>['_id'=>'$assigned_to','total'=>['$sum'=>1],'open'=>['$sum'=>['$cond'=>[['$in'=>['$status',['Reported','In Progress']]],1,0]]]]],['$sort'=>['open'=>-1,'total'=>-1]],['$limit'=>8]]),false):[];
require 'includes/header.php'; ?>
<?php $tone=['Reported'=>['bi-inbox','secondary'],'In Progress'=>['bi-arrow-repeat','primary'],'Resolved'=>['bi-check-circle','success'],'Closed'=>['bi-archive','dark']]; $total=array_sum($st); ?>
<div class="page-head"><div><h3>Dashboard</h3><p><?=seeall()?'Overview of all campus incidents':'Overview of your reported incidents'?> &middot; <?=$total?> total</p></div>
<?php if(canreport()): ?><a href="incident_form.php" class="btn btn-danger"><i class="bi bi-plus-lg me-1"></i> Report Incident</a><?php endif; ?></div>
<?php if($unassigned): ?><div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2"><div><i class="bi bi-hourglass-split me-2"></i><strong><?=$unassigned?></strong> incident<?=$unassigned==1?'':'s'?> waiting to be allocated.</div><a class="btn btn-sm btn-outline-dark" href="incidents.php?assigned=none">View</a></div><?php endif; ?>
<div class="row g-3 mb-4"><?php foreach(STATS as $s): ?><div class="col-6 col-lg-3"><div class="card stat"><div class="s-icon tone-<?=$tone[$s][1]?>"><i class="bi <?=$tone[$s][0]?>"></i></div><div><div class="s-label"><?=$s?></div><div class="s-value"><?=$st[$s]??0?></div></div></div></div><?php endforeach; ?></div>
<div class="row g-3 mb-4"><div class="col-lg-5"><div class="card p-4 h-100"><div class="card-title-row"><h6>Incidents by severity</h6></div><?php if($sv): ?><div class="chart-box"><canvas id="a"></canvas></div><?php else: ?><div class="empty"><i class="bi bi-pie-chart"></i>No data yet</div><?php endif; ?></div></div>
<div class="col-lg-7"><div class="card p-4 h-100"><div class="card-title-row"><h6>Incidents by category</h6></div><?php if($ct): ?><div class="chart-box"><canvas id="b"></canvas></div><?php else: ?><div class="empty"><i class="bi bi-bar-chart"></i>No data yet</div><?php endif; ?></div></div></div>
<?php if(seeall()): ?><div class="card mb-4"><div class="p-4 pb-2"><div class="card-title-row mb-0"><h6>Allocation &amp; workload</h6></div></div>
<div class="table-responsive"><table class="table mb-0"><thead><tr><th>Allocated to</th><th>Open</th><th>Total</th></tr></thead><tbody>
<?php foreach($wl as $w): ?><tr><td><i class="bi bi-person-check text-muted me-1"></i><?=e($w['_id'])?></td><td><?=$w['open']?></td><td><?=$w['total']?></td></tr><?php endforeach; if(!$wl): ?><tr><td colspan="3"><div class="empty"><i class="bi bi-person"></i>No incidents allocated yet</div></td></tr><?php endif; ?></tbody></table></div></div><?php endif; ?>
<div class="card"><div class="p-4 pb-2"><div class="card-title-row mb-0"><h6>Recent incidents</h6><a href="incidents.php" class="small fw-semibold">View all <i class="bi bi-arrow-right"></i></a></div></div>
<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Incident</th><th>Location</th><th>Severity</th><th>Status</th><th>Allocated to</th><th>Reported</th></tr></thead><tbody>
<?php $n=0; foreach($recent as $i): $n++; ?><tr><td class="sev-bar <?=sevclass($i['severity'])?>"><a class="row-title" href="incident_view.php?id=<?=$i['_id']?>"><?=e($i['title'])?></a><div class="row-sub"><?=e($i['category'])?></div><?=latest_line($i)?></td><td><i class="bi bi-geo-alt text-muted me-1"></i><?=e($i['location'])?></td><td><?=badge($i['severity'])?></td><td><?=badge($i['status'])?></td><td><?=assignee($i)?></td><td class="text-muted small"><?=ts($i['created_at'])?></td></tr><?php endforeach; if(!$n): ?><tr><td colspan="6"><div class="empty"><i class="bi bi-inbox"></i><strong>No incidents yet</strong>Reported incidents will appear here.</div></td></tr><?php endif; ?></tbody></table></div></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family="Inter,system-ui,sans-serif";Chart.defaults.color='#64748b';
const sevColor={Low:'#22c55e',Medium:'#eab308',High:'#f97316',Critical:'#dc2626'};
const sevLabels=<?=json_encode(array_keys($sv))?>;
if(document.getElementById('a'))new Chart(a,{type:'doughnut',data:{labels:sevLabels,datasets:[{data:<?=json_encode(array_values($sv))?>,backgroundColor:sevLabels.map(l=>sevColor[l]||'#94a3b8'),borderWidth:3,borderColor:'#fff',hoverOffset:6}]},options:{maintainAspectRatio:false,cutout:'68%',plugins:{legend:{position:'bottom',labels:{usePointStyle:true,pointStyle:'circle',padding:16}}}}});
if(document.getElementById('b'))new Chart(b,{type:'bar',data:{labels:<?=json_encode(array_keys($ct))?>,datasets:[{label:'Incidents',data:<?=json_encode(array_values($ct))?>,backgroundColor:'#c62828',borderRadius:8,maxBarThickness:42}]},options:{maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0},grid:{color:'#eef1f6'},border:{display:false}},x:{grid:{display:false}}}}});
</script>
<?php require 'includes/footer.php'; ?>
