<?php
session_start();
$message='';
$error_code=$_GET['c'];
$amount=$_SESSION['data_result']['amount'];
if($error_code==1){
    $message="Thanh toán ".$amount." thành công";
}
else{
    $message="Thanh toán ".$amount." không thành công.";
}
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
                <?php echo $message; ?>
            </div>
        </div>   
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


