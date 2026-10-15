<?php // Run once: php seed.php        (add --reset to wipe users + incidents first)
require __DIR__.'/config.php';
if(in_array('--reset',$argv??[])){$db->users->drop();$db->incidents->drop();echo "Old users and incidents removed.\n";}
$db->users->createIndex(['email'=>1],['unique'=>true]);
$db->incidents->createIndex(['status'=>1,'severity'=>1]);$db->incidents->createIndex(['created_at'=>-1]);
$db->users->deleteMany(['email'=>['$in'=>['admin@campus.edu','security@campus.edu','student@campus.edu','staff@campus.edu']]]); // old demo accounts
$now=new MongoDB\BSON\UTCDateTime();
$ago=fn($min)=>new MongoDB\BSON\UTCDateTime((time()-$min*60)*1000);

// name, email, role, ID  (password = <role>123)
$people=[
 ['Admin Suresh','admin.suresh@campus.edu','admin','ADM001'],
 ['Security Bala','security.bala@campus.edu','security','SEC101'],
 ['Security Murugan','security.murugan@campus.edu','security','SEC102'],
 ['Security Selvam','security.selvam@campus.edu','security','SEC103'],
 ['Security Anand','security.anand@campus.edu','security','SEC104'],
 ['Student Renu','student.renu@campus.edu','student','21CS101'],
 ['Student Divya','student.divya@campus.edu','student','21EC102'],
 ['Student Arun','student.arun@campus.edu','student','21ME103'],
 ['Student Kavin','student.kavin@campus.edu','student','21IT104'],
 ['Staff Lakshmi','staff.lakshmi@campus.edu','staff','EMP101'],
 ['Staff Ramesh','staff.ramesh@campus.edu','staff','EMP102'],
 ['Staff Priya','staff.priya@campus.edu','staff','EMP103'],
 ['Staff Vignesh','staff.vignesh@campus.edu','staff','EMP104'],
];
$ids=[];
foreach($people as [$n,$em,$r,$pid]){
 $db->users->updateOne(['email'=>$em],['$set'=>['name'=>$n,'role'=>$r,'person_id'=>$pid,'password'=>password_hash($r.'123',PASSWORD_DEFAULT)],'$setOnInsert'=>['created_at'=>$now]],['upsert'=>true]);
 $ids[$em]=(string)$db->users->findOne(['email'=>$em])['_id'];
}
$name=fn($em)=>array_values(array_filter($people,fn($p)=>$p[1]===$em))[0][0];

if(!$db->incidents->countDocuments()){
 // title, category, severity, location, description, reporter email, status, [assignee, allocated-by email, instruction], [[status,note,by email,minutes ago],...], reported minutes ago
 $inc=[
  ['Fire alarm in lab','Fire','High','Block C, Lab 2','Fire alarm is ringing continuously and there is a burning smell near the power sockets.','student.renu@campus.edu','In Progress',['Fire Safety Team - Mani','security.murugan@campus.edu','Check the alarm panel and evacuate the lab'],[['In Progress','Lab evacuated. Fire safety team is checking the wiring.','security.murugan@campus.edu',150]],180],
  ['Student fainted in canteen','Medical','Critical','Main Canteen','A student collapsed near the billing counter and is not responding properly.','staff.lakshmi@campus.edu','Resolved',['Security Selvam','security.selvam@campus.edu','Take the student to the campus clinic'],[['In Progress','Student shifted to the campus clinic.','security.selvam@campus.edu',1400],['Resolved','Student is stable and parents have been informed.','security.selvam@campus.edu',1300]],1440],
  ['Bike stolen','Theft','Medium','Parking Lot B','My bike was parked near the pillar at 9 AM and is missing now. Lock was intact earlier.','student.arun@campus.edu','Reported',null,[],90],
  ['Broken staircase railing','Infrastructure','Low','Block A','The railing on the second floor staircase is loose and may fall.','staff.ramesh@campus.edu','In Progress',['Maintenance - Raju','security.anand@campus.edu','Fix the railing and barricade the stairs until then'],[['In Progress','Stairs barricaded. Waiting for welding material.','security.anand@campus.edu',500]],2900],
  ['Water leakage in corridor','Infrastructure','Medium','Library, 1st Floor','Water is leaking from the ceiling and the floor is slippery.','student.divya@campus.edu','Reported',null,[],45],
  ['Harassment near bus stop','Harassment','High','Main Gate Bus Stop','Some outsiders are teasing girls waiting at the bus stop in the evening.','staff.priya@campus.edu','In Progress',['Security Anand','security.bala@campus.edu','Patrol the gate area from 4 PM to 7 PM'],[['In Progress','Patrol started. CCTV footage is being checked.','security.anand@campus.edu',600]],1000],
  ['Accident on hostel road','Accident','High','Hostel Road','Two students collided on bikes near the speed breaker. One has a leg injury.','student.kavin@campus.edu','Closed',['Security Bala','security.bala@campus.edu','Arrange first aid and an ambulance'],[['In Progress','First aid given, ambulance called.','security.bala@campus.edu',4300],['Resolved','Student treated and discharged.','security.bala@campus.edu',4100],['Closed','Report filed with the dean office.','security.bala@campus.edu',3900]],4400],
  ['Suspicious person near Block B','Other','Medium','Block B Entrance','An unknown person has been waiting near the entrance without an ID card.','security.selvam@campus.edu','Resolved',['Security Murugan','security.anand@campus.edu','Verify identity and escort out if needed'],[['Resolved','Visitor was verified, came to meet a faculty member.','security.murugan@campus.edu',2700]],2800],
 ];
 foreach($inc as [$t,$c,$sv,$l,$d,$rep,$st,$as,$steps,$age]){
  $log=[];$doc=['title'=>$t,'category'=>$c,'severity'=>$sv,'location'=>$l,'description'=>$d,'status'=>$st,'reporter_id'=>$ids[$rep],'reporter_name'=>$name($rep),'assigned_to'=>null,'comments'=>[],'created_at'=>$ago($age),'updated_at'=>$ago($age)];
  if($as){[$who,$by,$note]=$as;$doc['assigned_to']=$who;$doc['assigned_by']=$name($by);$doc['assigned_at']=$ago($age-10);
   $log[]=['type'=>'assign','status'=>'In Progress','note'=>"Allocated to $who: $note",'by'=>$name($by),'at'=>$ago($age-10)];}
  foreach($steps as [$s2,$n2,$b2,$m2])$log[]=['type'=>'status','status'=>$s2,'note'=>$n2,'by'=>$name($b2),'at'=>$ago($m2)];
  $doc['progress']=$log;
  $db->incidents->insertOne($doc);
 }
}
echo "Seeded.\nPasswords: admin123 / security123 / student123 / staff123 (by role)\n";
foreach($people as [$n,$em,$r])echo str_pad(ROLE_LABELS[$r],16).str_pad($n,18).$em."\n";
