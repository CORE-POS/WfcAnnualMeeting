<?php

include('print.php');
include('lib/display.php');
set_time_limit(0);

include('lib/db.php');
$db = db();

if (isset($_REQUEST['checkin'])){

    $cn = $_REQUEST['cn'];
    $pinfo = array();
    $pinfo['meals'] = array();
    $pinfo['card_no'] = $cn;
    $pinfo['amt'] = $_REQUEST['ttldue'];

    $q = "DELETE FROM regmeals WHERE card_no=".$cn;
    $r = $db->query($q);
    for($i=0;$i<count($_REQUEST['am']);$i++){
        $q = sprintf("INSERT INTO regmeals VALUES (%d,'%s',%d)",
            $cn,($i==0?'OWNER':'GUEST'),$_REQUEST['am'][$i]);
        $r = $db->query($q);
        if ($_REQUEST['am'][$i] == 1)
            $pinfo['meals'][] = 'meat';
        elseif($_REQUEST['am'][$i] == 2)
            $pinfo['meals'][] = 'veg';
        elseif ($_REQUEST['am'][$i] == 3)
            $pinfo['meals'][] = 'nmeat';
        else
            $pinfo['meals'][] = 'wveg';
    }
    if (isset($_REQUEST['km'])){
        foreach($_REQUEST['km'] as $km){
            if ($km == 0) continue;
            $q = "INSERT INTO regmeals VALUES ($cn,'CHILD',0)";
            $r = $db->query($q);
            $pinfo['meals'][] = 'kid';
        }
    }
    for($i=0;$i<$_REQUEST['chicken'];$i++){
        $q = "INSERT INTO regmeals VALUES ($cn,'GUEST',1)";
        $r = $db->query($q);
        $pinfo['meals'][] = 'meat';
    }
    for($i=0;$i<$_REQUEST['veg'];$i++){
        $q = "INSERT INTO regmeals VALUES ($cn,'GUEST',2)";
        $r = $db->query($q);
        $pinfo['meals'][] = 'veg';
    }
    for($i=0;$i<$_REQUEST['mgf'];$i++){
        $q = "INSERT INTO regmeals VALUES ($cn,'GUEST',3)";
        $r = $db->query($q);
        $pinfo['meals'][] = 'nmeat';
    }
    for($i=0;$i<$_REQUEST['vgf'];$i++){
        $q = "INSERT INTO regmeals VALUES ($cn,'GUEST',3)";
        $r = $db->query($q);
        $pinfo['meals'][] = 'wveg';
    }
    for($i=0;$i<$_REQUEST['kids'];$i++){
        $q = "INSERT INtO regmeals VALUES ($cn,'CHILD',0)";
        $r = $db->query($q);
        $pinfo['meals'][] = 'kid';
    }
    $q = "UPDATE registrations SET checked_in=1 WHERE card_no=".$cn;
    $r = $db->query($q);
    print_info($pinfo);
    header("Location: index.php");
    exit;
}
else if (isset($_REQUEST['back'])){
    header("Location: index.php");
    exit;
}

$cn = (int)$_REQUEST['cn'];

$q = "SELECT name,guest_count,child_count,1 as paid,checked_in
    FROM registrations WHERE card_no=".$cn;
$r = $db->query($q);
$regW = $db->fetch_row($r);

$q = "SELECT t.typeDesc,m.subtype FROM regmeals as m
    LEFT JOIN mealtype AS t ON m.subtype=t.id
    WHERE m.card_no=".$cn;
$r = $db->query($q);
$adult = array();
$kids = array();
while($w = $db->fetch_row($r)){
    if ($w[1] > 0)
        $adult[] = $w;
    else
        $kids[] = $w;
}

$meals = array();
$q = 'SELECT id as subtype, typeDesc FROM mealtype WHERE id > 0 ORDER BY id';
$r = $db->query($q);
while ($w = $db->fetch_row($r)) {
    $meals[$w['subtype']] = $w['typeDesc'];
}
?>
<!doctype html>
<html>
<head>
    <title>Annual Meeting</title>
    <link rel="stylesheet" type="text/css" href="vendor/components/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/components/bootstrap/css/bootstrap-theme.min.css">
    <script type="text/javascript" src="vendor/components/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="vendor/components/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript">
function reCalc(){
    var c = document.getElementById('chicken').value;
    var v = document.getElementById('veg').value;
    var g = document.getElementById('mgf').value;
	var w = document.getElementById('vgf').value;
    var b = document.getElementById('basedue').value;
    var k = document.getElementById('kis').value;

	var due = (c*20) + (v*20) + (g*20) + (w*20) + (k*5) + (1*b);
    document.getElementById('amtdue').innerHTML='$'+due;
    document.getElementById('ttldue').value = due;
}
</script>
</head>
<body onload="document.getElementById('chicken').focus();">
<div class="container">
    <form method="post" action="edit.php">
    <div class="col-sm-6">
    <table class="table">
    <?php if ($regW['checked_in'] != 0){ 
        echo '<tr><th colspan="2" style="color:red;">ALREADY CHECKED IN</th></tr>';
    } ?>
    <tr><th>Name</th><td><?php echo $regW['name']; ?></td></tr>
    <tr><th>Paid</th><td><?php echo ($regW['paid']==1?'Yes':'No'); ?></td></tr>
    <tr><th>Guests</th><td><?php echo $regW['guest_count']; ?></td></tr>
    <tr><th>Children</th><td><?php echo $regW['child_count']; ?></td></tr>
    <tr><td colspan="2" align="center">Registered Meals</td></tr>
    <?php foreach ($adult as $a){
    echo '<tr><th>Adult Meal</th><td><select name="am[]" class="form-control">';
    foreach ($meals as $id => $name) {
        printf('<option %s value="%d">%s</option>',
            ($a[1] == $id ? 'selected' : ''), $id, $name);
    }
    echo '</select></td></tr>';
    } ?>
    <?php foreach ($kids as $k){
    echo '<tr><th>Child Meal</th><td><select name="km[]" class="form-control">';
    echo '<option value="1" selected>Spaghetti</option><option value="0">None</option>';
    echo '</select></td></tr>';
    } ?>
    <tr><td colspan="2" align="center">Additional Meals</td></tr>
    <tr><th><?php echo $meals[1]; ?></th>
        <td><input type="text" class="form-control" name="chicken" id="chicken" value="0" onchange="reCalc(); "/></td></tr>
    <tr><th><?php echo $meals[2]; ?></th><td>
        <input type="text" class="form-control" name="veg" id="veg" onchange="reCalc();" value="0" /></td></tr>
    <tr><th><?php echo $meals[3]; ?></th>
        <td><input type="text" class="form-control" name="mgf" id="mgf" onchange="reCalc();" value="0" /></td></tr>
    <tr><th><?php echo $meals[4]; ?></th>
        <td><input type="text" class="form-control" name="vgf" id="vgf" onchange="reCalc();" value="0" /></td></tr>
    <tr><th>Spaghetti</th><td>
        <input type="text" class="form-control" name="kids" id="kids" onchange="reCalc();" value="0" /></td></tr>
    <tr><th>Amount Due</th><td id="amtdue">$<?php echo ($regW['paid']==1?0:20*$regW['guest_count']); ?></td></tr>
    <input type="hidden" id="basedue" name="basedue" value="<?php echo ($regW['paid']==1?0:20*$regW['guest_count']); ?>" />
    <input type="hidden" id="ttldue" name="ttldue" value="<?php echo ($regW['paid']==1?0:20*$regW['guest_count']); ?>" />
    <input type="hidden" name="cn" value="<?php echo $cn; ?>" />
    </table>
    </div>

    <div class="col-sm-6">
    <table class="table">
    <tr><th>Meal</th><th>Pending</th><th>Checked-in</th></tr>
    <?php foreach (getArrivedStatus($db) as $row) { ?>
        <tr>
            <td><?php echo $row['typeDesc']; ?></td>
            <td><?php echo $row['pending']; ?></td>
            <td><?php echo $row['arrived']; ?></td>
        </tr>
    <?php } ?>
    </table>
    <button type="submit" name="checkin" value="Check In"
        class="btn btn-primary">Check In</button>
    <button type="submit" name="back" value="Go Back"
        class="btn btn-default">Go Back</button>
    </div>
    </form>
</div>
</body>
</html>
