<?php
return array('pg123pay'=>array('merchant_code'=>'MICODE',
                               'pass_code'=>'MIPASSCODE',
                               'scret_key'=>'MIKEY',
                               'create_oder_url'=>'https://sandbox.123pay.vn/miservice/createOrder1',
                               'query_oder_url'=>'https://sandbox.123pay.vn/miservice/queryOrder',
                                'fee_atm'=>1000,
                                'fee_credit'=>2000),
    'payment_createOrder'=>'http://localhost:8084/Wellcare/payment/createOrder',
    'payment_updateBalance'=>'http://localhost:8084/Wellcare/payment/updateBalance',
    'payment_updateOrder'=>'http://localhost:8084/Wellcare/payment/updateOrder',
    'cardStore_checkCard'=>'http://localhost:8084/Wellcare/cardStore/checkCard',
    'cardStore_updateCardStatus'=>'http://localhost:8084/Wellcare/cardStore/updateCardStatus',
    'payment_getBalance'=>'http://localhost:8084/Wellcare/payment/getBalance',
    'payment_chargeBalance'=>'http://localhost:8084/Wellcare/payment/chargeBalance',
    'payment_cancelBalance'=>'http://localhost:8084/Wellcare/payment/cancelBalance',
    'getAllBalance'=>'http://localhost:8084/Wellcare/payment/getAllBalance')
?>

