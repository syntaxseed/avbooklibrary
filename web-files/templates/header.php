<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?php echo(LIBRARY_TITLE);?></title>
<link rel="stylesheet" type="text/css" href="<?php echo(LIBRARY_WEB.'default.css');?>" />
 <meta http-equiv="content-type" content="text/html; charset=iso-8859-15"/>
<?php if(BAN_SEARCH_ROBOTS){ ?>
	<meta name="robots" content="noindex,noarchive,nofollow" />
	<meta name="googlebot" content="noindex,noarchive,nofollow" />
<?php } ?>
</head>

<body bgcolor="#394a38">


<table align="center" border="0" width="760" cellpadding="0" cellspacing="0">

<tr><td valign="top"><a href="<?php echo(LIBRARY_WEB);?>"><img src="<?php echo(LIBRARY_WEB.'images/title.jpg');?>" border="0" alt="<?php echo(LIBRARY_TITLE);?>" /></a></td></tr>

<tr><td valign="top"><table align="center" border="0" style="border: 3px solid #BC9F63;" width="785" cellpadding="5" cellspacing="0">

<tr><td valign="top" bgcolor="#f5eed3">


<?php // Show the Admin Menu:
if( is_logged_in() ){ ?>

<table border="0" cellpadding="2" cellspacing="0" width="100%" style="border:1px solid #BC9F63;">
<tr><td bgcolor="#EFE3B3">
	&nbsp;<b>Admin Menu:</b>&nbsp;&nbsp;&nbsp;
	<a href="index.php"><b>Home</b></a> &nbsp;&nbsp;&nbsp;&nbsp; 
    <a href="add.php"><b>Add Book</b></a> 
    
</td><td align="right" bgcolor="#EFE3B3">
	<a href="about.php"><b>About</b></a> &nbsp;&nbsp;&nbsp;&nbsp; <a href="index.php?logout=1"><b>Logout</b></a>&nbsp;
</td></tr></table>

<?php } 

echo('<noscript><p style="color:#cc0000;"><b>Warning:</b> You do not have Javascript enabled in your browser. Some features of this site may not function properly.</p></noscript>');


?>