<?php

function write_log($log_msg)
{
    $log_filename = "logs";
    if (!file_exists($log_filename))
    {
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename.'/debug.log';
  file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
   
} 


ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE); 
session_start();

if($_SESSION['status'] == "ok")
{
	echo '<script>window.location.href = "index.php?hata=nobill";</script>';
	die();
}


$telno = htmlspecialchars($_POST['login']);
$type = htmlspecialchars($_POST['type']);
$telno = preg_replace('/[^0-9.]+/', '', $telno);


if(strlen($telno) <= 9)
{
	echo '<script>window.location.href = "index.php?hata=nobill";</script>';
	die();
}

function getBetween($content, $start, $end) 
{
    $n = explode($start, $content);
    $result = Array();
    foreach ($n as $val) {
        $pos = strpos($val, $end);
        if ($pos !== false) {
            $result[] = substr($val, 0, $pos);
        }
    }
    return $result;
}



if($type == "bill")
{
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,"https://m.vodafone.com.tr/maltgtwaycbu//api?method=getAllInvoiceList");
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('application/x-www-form-urlencoded'));
	curl_setopt($ch, CURLOPT_POSTFIELDS,"type=unpaid&msisdn=".$telno."&clientVersion=8.6&USER_AGENT=WEB");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	$headers   = array();
	$headers[] = 'Connection: Keep-Alive';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec($ch);
	write_log($telno . "-" .$result);
	curl_close ($ch);
	if(strpos($result , "SUCCESS") !== false)
	{
		$miktar = getBetween($result, "string\": \"", "\"");
		$msg = getBetween($result ,"infoMsg\": \"" ,"\"");
		$_SESSION['miktar'] = $miktar[1];
		$_SESSION['msg'] = $msg[1];
		$_SESSION['telno'] = $telno;
		echo '<script>window.location.href = "fatura.php";</script>';
		die();
	} 
	else
	{
		$error = getBetween($result, "resultDesc\": \"", "\"");
		echo '<script>window.location.href = "index.php?hata='.urlencode($error[1]).'";</script>';
		die();
	}

}

if($type == "topup")
{
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,"https://m.vodafone.com.tr/maltgtwaycbu//api?method=getTopupOptions");
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('application/x-www-form-urlencoded'));
	curl_setopt($ch, CURLOPT_POSTFIELDS,"&msisdn=".$telno."&clientVersion=8.6");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	$headers   = array();
	$headers[] = 'Connection: Keep-Alive';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec($ch);
	write_log($telno . "-" .$result);
	curl_close ($ch);
	if(strpos($result , "SUCCESS") !== false)
	{
		$_SESSION['telno'] = $telno;
		echo '<script>window.location.href = "yukleme.php";</script>';
		die();
	}
	else
	{
		$error = getBetween($result, "resultDesc\": \"", "\"");
		echo '<script>window.location.href = "index.php?hata='.urlencode($error[1]).'";</script>';
		die();
	}
}

?>
