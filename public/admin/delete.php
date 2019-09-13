<?php
require('../config.php');

session_start();

if(!is_logged_in()){
	header("Location: ".LIBRARY_WEB."admin/index.php");
	exit();
}

require(LIBRARY_DIR."/templates/header.php");
?>

<br />
<?php

$book_id = intval($_GET['id']);

if($book_id <= 0){

	echo("No book selected.");
	require(LIBRARY_DIR."/templates/footer.php");
	exit;
}


// Delete cover images

$name_lrg = "cover_".$book_id."_lrg.jpg";
$dest_lrg = COVERS_LRG.$name_lrg;  
@unlink($dest_lrg);

$name_sm = "cover_".$book_id."_sm.jpg";
$dest_sm = COVERS_SM.$name_sm;  
@unlink($dest_sm);


// Remove book from db.

$SQL = "DELETE FROM library WHERE id=".$book_id;

$result = $dba->Query($SQL);

if($result === FALSE){
	echo("<p>Delete failed.</p>");
}else{
	echo("<p>Book deleted.</p>");
}

echo("<a href='index.php'>Manage Books</a>");


require(LIBRARY_DIR."/templates/footer.php");
?>