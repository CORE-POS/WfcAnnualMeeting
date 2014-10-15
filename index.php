<?php
include('print.php');
include('db.php');
include('lib/display.php');

ini_set('display_errors','on');
set_time_limit(0);
$dbc = db();
if (isset($_REQUEST['cn'])) {
	$cn = $_REQUEST['cn'];
	$found = false;
	$db = $dbc;
    $json = array('member' => false, 'html' => '');
	if (is_numeric($cn)) {
		$q = sprintf("SELECT CardNo FROM custdata
			WHERE CardNo=%d",$cn);
		$r = $db->query($q);
		if ($db->num_rows($r) > 0) {
            $json['member'] = sprintf('%d', $cn);
		} else {
			$q2 = sprintf("SELECT card_no FROM
				membercards WHERE upc='%s'",
				str_pad($cn,13,'0',STR_PAD_LEFT));
			$r2 = $db->query($q2);
			if ($db->num_rows($r2) > 0) {
				$row = $db->fetch_row($r2);
                $json['member'] = $row['card_no'];
            }
		}
	} else {
		$q = sprintf("SELECT CardNo,
                LastName,
                FirstName,
                personNum,
                CASE WHEN r.card_no IS NULL THEN 'z alert-danger' 
                    WHEN r.card_no IS NOT NULL AND personNum=1 THEN 'a alert-success' 
                    ELSE 'b alert-warning' END AS css
			FROM custdata AS c
                LEFT JOIN registrations AS r ON c.CardNo=r.card_no
            WHERE LastName LIKE '%s%%'
			ORDER BY css, LastName,FirstName",
			$db->escape($cn));
		$r = $db->query($q);
		if ($db->num_rows($r) > 0) {
			$found = true;
            ob_start();
            ?>
		    <form onsubmit="location='edit.php?cn='+$('#cn-select').val(); return false;"
                class="form-inline">
            <label>Multiple Matches</label>
		    <select id="cn-select" class="form-control">
            <?php while($w = $db->fetch_row($r)) {
                if ($w['personNum'] == 1) {
                    echo '<strong>';
                }
                printf('<option class="%s" value="%d">%d %s, %s</option>',
                    $w['css'],
                    $w['CardNo'],$w['CardNo'], $w['LastName'],
                    $w['FirstName']);
                if ($w['personNum'] == 1) {
                    echo '</strong>';
                }
		    } ?>
		    </select>
            <button type="submit" class="btn btn-default">Proceed</button>
		    </form>
		    <?php
            $json['html'] = ob_get_clean();
		} else {
            $json['html'] = $q;
        }
	}
    if ($json['member'] === false && $json['html'] === '') {
        $json['html'] = '<div class="alert alert-danger">No owner found</div>';
    }
    echo json_encode($json);

    return;
}
?>
<!doctype html>
<html>
<head>
    <title>Annual Meeting</title>
    <link rel="stylesheet" type="text/css" href="components/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="components/bootstrap/css/bootstrap-theme.min.css">
    <script type="text/javascript" src="components/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="components/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript">
    function lookupMember()
    {
        var memNum = $('#cn').val();
        $.ajax({
            type: 'get',
            data: 'cn='+memNum,
            dataType: 'json',
            success: function(resp) {
                if (resp.member) {
                    location = 'edit.php?cn='+resp.member;
                } else {
                    $('#member-lookup-area').html(resp.html);
                    if ($('#cn-select').length) {
                        $('#cn-select').focus();
                    }
                }
            }
        });
    }
    </script>
</head>
<body onload="document.getElementById('cn').focus();">
<div class="container">
<?php

/*
$in = array();
$in['meals'] = array('meat');
$in['card_no'] = 10000;

print_info($in);
*/

?>
<h1>Annual Meeting Check-in</h1>
<div class="row">
    <div class="col-sm-6">
        <form class="form" onsubmit="lookupMember(); return false;">
        <label>Enter owner# or card# or last name</label>:
        <input type="text" id="cn" name="cn" class="form-control" />
        <button type="submit" class="btn btn-primary">Proceed</button>
        </form>
        <hr />
        <div id="member-lookup-area">
        </div>
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
    </div>
</div>
</body>
</html>
