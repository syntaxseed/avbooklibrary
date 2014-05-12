<?php
require('../config.php');
require(LIBRARY_DIR."/lib/functions_admin.php");

session_start();

if(!is_logged_in()){
	header("Location: ".LIBRARY_WEB."admin/index.php");
	exit();
}

require(LIBRARY_DIR."/templates/header.php");

$book_id = intval($_GET['id']);

if(isset($_POST['formSubmit'])){
    $book_id = update_book();
}

if($book_id <= 0){
    echo("Error: Invalid book id.");
    exit;
}

// Load book from DB:
$SQL = "SELECT * from library where id=".$book_id;
$result = $dba->getRow($SQL);
if($result === FALSE){
    echo("<p>No book found with that id.</p><a href='index.php'>Admin Home</a>");
    exit;
}

?>

<script type="text/javascript">

/* If the ISBN is edited, check the 're-cache cover images' check box to get a new cover image.
 */
function CheckISBNChanged(newISBN){

	var originalISBN = "<?php echo(htmlspecialchars($result['isbn'],ENT_QUOTES));?>";
	
	if(newISBN != originalISBN){
		// Check the 're-cache cover images' check box.
		document.getElementById("clear_covers").checked = true;
	}
}

function validateForm(){

	var isOK = true;
	var value;
	var msg = "Please correct or supply the following:";

	//Reset message.
	document.getElementById("alertmsg").innerHTML = "";

	// Category
	value = document.getElementById("category").options[document.getElementById("category").selectedIndex].value;
	if(isNaN(value) || value >= 1000){
		msg += "<br />&nbsp;- Category.";
		isOK = false;
	}

	// Title
	value = document.getElementById("title").value;
	if(value == ""){
		msg += "<br />&nbsp;- Title.";
		isOK = false;
	}

	// Author
	value = document.getElementById("author").value;
	if(value == ""){
		msg += "<br />&nbsp;- Author.";
		isOK = false;
	}

	if(!isOK){
		msg = "<table style='border:1px solid #ff0000;background-color:#fff3f3;font-weight: bold; color: #ff0000;'><tr><td style='padding:2px;' cellpadding='0' align='left' valign='middle'>"+msg+"</td></tr></table><br />";
		document.getElementById("alertmsg").innerHTML = msg;
	}

	return isOK;

}

function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}

</script>

<h3>Edit Book</h3>

<form action="<?php echo($_SERVER['PHP_SELF']);?>" method="POST">

<span id="alertmsg"></span>

<table border="0" cellpadding="1" cellspacing="0">

<?php

$cat = get_categories();

if($cat == FALSE){
    echo("<p><b>ERROR:</b>Could not load categories!</p>");
}else{

    echo("<tr><td valign='top' align='left' width='100'><b>Category: *</b> </td><td valign='top' align='left'>");
    echo("<select name='category' id='category'>");

    foreach($cat as $option){
		if ($option['root'] == TRUE) {
			if ($group_started == FALSE) {
				echo('<optgroup label="'.$option['name'].'">');			
				$group_started = TRUE;
			} else {
				echo('</optgroup><optgroup label="'.$option['name'].'">');
			}
		} else {
			if($option['id'] == $result['category']){
				echo("<option value='".$option['id']."' selected='selected'>".$option['name']."</option>");
			}else{
				echo("<option value='".$option['id']."'>".$option['name']."</option>");
			}
		}
	}

    echo("</select></td></tr>");
}


$formats = get_formats();

?>

<tr><td valign="top" align="left" width="100"><b>Title: *</b> </td><td valign="top" align="left"><input type="text" name="title" id="title" size="40" maxlength="255" value="<?php echo(htmlspecialchars($result['title'],ENT_QUOTES));?>" /></td></tr>
<tr><td valign="top" align="left" width="100"><b>Author: *</b> </td><td valign="top" align="left"><input type="text" name="author" id="author" maxlength="200" value="<?php echo(htmlspecialchars($result['author'],ENT_QUOTES));?>" /></td></tr>
<tr><td valign="top" align="left" width="100"><b>Format: *</b> </td><td valign="top" align="left">
<?php
if($formats == FALSE){
    echo("<p><b>ERROR:</b>Could not load formats!</p>");
}else{

   echo("<select name='format' id='format'>");

    foreach($formats as $option){

        if($option['id'] == $result['format']){
            echo("<option value='".$option['id']."' selected='selected'>".$option['name']."</option>");
         }else{
            echo("<option value='".$option['id']."'>".$option['name']."</option>");
         }

    }

    echo("</select>");
}
?></td></tr>

<tr><td valign="top" align="left" width="100"><b>Publisher:</b> </td><td valign="top" align="left"><input type="text" name="publisher" maxlength="200" value="<?php echo(htmlspecialchars($result['publisher'],ENT_QUOTES));?>" /></td></tr>
<tr><td valign="top" align="left" width="100"><b>ISBN:</b> </td><td valign="top" align="left"><input type="text" onblur="CheckISBNChanged(this.value);" name="isbn" value="<?php echo(htmlspecialchars($result['isbn'],ENT_QUOTES));?>" /></td></tr>
<tr><td valign="top" align="left" width="100"><b>Pages:</b> </td><td valign="top" align="left"><input type="text" name="pages" size="4" value="<?php echo($result['pages']);?>" /><br /></td></tr>

<tr><td valign="top" align="left" width="100"><b>Read: *</b> </td><td valign="top" align="left">
<?php

    $read_options[0]['id']=1;
    $read_options[0]['name']="Read";
    $read_options[1]['id']=0;
    $read_options[1]['name']="Not Read";
    $read_options[2]['id']=2;
    $read_options[2]['name']="Reading";

    echo("<select name='self_read'>");

    foreach($read_options as $option){

        if($option['id'] == $result['self_read']){
            echo("<option value='".$option['id']."' selected='selected'>".$option['name']."</option>");
         }else{
            echo("<option value='".$option['id']."'>".$option['name']."</option>");
         }

    }

    echo("</select>");

?></td></tr>

<tr><td valign="top" align="left" width="100"><b>On Loan:</b> </td><td valign="top" align="left"><input type="text" name="on_loan" value="<?php echo(htmlspecialchars($result['self_loaned'],ENT_QUOTES));?>" /></td></tr>

</table>

<br />

<textarea name="description" cols="40" rows="5"><?php echo(htmlspecialchars($result['description'],ENT_QUOTES));?></textarea>
<br /><input type="checkbox" id="clear_covers" name="clear_covers" value="1" /> Clear cached cover images?

<input type="hidden" name="create" value="1" />
<input type="hidden" name="book_id" value="<?php echo($book_id);?>" />
<br /><br />
<input type="submit" value="Save" name="formSubmit" onclick="return validateForm();" />
</form>


<?php
require(LIBRARY_DIR."/templates/footer.php");
?>