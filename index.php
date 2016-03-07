<?php
session_start();
include 'library/service.php';
include 'library/walletpayment.php';
include 'library/pg123pay.php';
//include "paynganluong.php";
$config = include('config.php');
$customerId = $_SESSION['info']['persionid'];
$productId = 100;
$customer = $_SESSION['info']['name'];
$productPrice = floatval($_SESSION['info']['fee']);
$isAddCredit = 1;
$currentBalance = floatval($_SESSION['info']['currentBalance']);
$addAmount = $productPrice - $currentBalance;
$messageError='';
?>
<?php
if ($_POST) {
    if ($_POST['bankcode'] == 'WELLCARE') {
        // 1. wellcare - checkCard
        $params = array(
            "card_info" => (string) $_POST['wellcare_card'],
        );
        //print_r($params);
        $url = $config['cardStore_checkCard'];
        $rscheckCard = CallAPI('POST', $url, json_encode($params));
        
        if ($rscheckCard == null) {
            $messageError= "Card không hợp lệ.";
            
        } else {
            $rscheckCard = json_decode($rscheckCard, true);
            //echo "<br>checkCard";
            //print_r($rscheckCard);

            // 2. wellcare - updateCardStatus
            $url = $config['cardStore_updateCardStatus'];
            $params = array(
                "card_id" => (int) $rscheckCard['id'],
                "card_status" => -1, //update card da su dung
            );
            $rsUpdateCardStatus = CallAPI('POST', $url, json_encode($params));
            //echo "<br>updateCardStatus";
            //print_r($rsUpdateCardStatus);
            if ($rsUpdateCardStatus == 1) {//update Card Status thanh cong
                // 3. wellcare - updateBalance ( update Credit )
                $params = array(
                    "person_id" => intval($_SESSION['info']['persionid']),
                    "sign" => (string) "IN",
                    "type" => (string) 'TOPUP', //nap tien
                    "amount" => (int) $rscheckCard['value'],
                    "status" => 1,
                    "description" => " ",
                    "visit_id" => (string) ($_SESSION['info']['visitid'])
                );
                $url = $config['payment_updateBalance'];
                $trans_wallet_id = CallAPI('POST', $url, json_encode($params));
                //echo "<br>updateBalance";
                //print_r($trans_wallet_id);
                if ($trans_wallet_id > 0) {
                    // 4. wellcare - getBalance
                    $url = $config['payment_getBalance'];
                    $params = array(
                        "person_id" => intval($_SESSION['info']['persionid']),
                    );
                    $rsGetBalance = CallAPI('POST', $url, json_encode($params));
                    $currentBalance = floatval($rsGetBalance);// O TREN DA UPDATE VAO VI, NEN GET BALANCE CHO NAY SO TIEN THE WELLCARE DA NAP VAO VI
                    //echo "<br>getBalance";
                    //echo $currentBalance;
                    //echo "fee";
                    //var_dump($_SESSION['info']['fee']);
                    $totalAmount = $currentBalance;
                    //echo "<br>totalAmount";
                    //var_dump($totalAmount);
                    //echo "<br>so sanh balance nho hon phi";
                    //var_dump(floatval($_SESSION['info']['fee'])<=$currentBalance);                 
                    if (floatval($_SESSION['info']['fee']) <= $currentBalance) {
                        // 5. wellcare - normal payment flow
                        walletPayment($config, $_SESSION['info']);                        
                    } else {
                        //update session balance
                        $_SESSION['info']['currentBalance'] = $_SESSION['info']['currentBalance'] + $rscheckCard['value'];
                        $addAmount = $productPrice - $currentBalance;//SO TIEN THANH TOAN CON LAI
                    }
                }
            }
        }
        // end WELLCARE
    } else {
        //begin 123PAY
        //MIN AMOUNT OF 123PAY
        $paymentAmount = $productPrice - floatval($_SESSION['info']['currentBalance'])
;        if ($paymentAmount < 20000) {
            $paymentAmount = 20000;
        }
        //1. createOrder api
        $params = array(
            "person_id" => intval($customerId),
            "visit_id" => (string) ($_SESSION['info']['visitid']),
            "amount" => (int) $paymentAmount,
            "discount" => (int) 0,
            "total" => (int) $paymentAmount,
            "description" => " ",
        );
        //print_r($params);
        $url = $config['payment_createOrder'];
        $rsCreateOrder = CallAPI('POST', $url, json_encode($params));
        //print_r($rsCreateOrder);die;
        $rsCreateOrder = json_decode($rsCreateOrder, true);
        if (isset($rsCreateOrder['status']) && $rsCreateOrder['status'] == 0) {
            if (isset($rsCreateOrder['id']) && $rsCreateOrder['id'] > 0) {
                //2 createOder to 123Pay Payment Gateway
                $params = array('totalAmount' => $paymentAmount,
                    'custName' => $customer,
                    'bankcode' => $_POST['bankcode'],
                    'mTransactionID' => $rsCreateOrder['id']);
                $rsCreateOrder123Pay = createOrder123pay($params, $config['pg123pay']);
                if (!$rsCreateOrder123Pay) {
                    $url = '/result.php?c=5000';
                    header("Location: " . $url);
                }
            }
        }
    }
    //end 123PAY
}

//2.1 call queryorder to 123Pay
if (!empty($_GET['transactionID']) && !empty($_GET['time']) && !empty($_GET['status']) && !empty($_GET['ticket'])) {
    $rsQueryOrder123Pay = queryorder123pay($_GET, $config['pg123pay']);
    $transactionID = $_GET['transactionID'];
    $transactionID = str_replace("wc", "", $transactionID);
    //print_r($rsQueryOrder123Pay);die;
    if ($rsQueryOrder123Pay['httpcode'] == 200) {
        if ($rsQueryOrder123Pay[0] == '1') {
            if ($rsQueryOrder123Pay[2] == '1') {
                //2.2 updateBalance ( update Credit ) TOUP
                $params = array(
                    "person_id" => intval($_SESSION['info']['persionid']),
                    "sign" => (string) "IN",
                    "type" => (string) 'TOPUP', //nap tien
                    "amount" => (int) $rsQueryOrder123Pay[3],
                    "status" => 1,
                    "description" => " ",
                    "visit_id" => (string) ($_SESSION['info']['visitid'])
                );
                $url = $config['payment_updateBalance'];
                $trans_wallet_id = CallAPI('POST', $url, json_encode($params));
                echo "<br>updateBalance";
                print_r($trans_wallet_id);
                if ($trans_wallet_id > 0) {
                    //2.3 updateBalance ( update Credit ) LOCK
                    $params = array(
                        "person_id" => intval($_SESSION['info']['persionid']),
                        "sign" => (string) "OUT",
                        "type" => (string) 'LOCK',
                        "amount" => (int) $productPrice,
                        "status" => 1,
                        "description" => " ",
                        "visit_id" => (string) ($_SESSION['info']['visitid']),
                    );

                    $url = $config['payment_updateBalance'];
                    $trans_wallet_id = CallAPI('POST', $url, json_encode($params));
                }

                $pg_status = 1;
                $urlRedirect = '/result.php?c=1';
            } else {
                $pg_status = -1;
                $urlRedirect = '/result.php?c=5000';
            }
        } else {
            $pg_status = -1;
            $urlRedirect = '/result.php?c=5000';
        }
    } else {
        $pg_status = -1;
        $urlRedirect = '/result.php?c=5000';
    }
    //2.3 updateOrder api (update payment)
    $params = array(
        "trans_id" => intval($transactionID),
        "pg_trans_id" => (string) $rsQueryOrder123Pay[1], //orderNo 123Pay
        "pg_name" => "123PAY",
        "pg_status" => $pg_status,
        "description" => " ",
    );
    //print_r($params);
    $url = $config['payment_updateOrder'];
    $transaction_id = CallAPI('POST', $url, json_encode($params));
//print_r($transaction_id);die;
    $_SESSION['data_result']['amount'] = $productPrice;//$rsQueryOrder123Pay[3]; Thong bao thanh cong la phi kham, ko phai so tien thanh toan 123Pay
    if ($transaction_id != 1) {//update Order loi  mac du thanh toan thanh cong
        $urlRedirect = '/result.php?c=5000';
    }
    header("Location: " . $urlRedirect);
}
?>

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
            <div class="">
                <form name="frmPayment" id="frmPayment" action="" method="post"><!-- onsubmit="return validateForm()"-->
                    <div class="row">                        
                        <p>Bác sĩ:<b><?php echo $customer ?></b></p>
                        <p>
                            Phí khám:
                            <strong style="color: #008AFF;"><?php echo number_format($productPrice, 0, '', ','); ?></strong>/lần tư vấn trực tuyến
                        </p>
<!--                        <p>
                            <?php //if ($isAddCredit): ?>
                                Bạn vừa nạp <?php //echo number_format($addAmount, 0, '', ','); ?> vào tài khoản.
                            <?php //endif; ?>
                        </p>-->
                        <p style="color: #e65505">
                            Số dư hiện tại <strong style="color: #555"><?php echo $currentBalance ?></strong>
                            chưa đủ thực hiện cuộc gọi, vui lòng gọi thêm vào tài khoản.
                        </p>
                        <p>
                            Bạn cần nạp thêm <b><?php echo number_format(($productPrice - $currentBalance), 0, '', ','); ?></b>
                        </p>
                        <p style="color:red;"><?php echo $messageError; ?></p>
                    </div>
                    <div class="row">
                        <a href="javascript:void(0);" onclick="jQuery('#div_wellcare_card').toggle()"
                           title="Thẻ Wellcare" class="btn btn-primary btn-payment">
                            <span><span>Thẻ Wellcare</span></span>
                        </a>
                        <br/><br/>
                        <div class="form-inline" id="div_wellcare_card" style="display: none">
                            <span><input type="text" class="form-control" value="" id="wellcare_card" name="wellcare_card" placeholder="Nhập mã thẻ" style="width: 50%;padding: 2px;"></span>
                            <span>
                                <a href="javascript:void(0);" onclick="checkCard();" title="Nạp" class="btn btn-primary" style="background: #666;">
                                    <span>Nạp</span>
                                </a>
                            </span>
                            <br/><br/>
                        </div>
                    </div>
                    <div class="row">
                        <a href="javascript:void(0);" onclick="jQuery('#div_atm_card').toggle()" title="Nạp từ ATM"
                           class="btn btn-primary btn-payment">
                            <span><span>Nạp từ ATM</span></span>
                        </a>
                        <br/><br/>
                        <div style="display: none; padding-bottom: 25px;" id="div_atm_card" class="col-md-12">

                            <div class="col-md-12">
                                <div class="payment-bank" id="payment-bank-atm">
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PVCB">
                                        <img class="img-thumbnail" src="default/images/payment/123PVCB.gif" alt="123PVCB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PEIB">
                                        <img class="img-thumbnail" src="default/images/payment/123PEIB.gif" alt="123PEIB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PACB">
                                        <img class="img-thumbnail" src="default/images/payment/123PACB.gif" alt="123PACB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PVTB">
                                        <img class="img-thumbnail" src="default/images/payment/123PVTB.gif" alt="123PVTB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PTCB">
                                        <img class="img-thumbnail" src="default/images/payment/123PTCB.gif" alt="123PTCB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PSCB">
                                        <img class="img-thumbnail" src="default/images/payment/123PSCB.gif" alt="123PSCB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PBIDV">
                                        <img class="img-thumbnail" src="default/images/payment/123PBIDV.gif" alt="123PBIDV">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PAGB">
                                        <img class="img-thumbnail" src="default/images/payment/123PAGB.gif" alt="123PAGB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PMRTB">
                                        <img class="img-thumbnail" src="default/images/payment/123PMRTB.gif" alt="123PMRTB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PVIB">
                                        <img class="img-thumbnail" src="default/images/payment/123PVIB.gif" alt="123PVIB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PMB">
                                        <img class="img-thumbnail" src="default/images/payment/123PMB.gif" alt="123PMB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PVPB">
                                        <img class="img-thumbnail" src="default/images/payment/123PVPB.gif" alt="123PVPB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123POCB">
                                        <img class="img-thumbnail" src="default/images/payment/123POCB.gif" alt="123POCB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PHDB">
                                        <img class="img-thumbnail" src="default/images/payment/123PHDB.gif" alt="123PHDB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PSGB">
                                        <img class="img-thumbnail" src="default/images/payment/123PSGB.gif" alt="123PSGB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PNAB">
                                        <img class="img-thumbnail" src="default/images/payment/123PNAB.gif" alt="123PNAB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PVAB">
                                        <img class="img-thumbnail" src="default/images/payment/123PVAB.gif" alt="123PVAB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PNVB">
                                        <img class="img-thumbnail" src="default/images/payment/123PNVB.gif" alt="123PNVB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PGPB">
                                        <img class="img-thumbnail" src="default/images/payment/123PGPB.gif" alt="123PGPB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PDAB">
                                        <img class="img-thumbnail" src="default/images/payment/123PDAB.gif" alt="123PDAB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PBAB">
                                        <img class="img-thumbnail" src="default/images/payment/123PBAB.gif" alt="123PBAB">
                                    </a>
                                    <a class="bank" href="javascript:void(0)" data-type="ATM" data-code="123PPGB">
                                        <img class="img-thumbnail" src="default/images/payment/123PPGB.gif" alt="123PPGB">
                                    </a>
                                </div>
                                <div margin-top: 10px;>Số tiền thanh toán tối thiểu là 20.000VND</div>
                            </div>

                            <input type="hidden" name="bankcode" id="bankcode" value="">

                        </div>
                    </div>
                    <!--<div class="row">
                        <a href="javascript:void(0);" onclick="jQuery('#div_internet').toggle()" title="Internet Banking"
                           class="btn btn-primary btn-payment">
                            <span><span>Internet Banking</span></span>
                        </a>
                        <br/><br/>
                        <div style="display: none; padding-bottom: 25px;" id="div_internet" class="col-md-12">
                            <div class="col-md-12">
                                <div class="payment-bank" id="payment-bank-cc">
                                    <a href="javascript:void(0)" class="bank" data-type="CC" data-code="MASTER">
                                        <img class="img-thumbnail" src="default/images/payment/123PCC_MASTER.gif" alt="MASTER">
                                    </a>
                                    <a href="javascript:void(0)" class="bank" data-type="CC" data-code="VISA">
                                        <img class="img-thumbnail" src="default/images/payment/123PCC_VISA.gif" alt="VISA">
                                    </a>
                                </div>
                            </div>
                            <br/><br/>
                        </div>
                    </div>-->
                    <div class="row">
                        <a href="javascript:void(0);" onclick="jQuery('#div_credit_card').toggle()"
                           title="Nạp từ thẻ tín dụng" class="btn btn-primary btn-payment">
                            <span><span>Nạp từ thẻ tín dụng</span></span>
                        </a>
                        <br/><br/>
                        <div style="display: none; padding-bottom: 25px;" id="div_credit_card" class="col-md-12">
                            <div class="col-md-12">
                                <div class="payment-bank" id="payment-bank-cc">
                                    <a href="javascript:void(0)" class="bank" data-type="CC" data-code="123PCC">
                                        <img style="width: auto !important;" class="img-thumbnail" src="default/images/payment/123PCC.gif" alt="MASTER">
                                    </a>
<!--                                    <a href="javascript:void(0)" class="bank" data-type="CC" data-code="123PCC">
                                        <img class="img-thumbnail" src="default/images/payment/123PCC_VISA.gif" alt="VISA">
                                    </a>-->
                                </div>
                                <div style="margin-top: 10px;">Số tiền thanh toán tối thiểu là 20.000VND</div>
                            </div>

                            <br/><br/>
                        </div>
                    </div>
                    <div class="row">
                        <a href="#" title="Thẻ cào điện thoại" class="btn btn-primary btn-payment">
                            <span><span>Thẻ cào điện thoại</span></span>
                        </a>

                    </div>
                </form>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                $(".bank").click(function() {
                    $('#bankcode').val($(this).attr('data-code'));
                    $("#frmPayment").submit();
                });
            });
            function checkCard() {
                $('#bankcode').val('WELLCARE');
                $("#frmPayment").submit();
            }
            function validateForm() {
                return true;
            }


        </script>
    </body>
</html>


