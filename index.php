<?php $title='Home'; require 'includes/header.php'; ?>
<section class="hero text-center mb-5">
<span class="eyebrow"><span class="pulse"></span> Campus safety, always on</span>
<h1>Report. Respond. <span>Resolve.</span></h1>
<p class="lead">A single place for students, staff and security to report and track campus emergencies in real time.</p>
<?php if(!user()): ?><a href="register.php" class="btn btn-light btn-lg me-2 mb-2">Create account</a><a href="login.php" class="btn btn-outline-light btn-lg mb-2">Login</a>
<?php else: ?><?php if(canreport()): ?><a href="incident_form.php" class="btn btn-light btn-lg me-2 mb-2"><i class="bi bi-exclamation-triangle me-1"></i> Report Incident</a><?php endif; ?><a href="dashboard.php" class="btn btn-outline-light btn-lg mb-2">Open dashboard</a><?php endif; ?></section>

<div class="row g-4 mb-5"><?php foreach([['bi-lightning-charge','Fast Reporting','Submit an incident in under a minute with category, severity and location.'],['bi-diagram-3','Smart Tracking','Severity levels and a clear status workflow from report to resolution.'],['bi-bar-chart-line','Live Dashboard','Charts and statistics that help security staff prioritise what matters.']] as $c): ?>
<div class="col-md-4"><div class="card feature"><div class="icon-box"><i class="bi <?=$c[0]?>"></i></div><h5><?=$c[1]?></h5><p><?=$c[2]?></p></div></div><?php endforeach; ?></div>

<div class="card p-4 p-lg-5"><div class="text-center mb-4"><div class="section-label mb-1">How it works</div><h4 class="mb-0">From report to resolution in three steps</h4></div>
<div class="row g-4 steps"><?php foreach([['Report','Describe what happened and where. Mark the severity so responders know the urgency.'],['Respond','Security officers allocate it to a responder and keep the progress updated; admin can see everything.'],['Resolve','Follow updates and comments until the incident is resolved and closed.']] as $s): ?>
<div class="col-md-4"><div class="step"><div><h6><?=$s[0]?></h6><p><?=$s[1]?></p></div></div></div><?php endforeach; ?></div></div>
<?php require 'includes/footer.php'; ?>
