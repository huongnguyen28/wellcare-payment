<?php
/**
 * 123Pay Merchant Service
 * @package		miservice
 * @subpackage 	notify.php
 * @copyright	Copyright (c) 2012 VNG
 * @version 	1.0
 * @author 		quannd3@vng.com.vn (live support; zingchat:kibac2001, yahoo:kibac2001, Tel:0904904402)
 * @created 	01/10/2012
 * @modified 	05/10/2012
 */
//this sample code use both GET and POST method
//You can modify to use one that you like
$mTransactionID = $_REQUEST['mTransactionID'];
$bankCode = $_REQUEST['bankCode'];
$transactionStatus = $_REQUEST['transactionStatus'];
$description = $_REQUEST['description'];
$ts = $_REQUEST['ts'];
$checksum = $_REQUEST['checksum'];


$sMySecretkey = 'MIKEY';//key use to hash checksum that will be provided by 123Pay
$sRawMyCheckSum = $mTransactionID.$bankCode.$transactionStatus.$ts.$sMySecretkey;
$sMyCheckSum = sha1($sRawMyCheckSum);

if($sMyCheckSum != $checksum)
{
	 response($mTransactionID, '-1', $sMySecretkey);
}

$processResult = process($mTransactionID, $bankCode, $transactionStatus);
response($mTransactionID, $processResult, $sMySecretkey);


/*===============================Function region=======================================*/
function process($mTransactionID, $bankCode, $transactionStatus)
{
	try
	{
		if(empty($mTransactionID) || empty($bankCode) || empty($transactionStatus))
		{
			return -1;
		}
		//if lay status123pay duoi database len theo $mTransactionID neu==1 return 2;
		//else{
			//if($transactionStatus==1){cap nhat don hang thanh toan thanh cong}
			//luu xuong db status123pay=$transactionStatus theo mTransactionID;
			//return 1;
		//}
		
	}
	catch(Exception $_e)
	{
		return -3;	
	}
}
function response($mTransactionID, $returnCode, $key)
{
	$ts = time();
	$sRawMyCheckSum = $mTransactionID.$returnCode.$ts.$key;
	$checksum = sha1($sRawMyCheckSum);
	$aData = array(
		'mTransactionID' => $mTransactionID,
		'returnCode' => $returnCode,
		'ts' => time(),
		'checksum' => $checksum
	);
	echo json_encode($aData);
	exit;
}
/*===============================End Function region=======================================*/
?>
