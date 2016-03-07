<?php

function walletPayment($config,$data) {
    //1.createOrder api (createorder of payment)
    $params = array(
        "person_id" => intval($data['persionid']),
        "visit_id" => (string) ($data['visitid']),
        "amount" => (int) $data['fee'],
        "discount" => (int) 0,
        "total" => (int) $data['fee'],
        "description" => " ",
    );
    $url = $config['payment_createOrder'];
    $rsCreateOrder = CallAPI('POST', $url, json_encode($params));
    $rsCreateOrder = json_decode($rsCreateOrder, true);
    if (isset($rsCreateOrder['status']) && $rsCreateOrder['status'] == 0) {
            if (isset($rsCreateOrder['id']) && $rsCreateOrder['id'] > 0) {
                //2. updateBalance api (update Credit la update wallet)
                $params = array(
                    "person_id" => intval($data['persionid']),
                    "sign" => (string) "OUT",
                    "type" => (string) 'LOCK',
                    "amount" => (int) $data['fee'],
                    "status" => 1,
                    "description" => " ",
                    "visit_id" => (string) ($_SESSION['info']['visitid']),
                );
                $url = $config['payment_updateBalance'];
                $trans_wallet_id = CallAPI('POST', $url, json_encode($params));
                //3. updateOrder api (update payment)
                if ($trans_wallet_id > 0) {

                    $params = array(
                        "trans_id" => intval($rsCreateOrder['id']),
                        "pg_trans_id" => (string) $trans_wallet_id,
                        "pg_name" => "WALLET",
                        "pg_status" => 1,
                        "description" => " ",
                    );
                     
                } else {//update Credit ko thanh
                    $params = array(
                        "trans_id" => intval($rsCreateOrder['id']),
                        "pg_trans_id" => (string) $trans_wallet_id,
                        "pg_name" => "WALLET",
                        "pg_status" => -1,
                        "description "=> " ",
                    );
                }
                $url = $config['payment_updateOrder'];
                $transaction_id = CallAPI('POST', $url, json_encode($params));
                $_SESSION['data_result']['amount']=$_SESSION['info']['fee'];
                if($transaction_id==1){
                    
                    $url = '/result.php?c=1';
                }
                else{
                    $url = '/result.php?c='.$transaction_id;
                }                
                header("Location: " . $url);
                
            }
        }
}

?>