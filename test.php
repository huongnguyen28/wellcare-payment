<?php
session_start();
include 'library/service.php';
$config = include('config.php');
$params = array(
    "patient_id" => 1,
    "doctor_id" => 10,
    "visit_id" => "99999",
);
$url = $config['payment_cancelBalance'];
$url = $config['payment_chargeBalance'];
$result = CallAPI('POST', $url, json_encode($params));

//$params = array(
//    "person_id" => 1,
//    "fromdate" => '',
//    "todate" => '',
//    "type" => '',
//);
//$url = $config['getAllBalance'];
//$acc = CallAPI('POST', $url, json_encode($params));

?>


<html>
<head>

    <meta http-equiv = "Content-Type" content = "text/html; charset=utf-8">
    <title>Wellcare</title>
    <meta name = "viewport" content = "width=device-width, initial-scale=1, maximum-scale=1">
    <link rel = "stylesheet" type = "text/css" href = "default/css/payment.css">
    <link rel = "stylesheet" type = "text/css" href = "default/css/bootstrap.min.css">
    <link rel = "stylesheet" type = "text/css" href = "default/css/bootstrap-theme.min.css">
    <script type = "text/javascript" src = "default/js/jquery-1.12.1.min.js"></script>
    <script type="text/javascript" src="default/js/bootstrap.min.js"></script>

</head>
<body>
<div class="container">

    <div class="">
        <div class="row">
            Result: <?php echo $result; ?>
<!--            Acc: --><?php //echo $acc; ?>
        </div>
    </div>
</div>
</body>
</html>
