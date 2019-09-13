<?php
require('../config.php');
require(LIBRARY_DIR."/lib/functions_admin.php");

session_start();

if(!is_logged_in()){
	header("Location: ".LIBRARY_WEB."admin/index.php");
	exit();
}

require(LIBRARY_DIR."/templates/header.php");

$ftitle = "";
$fpages = "";
$fauthor = "";
$fpublisher = "";
$fdescription = "";
$fisbn="";
$fformat = "";
$fcat = "";
$floan = "";
$fread = "0";

if( isset($_POST['formSubmit']) ){

    save_book();

}elseif( AMAZON_ENABLED && isset($_POST['formFetch']) && isset($_POST["isbn"]) ){

	// Special thanks to patch contributer Jan De Luyck for the initial version of this feature.

	$amazonInfo = fetch_amazon_info($_POST["isbn"]);
	$fisbn = htmlspecialchars($_POST["isbn"]);

	if ($amazonInfo !== FALSE)
	{		
		$ftitle = htmlspecialchars($amazonInfo->Items->Item->ItemAttributes->Title);
		$fauthor = htmlspecialchars($amazonInfo->Items->Item->ItemAttributes->Author);
		$fpublisher = htmlspecialchars($amazonInfo->Items->Item->ItemAttributes->Publisher);
		$fpages = htmlspecialchars($amazonInfo->Items->Item->ItemAttributes->NumberOfPages);
		
		$aFormat = $amazonInfo->Items->Item->ItemAttributes->Binding;
		if($aFormat == "Hardcover"){
			$fformat = 2;
		}elseif($aFormat == "Paperback"){
			$fformat = 1;
		}else{
			$fformat = $_POST["format"];
		}

		if(!empty($amazonInfo->Items->Item->EditorialReviews->EditorialReview)){
		foreach ($amazonInfo->Items->Item->EditorialReviews->EditorialReview as $aReview)
		{
			// We're only interested in the official Amazon.com review."
			if ($aReview->Source == "Product Description")
			{
				$fdescription = htmlspecialchars(strip_tags($aReview->Content));
				break;
			}
		}
		}

		if($fdescription == ""){
			$fdescription = htmlspecialchars($_POST['description']);
		}

		$fcat = $_POST['category'];
		$floan = htmlspecialchars($_POST['on_loan']);
		$fread = $_POST['self_read'];
	}
	
}

?>

<script type="text/javascript" src="addBook.js"></script>

<h3>Add Book</h3>

<form action="<?php echo($_SERVER['PHP_SELF']);?>" method="post">

<span id="alertmsg"></span>

<table border="0" cellpadding="1" cellspacing="0">

<?php
$cat = get_categories();

if($cat == FALSE){
    echo("<p><b>ERROR:</b>Could not load categories!</p>");
}else{

    echo("<tr><td valign='top' align='left' width='100'><b>Category: *</b> </td><td valign='top' align='left'>");
    echo("<select name='category' id='category'>");

    $group_started = FALSE;

    foreach($cat as $option){
		if ($option['root'] == TRUE) {
			if ($group_started == FALSE) {
				echo('<optgroup label="'.$option['name'].'">');			
				$group_started = TRUE;
			} else {
				echo('</optgroup><optgroup label="'.$option['name'].'">');
			}
		} else {
			?><option value="<?php echo($option['id']);?>" <?php if ($option['id'] == $fcat){ echo("selected='selected'");}?>><?php echo(htmlspecialchars($option['name'], ENT_COMPAT,'ISO-8859-15',FALSE));?></option><?php
		}
    }

    if ($group_started == TRUE) {
       echo('</optgroup>');
    }
    
    echo('</select></td></tr>');
}

$formats = get_formats();

?>

<tr><td valign="top" align="left" width="100"><b>Title: *</b> </td><td valign="top" align="left"><input type="text" name="title" id="title" size="40" maxlength="255" value="<?php echo($ftitle);?>" /></td></tr>
<tr><td valign="top" align="left" width="100"><b>Author: *</b> </td><td valign="top" align="left"><input type="text" name="author" id="author" maxlength="200" value="<?php echo($fauthor);?>" /></td></tr>
<tr><td valign="top" align="left" width="100"><b>Format: *</b> </td><td valign="top" align="left">

<?php
if($formats == FALSE){
    echo('<p><b>ERROR:</b>Could not load formats!</p>');
}else{

	echo('<select name="format" id="format">');
    foreach($formats as $option){
		?><option value="<?php echo($option['id']);?>" <?php if ($option['id'] == $fformat){ echo("selected='selected'");}?>><?php echo($option['name']);?></option><?php
	}
    echo('</select>');
}
?></td></tr>

<tr><td valign="top" align="left" width="100"><b>Publisher:</b> </td><td valign="top" align="left"><input type="text" name="publisher" maxlength="200" value="<?php echo($fpublisher);?>" /></td></tr>
<tr><td valign="top" align="left" width="100"><b>ISBN:</b> </td><td valign="top" align="left"><input type="text" name="isbn" value="<?php echo($fisbn);?>" /><?php if(AMAZON_ENABLED){?>&nbsp;&nbsp;<input type="submit" name="formFetch" value="Fetch From Amazon" />&nbsp;&nbsp;&nbsp;&nbsp;<input style="font-weight:bold;" type="button" value="?" onclick="alert('Fetch From Amazon\n\nUsing the ISBN, this will look up the book in Amazon\'s product database and populate the Add Book form fields for you. You must still select a category, and you may have to tidy up and/or proofread the information received from Amazon.');" /><?php }?></td></tr>
<tr><td valign="top" align="left" width="100"><b>Pages:</b> </td><td valign="top" align="left"><input type="text" name="pages" size="4" value="<?php echo($fpages);?>" /><br /></td></tr>

<tr><td valign="top" align="left" width="100"><b>Read: *</b> </td><td valign="top" align="left">
<select name="self_read">
    <option value="1" <?php if($fread==1){ echo("selected='selected'");}?>>Read</option>
    <option value="0" <?php if($fread==0){ echo("selected='selected'");}?>>Not Read</option>
    <option value="2" <?php if($fread==2){ echo("selected='selected'");}?>>Reading</option>
</select></td></tr>

<tr><td valign="top" align="left" width="100"><b>On Loan:</b> </td><td valign="top" align="left"><input type="text" name="on_loan" value="<?php echo($floan);?>" /></td></tr>

</table>

<br />

<textarea name="description" cols="40" rows="5"><?php echo($fdescription);?></textarea>

<br /><br />

<input type="submit" value="Save" name="formSubmit" onclick="return validateForm();" />
</form>


<?php
require(LIBRARY_DIR."/templates/footer.php");
?>