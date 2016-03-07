<?php
function paynganluong($total){	
	$email="tramle120887";	
	$product_name="1";//Mã đơn đặt hàng	
	$str = "http://vc.local/index.php";
	header("Location:https://www.nganluong.vn/button_payment.php?receiver=$email&product_name=$id&price=$total&return_url=$str");
	//href="https://www.nganluong.vn//button_payment.php?receiver=tramle120887@gmail.com&product_name=(Mã đơn đặt hàng)&price=(Tổng giá trị)&return_url=(URL thanh toán thành công)&comments=(Ghi chú về đơn hàng)"
}
function resultnganluong()
{
	require('nganluong.php');

	$NL_Checkout = new NL_Checkout;

	// Lay cac thong tin duoc tra ve tu website nganluong sau khi thanh toan xong

	$transaction_info = $_GET['transaction_info'];
	$order_code = $_GET['order_code'];
	$price = $_GET['price'];
	$payment_id = $_GET['payment_id'];
	$payment_type =  $_GET['payment_type'];
	$error_text = $_GET['error_text'];
	$secure_code = $_GET['secure_code'];

	// Dung ham verifyPaymentURL de xac nhan tin dung dan cua thong tin va trang thai thanh toan don hang duoc tra ve tu nganluong

	$result = $NL_Checkout->verifyPaymentURL($transaction_info, $order_code, $price, $payment_id, $payment_type, $error_text, $secure_code);

	// Kiem tra ket qua tra ve va thong bao

	if ($result==true){
		if ($_GET['error_text']!=''){
			$message = 'Đơn hàng đã bị hủy bỏ.';
		}else{
			$message = 'Đơn hàng đã được thanh toán thành công.';
			$id=(int)  str_replace('ID','',$order_code);

					/*$input_data= array(	"ipay"=>1);
					$sql = 'UPDATE ' . $prefix . '_shop_orders 
					SET '.$db->sql_build_array('UPDATE', $input_data).'
					WHERE order_id='.$id.'';
					$db->sql_query($sql);*/
					sendOrdertoCustomer($id);	
				}
			}else{
				$message = 'Thông tin đã bị thay đổi, đơn hàng không được xử lý.';
			}


			$html .= "<p>&nbsp;</p><table border=1 cellpadding=3 cellspacing=0 style=border-collapse:collapse width=500px align=center>";
			$html .= "<tr bgcolor=#CCCCCC>
			<td align=center><b>Thông báo</b></td>
		</tr>";	
		$html .= "<tr style='height:100px; line-height:100px;'>
		<td align=center>".$message."</td>
	</tr></table>";	
	echo $html;
	unset($_SESSION['client']);
	include("footer.php");

}
?>
