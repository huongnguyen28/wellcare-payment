<?php
function paynganluong($total){	
	$email="tramle120887";	
	$product_name="1";//Mã đơn đặt hàng	
	$str = "http://vc.local/index.php";
	header("Location:https://www.nganluong.vn/button_payment.php?receiver=$email&product_name=$id&price=$total&return_url=$str");
	//href="https://www.nganluong.vn//button_payment.php?receiver=tramle120887@gmail.com&product_name=(Mã đơn đặt hàng)&price=(Tổng giá trị)&return_url=(URL thanh toán thành công)&comments=(Ghi chú về đơn hàng)"
}
?>
