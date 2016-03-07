<?php
//call api payment        
                       
                        
                        //print_r($params);
                        
                        //echo "<pre>";
                        print_r($result);
                        //call service di thanh toan, thanh cong hay ko di thong bao

    {
                            
                                {
                                
                                $url = 'http://localhost:8084/Wellcare/payment/updateOrder';
                                $transaction_id = CallAPI('POST', $url, json_encode($params));
                                $_SESSION['data_result']['amount'] = $_SESSION['info']['fee'];
                                if ($transaction_id == 1) {
                                    $url = '/result.php?c=1';
                                } else {
                                    $url = '/result.php?c=' . $transaction_id;
                                }
                                header("Location: " . $url);
                            }
                        }
                        ?>