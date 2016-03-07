<?php

function createOrder123pay($params, $config) {
    include 'library/rest.client.class.php';
    if (!class_exists("Common")) {
        include 'library/common.class.php';
    }

    $result = null;
    $resultMessage = '';

    $bankcode = $params['bankcode'];
    $mTransactionID = 'wc' . $params['mTransactionID']; // 'micode' . time();
    $resultMessage = 'Current order id: <strong>' . $mTransactionID . '</strong><br>';
    $aData = array
        (
        'mTransactionID' => $mTransactionID,
        'merchantCode' => $config['merchant_code'],
        'bankCode' => $bankcode,
        'totalAmount' => $params['totalAmount'],
        'clientIP' => '172.0.0.1',
        'custName' => $params['custName'],
        'custAddress' => '1 nguyen trai',
        'custGender' => 'U',
        'custDOB' => '',
        'custPhone' => '0973111222',
        'custMail' => 'tramle120887@gmail.com',
        'description' => 'thanh toan don hang WELLCARE',
        'cancelURL' => 'http://vc.local/index.php',
        'redirectURL' => 'http://vc.local/index.php',
        'errorURL' => 'http://vc_payment/index.php',
        'passcode' => $config['pass_code'],
        'checksum' => '',
        'addInfo' => ''
    );

    $aConfig = array
        (
        'url' => $config['create_oder_url'],
        'key' => $config['scret_key'],
        'passcode' => $config['pass_code'],
        'cancelURL' => 'merchantCancelURL', //fill cancelURL here
        'redirectURL' => 'merchantRedirectURL', //fill redirectURL here
        'errorURL' => 'merchantErrorURL', //fill errorURL here
    );

    try {
        $data = Common::callRest($aConfig, $aData); //call 123Pay service         
        //print_r($aData);
        $result = $data->return;
//        print_r($result);die;
        if ($result['httpcode'] == 200) {
            //call service success do success flow
            if ($result[0] == '1') {//service return success
                //re-create checksum
                $rawReturnValue = '1' . $result[1] . $result[2];
                $reCalChecksumValue = sha1($rawReturnValue . $aConfig['key']);
                if ($reCalChecksumValue == $result[3]) {//check checksum
                    $resultMessage .= 'Call service result:<hr>';
                    $resultMessage .= 'mTransactionID=' . $mTransactionID . '<br>';
                    $resultMessage .= '123PayTransactionID=' . $result[1] . '<br>';
                    $resultMessage .= 'URL=' . $result[2] . '<br>';
                    //call php header to redirect to input card page
                    $resultMessage .= '<a style="color:red;font-weight:bold;" href="' . $result[2] . '" target="_parent">Click here to go to payment process</a><br>';
                    echo'<script>window.location.href="' . $result[2] . '"</script>';
                    exit();
                } else {                    
                    //Call 123Pay service create order fail, return checksum is invalid
                    $resultMessage .= 'Return data is invalid<br>';
                }
            } else {
                //Call 123Pay service create order fail, please refer to API document to understand error code list
                //$result[0]=error code, $result[1] = error description
                $resultMessage .= $result[0] . ': ' . $result[1];
                
            }
        } else {
            //call service fail, do error flow
            $resultMessage .= 'Call 123Pay service fail. Please recheck your network connection<br>';
        }
    } catch (Exception $e) {
        $resultMessage .= '<pre>';
        $resultMessage .= $e->getMessage();
    }
    return 0;
}

function queryorder123pay($params, $config) {
    if (!class_exists("Common")) {
        include 'library/common.class.php';
    }
    $aConfig = array
        (
        'merchantCode' => $config['merchant_code'],
        'url' => $config['query_oder_url'],
        'key' => $config['scret_key'],
        'passcode' => $config['pass_code'],
    );

    $transactionID = $params['transactionID'];

    $time = $params['time'];
    $status = $params['status'];
    $ticket = $params['ticket'];

    $recalChecksum = md5($status . $time . $transactionID . $aConfig['key']);
    if ($recalChecksum != $ticket) {
        echo 'Invalid url';
        exit;
    }

    try {
        $aData = array
            (
            'mTransactionID' => $transactionID,
            'merchantCode' => $aConfig['merchantCode'],
            'clientIP' => '127.0.0.1', //current browser ip
            'passcode' => $aConfig['passcode'],
            'checksum' => '',
        );
        $data = Common::callRest($aConfig, $aData);

        $result = $data->return;
        return $result;

//        if ($result['httpcode'] == 200) {
//            
//            if ($result[0] == '1') {
//                echo 'Order info:<hr>';
//                echo 'mTransactionId:' . $transactionID . '<br>';
//                echo '123PayTransactionId: ' . $result[1] . '<br>';
//                echo 'Status: ' . $result[2] . '<br>';
//                echo 'Amount: ' . $result[3] . '<br>';
//                echo '<hr>';
//                if ($result[2] == '1') {//success
//                    //Do success call service
//                    echo 'Checkout process successfully';
//                } else {
//                    echo 'Show message base on order status (' . $result[2] . ')';
//                }
//            } else {
//                echo 'Call service queryOrder fail: Order is processing. Please waiting some munite and check your order history list';
//            }
//        } else {
//            //do error call service.
//            echo 'Call service queryOrder fail: Order is processing. Please waiting some munite and check your order history list';
//        }
    } catch (Exception $e) {
        return array();
        //write log here to monitor your exception
        echo 'Call service queryOrder fail: Order is processing. Please waiting some munite and check your order history list';
    }
}

?>
