<?php require_once __DIR__.'/../config.php'; $u=user(); $cur=basename($_SERVER['SCRIPT_NAME']);
$nav=fn($f)=>$cur===$f?' active" aria-current="page':''; ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($title??'CampusSafe')?> | Campus Emergency System</title>
<meta name="theme-color" content="#0f172a">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23c62828'/%3E%3Cpath d='M16 6l8 3v6c0 5-3.4 8.6-8 10-4.6-1.4-8-5-8-10V9z' fill='%23fff'/%3E%3C/svg%3E">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet"></head><body>
<div class="emergency-strip d-none d-md-block"><div class="container d-flex justify-content-between">
<span><i class="bi bi-telephone-fill me-1"></i> Life-threatening emergency? Call <a href="tel:112">112</a> immediately.</span>
<span>Campus Emergency &amp; Incident Management</span></div></div>
<nav class="navbar navbar-expand-lg app-nav"><div class="container">
<a class="navbar-brand" href="index.php"><span class="brand-mark"><i class="bi bi-shield-fill-exclamation"></i></span>CampusSafe</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#n" aria-controls="n" aria-expanded="false" aria-label="Toggle navigation"><i class="bi bi-list fs-4"></i></button>
<div id="n" class="collapse navbar-collapse"><ul class="navbar-nav me-auto ms-lg-3 gap-lg-1">
<?php if($u): ?>
<li class="nav-item"><a class="nav-link<?=$nav('dashboard.php')?>" href="dashboard.php"><i class="bi bi-grid-1x2 me-1"></i>Dashboard</a></li>
<li class="nav-item"><a class="nav-link<?=$nav('incidents.php')?>" href="incidents.php"><i class="bi bi-list-ul me-1"></i>Incidents</a></li>
<?php if(canreport()): ?><li class="nav-item"><a class="nav-link<?=$nav('incident_form.php')?>" href="incident_form.php"><i class="bi bi-plus-circle me-1"></i>Report Incident</a></li><?php endif; ?>
<?php if($u['role']=='admin'): ?><li class="nav-item"><a class="nav-link<?=$nav('users.php')?>" href="users.php"><i class="bi bi-people me-1"></i>Users</a></li><?php endif; endif; ?></ul>
<ul class="navbar-nav align-items-lg-center gap-2"><?php if($u): ?>
<li class="nav-item"><div class="user-chip"><span class="avatar"><?=e(initials($u['name']))?></span><span><div class="u-name"><?=e($u['name'])?></div><div class="u-role"><?=e(ROLE_LABELS[$u['role']]??$u['role'])?></div></span></div></li>
<li class="nav-item"><a class="btn btn-sm btn-outline-dark" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
<?php else: ?><li class="nav-item"><a class="nav-link<?=$nav('login.php')?>" href="login.php">Login</a></li><li class="nav-item"><a class="btn btn-danger btn-sm" href="register.php">Get started</a></li><?php endif; ?></ul></div></div></nav>
<main class="container py-4 py-lg-5"><?php if($f=flash()): ?><div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert"><i class="bi bi-info-circle-fill me-2"></i><div><?=e($f)?></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
