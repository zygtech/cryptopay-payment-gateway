<?php
  require_once 'jsonRPCClient.php';
  $coin = new jsonRPCClient('http://user:pass@127.0.0.1:9998/');
  $addr = $coin->getnewaddress();
  $link = mysqli_connect('localhost', 'user', 'pass', 'database');
  mysqli_set_charset($link,'utf8');
  if ($_GET['hash']==md5($_GET['id'] . $_GET['site'] . $_GET['amount'] . base64_decode($_GET['my_address']) . 'CryptoCoin')) {
    mysqli_query($link,'INSERT INTO `payments` VALUES (0,' . $_GET['id'] . ',"' . $_GET['site'] . '","' . $addr . '",' . $_GET['amount'] . ',"' . base64_decode($_GET['my_address']) . '",NOW(),0);');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Payment Gateway</title>
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<style>
	body { font-family: 'Roboto', sans-serif; }
	a { color: #000000; text-decoration: underline; }
</style>
</head>
<body>
<center>
<img src="qr.php?addr=<?php echo $addr; ?>"><br />
<?php echo $addr; ?><br /><h3>Price: <?php echo $_GET['amount']; ?> mCTC<br /><br /><a href="<?php echo $_GET['site']; ?>">BACK TO SHOP</a></h3>
</center>
</body>

</html>
<?php
  }
?>
