<?php
  require_once 'jsonRPCClient.php';
  $coin = new jsonRPCClient('http://user:pass@127.0.0.1:9998/');
  $link = mysqli_connect('localhost', 'user', 'pass', 'database');
  mysqli_set_charset($link,'utf8');
  $result = mysqli_query($link,'SELECT * FROM `payments` WHERE site="' . $_GET['s'] . '";');
  while ($row = mysqli_fetch_array($result)) { if ($row['date']<date('Y-m-d',time()-24*60*60)) mysqli_query($link,'DELETE FROM `payments` WHERE id=' . $row['id'] . ';'); } 
  $result = mysqli_query($link,'SELECT * FROM `payments` WHERE site="' . $_GET['s'] . '";');
  while ($row = mysqli_fetch_array($result)) { 
	if ($row['status']==0) {
		$income = floatval($coin->getreceivedbyaddress($row['address']));
		$price = floatval($row['amount']);
		if ($income>=$price) {
			if ($income>0) $coin->sendtoaddress($row['receive'],$income);
			mysqli_query($link,'UPDATE `payments` SET status=1 WHERE id=' . $row['id'] . ';');
		}
	}
  }
  $result = mysqli_query($link,'SELECT * FROM `payments` WHERE orderid=' . $_GET['o'] . ' AND site="' . $_GET['s'] . '";');
  $status = -1;
  $status = mysqli_fetch_array($result)['status'];
  if ($status==-1) echo 'Failed';
  if ($status==1) echo 'Payed';
  mysqli_free_result($result);
  mysqli_close($link);
?>
