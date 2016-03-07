<?php
session_start();
include 'library/service.php';
include 'library/walletpayment.php';
$config = include('config.php');
if ($_POST) {
    //1. getBalance api
    $url = $config['payment_getBalance'];
    $data = $_POST;
    $_SESSION['info'] = $data;
    $params = array(
        "person_id" => intval($data['persionid']),
    );
    $result = CallAPI('POST', $url, json_encode($params));
    $currentBalance = floatval($result);
    $_SESSION['info']['currentBalance'] = $currentBalance;

    $isValidBalance = 0;
    //2. If It is enough amount(Fee<= current_balance)
    if (floatval($data['fee']) <= $currentBalance) {
        walletPayment($config, $data);
    } else {
        $url = '/index.php';
        header("Location: " . $url);
    }
}
?>
<?php ?>

<html>
    <head>

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Wellcare</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <link rel="stylesheet" type="text/css" href="default/css/payment.css">
        <link rel="stylesheet" type="text/css" href="default/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="default/css/bootstrap-theme.min.css">
        <script type="text/javascript" src="default/js/jquery-1.12.1.min.js"></script>
        <script type="text/javascript" src="default/js/bootstrap.min.js"></script>

    </head>
    <body>
        <div class="container">

            <form action="" method="POST" name="frmInputPatient" id="frmInputPatient">
                <div class="form-group">
                    <label for="name">Bác sĩ</label>
                    <input type="text" class="form-control" value="Nguyễn Hoàng Anh" id="name" name="name" placeholder="Bác sĩ">
                </div>
                <div class="form-group">
                    <label for="fee">Phí khám</label>
                    <input type="tel" class="form-control" value="150000" name="fee" id="fee" placeholder="Phí khám">
                </div>
                <div class="form-group">
                    <label for="persionid">Person ID</label>
                    <input type="tel" class="form-control" value="1" name="persionid" id="persionid" placeholder="Person ID">
                </div>
                <div class="form-group">
                    <label for="exampleInputFile">Visit ID</label>
                    <input type="tel" class="form-control" name="visitid" id="visitid" placeholder="Visit ID">    
                </div>         
                <button type="submit" class="btn btn-default">Submit</button>
            </form>
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                $(".bank").click(function() {
                    $('#bankcode').val($(this).attr('data-code'));
                    $("#frmPayment").submit();
                });

            });
            function validateForm() {
                return true;
            }

        </script>
    </body>
</html>


