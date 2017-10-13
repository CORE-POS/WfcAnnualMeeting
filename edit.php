<?php

include('vendor/autoload.php');
include('lib/printInfo.php');
include('lib/display.php');
set_time_limit(0);

include('lib/db.php');
$dbc = db();

/**
  Get available meals list first. These are used throughout to avoid hardcoding
  specific entrees so the entries should be fairly simple; mainly no spaces
*/
$meals = array();
$q = 'SELECT id as subtype, typeDesc FROM mealtype WHERE id > 0 ORDER BY id';
$r = $dbc->query($q);
while ($w = $dbc->fetch_row($r)) {
    $meals[$w['subtype']] = $w['typeDesc'];
}
$defaultMeal = current($meals);

if (isset($_REQUEST['checkin'])){

    $cn = $_REQUEST['cn'];
    $pinfo = array();
    $pinfo['meals'] = array();
    $pinfo['card_no'] = $cn;
    $pinfo['amt'] = $_REQUEST['ttldue'];

    /**
      Re-enter registered adult and child meals
      in case anything was changed during the check-in
    */
    $q = "DELETE FROM regmeals WHERE card_no=".$cn;
    $r = $dbc->query($q);
    for($i=0;$i<count($_REQUEST['am']);$i++){
        $q = sprintf("INSERT INTO regmeals VALUES (%d,'%s',%d)",
            $cn,($i==0?'OWNER':'GUEST'),$_REQUEST['am'][$i]);
        $r = $dbc->query($q);
        $reqMeal = $_REQUEST['am'][$i];
        if (isset($meals[$reqMeal])) {
            $pinfo['meals'][] = strtolower($meals[$reqMeals]);
        } else {
            $pinfo['meals'][] = strtolower($defaultMeal);
        }
    }
    if (isset($_REQUEST['km'])){
        foreach($_REQUEST['km'] as $km){
            if ($km == 0) continue;
            $q = "INSERT INTO regmeals VALUES ($cn,'CHILD',0)";
            $r = $dbc->query($q);
            $pinfo['meals'][] = 'kid';
        }
    }

    /**
      Look for any additional meals the person registered for
      and add them to the regMeals table
    */
    foreach ($meals as $m) {
        if (isset($_REQUEST[$m])) {
            $mealID = (int)array_search($m, $meals);
            for ($i=0; $i<$_REQUEST[$m]; $i++) {
                $q = "INSERT INTO regmeals VALUES ($cn,'GUEST',$mealID)";
                $r = $dbc->query($q);
                $pinfo['meals'][] = $m;
            }
        }
    }
    for($i=0;$i<$_REQUEST['kids'];$i++){
        $q = "INSERT INtO regmeals VALUES ($cn,'CHILD',0)";
        $r = $dbc->query($q);
        $pinfo['meals'][] = 'kid';
    }

    $q = "UPDATE registrations SET checked_in=1 WHERE card_no=".$cn;
    $r = $dbc->query($q);
    print_info($pinfo);
    header("Location: index.php");
    exit;
}

$cn = (int)$_REQUEST['cn'];
$q = "SELECT name,guest_count,child_count,paid,checked_in
    FROM registrations WHERE card_no=".$cn;
$r = $dbc->query($q);
$regW = $dbc->fetch_row($r);

$q = "SELECT t.typeDesc,m.subtype FROM regmeals as m
    LEFT JOIN mealtype AS t ON m.subtype=t.id
    WHERE m.card_no=".$cn;
$r = $dbc->query($q);
$adult = array();
$kids = array();
while($w = $dbc->fetch_row($r)){
    if ($w[1] > 0)
        $adult[] = $w;
    else
        $kids[] = $w;
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
    var due = 0;
    <?php foreach ($meals as $m) { ?>
        due += (20 * document.getElementById('<?php echo $m; ?>').value);
    <?php } ?>
    due += document.getElementById('basedue').value;
    due += (5 * document.getElementById('kis').value);

    document.getElementById('amtdue').innerHTML='$'+due;
    document.getElementById('ttldue').value = due;
}
</script>
</head>
<body onload="document.getElementById('<?php echo $defaultMeal; ?>').focus();">
<div class="container">
    <form method="post" action="edit.php">
    <div class="col-sm-6">
    <table class="table">
    <?php if ($regW['checked_in'] != 0){ 
        echo '<tr><th colspan="2" style="color:red;">ALREADY CHECKED IN</th></tr>';
    } ?>
    <tr><th>Name</th><td><?php echo $regW['name']; ?></td></tr>
    <tr class="<?php echo $regW['paid'] ? '' : 'danger'; ?>"><th>Paid</th><td><?php echo ($regW['paid']==1?'Yes':'No'); ?></td></tr>
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
    <?php foreach ($meals as $id => $meal) { ?>
        <tr><th><?php echo $meal; ?></th>
        <td><input type="text" class="form-control" name="<?php echo $meal; ?>" id="<?php echo $meal; ?>" value="0" onchange="reCalc(); "/></td></tr>
    <?php } ?>
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
    <?php foreach (getArrivedStatus($dbc) as $row) { ?>
        <tr>
            <td><?php echo $row['typeDesc']; ?></td>
            <td><?php echo $row['pending']; ?></td>
            <td><?php echo $row['arrived']; ?></td>
        </tr>
    <?php } ?>
    </table>
    <button type="submit" name="checkin" value="Check In"
        class="btn btn-primary">Check In</button>
    <a href="index.php" class="btn btn-default">Go Back</a>
    </div>
    </form>
</div>
</body>
</html>
