<?php

/* This function shows a search form with extra options for admins.
 */
function show_search_form($is_admin=0){

    // Init form values.
    $pcat = 	(isset($_POST['category'])) ? intval($_POST['category']) : "";
    $prstatus =	(($is_admin == 1) && isset($_POST['read_status'])) ? intval($_POST['read_status']) : "-1";
    $pterm = 	(isset($_POST['term'])) ? htmlspecialchars($_POST['term']) : "";
    $psort = 	(isset($_POST['sort'])) ? htmlspecialchars($_POST['sort']) : "tu";
    $presults = (isset($_POST['results'])) ?  htmlspecialchars($_POST['results']) : "1";
	
    if (!empty($pterm) && get_magic_quotes_gpc()) {
        $pterm = stripslashes($pterm);
    }
    
    echo("<h3>Search");
    if($is_admin==1){
    	echo(" and Manage");
    }
    echo(" Books</h3>");    
    echo("<br /><b>Browse Books By Category:</b>");
    
    $cat = get_categories();
    
    if($cat == FALSE){
        echo("<p><b>ERROR:</b>Could not load categories!</p>");
    }else{

    	echo('<form id="catsearch" action="'.$_SERVER['PHP_SELF'].'" method="post">');
       	echo('<select name="category">');
    	echo('<option value="-1">ALL CATEGORIES</option>');

    	foreach($cat as $option){
			// add name 'prefix'
            if($option['root'] == TRUE){
				$option['name'] = ">>" . $option['name'];
				$option['style'] = "font-weight:bold;font-style:italic;";
            }else{
				$option['name'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $option['name'];
				$option['style'] = "";
			}
			
            if($pcat==$option['id']){
				$sel = "selected='selected'";
			}else{
				$sel = "";
			}
    		echo("<option value='".$option['id']."' ".$sel." style='".$option['style']."'>".htmlspecialchars($option['name'],ENT_COMPAT,'ISO8859-15',FALSE)."</option>");
    	}	
    
    	echo("</select>");
    
        if($is_admin == 1){ ?>

    		<select name="read_status">
    			<option value="-1" <?php if($prstatus==-1){echo('selected="selected"');}?>>All</option>
    			<option value="0" <?php if($prstatus==0){echo('selected="selected"');}?>>Not Read</option>
    			<option value="1" <?php if($prstatus==1){echo('selected="selected"');}?>>Read</option>
    			<option value="2" <?php if($prstatus==2){echo('selected="selected"');}?>>Reading</option>
    		</select>
		<?php
    	}else{
            echo('<input type="hidden" name="read_status" value="-1" />');
        } 		
		?>
		
        <input type="hidden" id="results1" name="results" value="<?php echo($presults);?>" />
        <input type="hidden" id="sort1" name="sort" value="<?php echo($psort);?>" />
    	<input type='submit' value='GO' />&nbsp;&nbsp;&nbsp;&nbsp;<input style="font-weight:bold;" type="button" value="?" onclick="alert('Browse By Category\n\n -Select a category to view all books in that category. Or,\n -Choose the super-category to view books in all sub-categories.');" /><br />   	
    	</form>

    <?php
    }    
    ?>
    
    <p>OR</p>
    
    <b>Search Book Info:</b>
    <form  id='textsearch' action="<?php echo($_SERVER['PHP_SELF']);?>" method="post">
    
    <input type="hidden" name="text_search" value="1" />
    <input type="text" name="term" value="<?php echo($pterm);?>" />
    
    <?php if($is_admin == 1){ ?>
    
    	<select name="read_status">
    		<option value="-1" <?php if($prstatus==-1){echo('selected="selected"');}?>>All</option>
    		<option value="0" <?php if($prstatus==0){echo('selected="selected"');}?>>Not Read</option>
    		<option value="1" <?php if($prstatus==1){echo('selected="selected"');}?>>Read</option>
    		<option value="2" <?php if($prstatus==2){echo('selected="selected"');}?>>Reading</option>
    	</select>
    
    <?php }else{ ?>
    	<input type="hidden" name="read_status" value="-1" />
    <?php } ?>
    
    <input type="hidden" id="results2" name="results" value="<?php echo($presults);?>" />
        <input type="hidden" id="sort2" name="sort" value="<?php echo($psort);?>" />
    <input type="submit" value="Go" />&nbsp;&nbsp;&nbsp;&nbsp;<input style="font-weight:bold;" type="button" value="?" onclick="alert('Search Book Info\n\n -Searches in: title, author, publisher, isbn and description.\n -Use * as a wildcard for partial word matches, example: purpl* .\n -Enter multiple words separated by a space.\n -Common words are ignored.');" /><br />

    
    </form>

<?php
} // End Function show_search_form().
?>