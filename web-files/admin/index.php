<?php
require('../config.php');
session_start();

//*** Check if we are logging out:
if(!empty($_GET['logout'])){
	
	$_SESSION=array();
	session_destroy();
	session_start();
	
	$msg = "<p style='color:#00aa00;'><b>You have logged out successfully.</b></p>";
}else{
	$msg = "";
}

//*** Check if we are logging in:
if(!empty($_POST['user']) && !empty($_POST['pass'])){

	$iuser = $_POST['user'];
	$ipass = $_POST['pass'];
	$ip = $_SERVER["REMOTE_ADDR"];
	
	$result = user_login($iuser, $ipass, $ip);
	if(!$result){
		$msg = "<p style='color:#dd0000;'><b>Username or password incorrect!</b></p>";
	}else{
		header("Location: ".LIBRARY_WEB."admin/index.php"); // Refresh to remove post data.
	}

}

require(LIBRARY_DIR."/templates/header.php");

echo($msg);	// Output any messages (like logged out) that we have.


//*** Check if we are logged in. If not, show login form:
if( !is_logged_in() ){

	$_SESSION=array();	// make sure nothing carries over from previous sessions.

	?>
	
	<h4>Login:</h4>	
	<form action="index.php" method="post">
		<table border="0" cellpadding="2" cellspacing="0">
			<tr><td align="right">Username:</td><td><input name="user" type="text" value="" /></td></tr>
			<tr><td align="right">Password:</td><td><input name="pass" type="password" value="" /></td></tr>
			<tr><td colspan="2"><input type="submit" value="Login" /></td></tr>
		</table>
	</form>

        <br /><br /><br />
	
	<?php
	require(LIBRARY_DIR."/templates/footer.php");
	exit();
}

book_search(1);

require(LIBRARY_DIR."/templates/footer.php");
?>