<html>
<head>
	<title>Rublon Example</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script type="text/javascript" src="./confirm.js"></script>
	<script type="text/javascript">
		function RublonLogoutCallback() {
			location.href = './?action=logout';
		}
	</script>
	<link rel="stylesheet" href="./style.css" type="text/css" />
</head>
<body>

<a href="./?action=logout" style="float:right">Logout</a>
<h1>Hello <?php echo $_SESSION['user']['name']; ?></h1>

<?php

show_messages();

// Show Rublon GUI
echo '<div class="rublon-gui">';
new RublonSubscribeWidget();
echo '</div>';

?>

<h2>Transaction</h2>
<div id="app">
	<div class="spinner"></div>
	<form action="./?action=confirm" method="post" id="confirmForm" class="rublon-confirmation-form">
		<textarea cols="70" rows="5" name="confirmMessage">Do you confirm transaction #<?php echo rand(9999, 99999999); ?>?</textarea><br />
		Time buffer: <input type="text" name="buffer" value="600" /><br />
		<input type="submit" value="Confirm Transaction" />
	</form>
</div>

<h2>Send notification</h2>
<div id="notification">
	<form action="./?action=notification" method="post">
		URL: <input type="url" name="url" value="<?php echo htmlspecialchars($rublon->getCurrentUrl()); ?>"><br />
		<input type="submit" value="Send" />
	</form>
</div>

<div id="features">
<h2>Check available features</h2>
<p><a href="./?action=features" target="featuresFrame">Check</a></p>
<iframe id="featuresFrame" name="featuresFrame"></iframe>
</div>

</body>
</html>