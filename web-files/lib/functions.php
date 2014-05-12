<?php

/* Perform all book search operations and display the results.
 */
function book_search($is_admin=0){

	global $dba;	
	
	// First, build the search SQL and determine search type.	
	
	if( isset($_POST['category']) || ( isset($_POST['text_search']) && intval($_POST['text_search'])==1 ) ){
	
		$read_status = intval($_POST['read_status']);
	
		if($is_admin == 1){
		
			if($read_status == 0){
				$read_status = " AND (library.self_read=0)";
			}elseif($read_status == 1){
				$read_status = " AND (library.self_read=1)";
			}elseif($read_status == 2){
				$read_status = " AND (library.self_read=2)";
			}else{
				$read_status = '';
			}
		
		}else{
			$read_status = '';
		}	
	
		if( isset($_POST['results']) && intval($_POST['results'])>=1 ){
			$pagination = intval($_POST['results']);
			$limit = " LIMIT ".(($pagination-1)*RESULTS_PER_PAGE).", ".RESULTS_PER_PAGE;
		}else{
			$pagination = 0;
			$limit = "";
		}
		
		if( isset($_POST['sort']) && $_POST['sort']!="" ){
			switch ($_POST['sort']) {
				case "td":
					$orderby = ' ORDER BY library.title DESC';
					break;
				case "tu":
					$orderby = ' ORDER BY library.title ASC';
					break;
				case "ad":
					$orderby = ' ORDER BY library.author DESC';
					break;
				case "au":
					$orderby = ' ORDER BY library.author ASC';
					break;
				case "cd":
					$orderby = ' ORDER BY lc2.name DESC';
					break;
				case "cu":
					$orderby = ' ORDER BY lc2.name ASC';
					break;
				case "rd":
					$orderby = ' ORDER BY library.self_read DESC';
					break;
				case "ru":
					$orderby = ' ORDER BY library.self_read ASC';
					break;
				default:
					$orderby = ' ORDER BY library.title ASC';
			}
		
		}else{
			$orderby = ' ORDER BY library.title ASC';
		}
	
		// Perform plain text search.
		if( isset($_POST['text_search']) && intval($_POST['text_search'])==1 ){
			
            $pterm = $_POST['term'];
            
            if (!empty($pterm) && get_magic_quotes_gpc()) {
                $pterm = stripslashes($pterm);
            }
            
			$SQL = "select distinct library.title, library.author, library.id, library.self_read, lc2.name as category_name, library_format.short_name as format from library_format, library, library_category, library_category as lc2 WHERE
					(
					( (MATCH(title,author,publisher,description) AGAINST('".$dba->escapeStr($pterm, TRUE)."' IN BOOLEAN MODE))
					 OR
					 (library.isbn='".$dba->escapeStr($pterm, TRUE)."')
					)                 
					AND
					 (lc2.id=library.category) 
					 AND
					 (library_format.id=library.format)
					 ".$read_status."  
					)".$orderby;   			
			

		}else{	// Perform search by category.				
				
			$iparent = intval($_POST['parent']);        
			$icat = intval($_POST['category']);
				
			if($icat >= 1000){	// Did we select a super-category?
				
				$iparent = $icat / 1000;
				
				$SQL = "select distinct library.title, library.author, library.id, library.self_read, lc2.name as category_name, library_format.short_name as format from library_format, library, library_category, library_category as lc2 WHERE
						(
						
						((library.category=".$iparent.")
						OR
						((library.category = library_category.id)
						  AND
						 (library_category.parent=".$iparent.")
						))                   
						AND
						
						 (lc2.id=library.category) 
						AND
						(library_format.id=library.format)
						
						".$read_status." 
						
						)".$orderby;        
		
			}else{
			
				// Don't search sub categories. Do specific category.                
				$SQL = "select distinct library.title, library.author, library.id, library.self_read,  lc2.name as category_name, library_format.short_name as format from library_format, library, library_category, library_category as lc2 WHERE ( ";
				if($icat != -1){
					$SQL .= "(library.category=".$icat.") AND ";
				}
				$SQL .= " (lc2.id=library.category) AND (library_format.id=library.format) ".$read_status." )".$orderby;
			
			}
			
		}
	
			
		// Perform Search with the SQL we built:
	
		$resultsCheck = $dba->Select($SQL);
		$totalResults = $dba->affectedRows();
		$lastPage = intval(ceil($totalResults / RESULTS_PER_PAGE));
		
		$results = $dba->Select($SQL.$limit);
	
		if($resultsCheck===FALSE || $results === FALSE){
		
			echo("<p><b>Search Results:</b></p>");
			echo("<p>No results matched your query.</p>");
	
		}else{
			if($pagination == 0){
				$showing = "all ".$totalResults." results";
			}else{
				$endRes = (($pagination-1)*RESULTS_PER_PAGE) + RESULTS_PER_PAGE;
				if($endRes > $totalResults){
					$endRes = $totalResults;
				}
				$showing = (($pagination-1)*RESULTS_PER_PAGE) +1;
				$showing .= " to ".$endRes;
				$showing .= " out of ".$totalResults;
			}
			?>
		
			<script type="text/Javascript" language="Javascript">
		
				function changePage(resultNum)
				{				
					document.getElementById("results1").value = resultNum;
					document.getElementById("results2").value = resultNum;					
					resubmitSearchForm();				
				}
				
				function changeSort(column)
				{
					var sortField1 = document.getElementById("sort1");
					var sortField2 = document.getElementById("sort2");

					if(column == "Title"){
						if(sortField1.value == "tu"){
							sortField1.value = "td";
							sortField2.value = "td";
						}else{
							sortField1.value = "tu";
							sortField2.value = "tu";
						}
					}else if(column == "Author"){
						if(sortField1.value == "au"){
							sortField1.value = "ad";
							sortField2.value = "ad";
						}else{
							sortField1.value = "au";
							sortField2.value = "au";
						}
					}else if(column == "Category"){
						if(sortField1.value == "cu"){
							sortField1.value = "cd";
							sortField2.value = "cd";
						}else{
							sortField1.value = "cu";
							sortField2.value = "cu";
						}
					}else if(column == "Read"){
						if(sortField1.value == "ru"){
							sortField1.value = "rd";
							sortField2.value = "rd";
						}else{
							sortField1.value = "ru";
							sortField2.value = "ru";
						}
					}
					resubmitSearchForm();
				}

				function toggleSortHeaders(sort){

					document.getElementById("sortt").className = "sortable";
					document.getElementById("sorta").className = "sortable";
					document.getElementById("sortc").className = "sortable";
					<?php if($is_admin == 1){ ?>
						document.getElementById("sortr").className = "sortable";
					<?php } ?>

					var col = sort.substr(0,1);
					var dir = sort.substr(1,1);

					document.getElementById("sort"+col).className = "sorted-"+dir;

				}
				
				function resubmitSearchForm(){
					<?php if( isset($_POST['text_search']) && intval($_POST['text_search'])==1 ){ ?>
						document.getElementById('textsearch').submit();					
					<?php }else{ ?>
						document.getElementById('catsearch').submit();
					<?php } ?>		
				}
			
			</script>			
			
			<?php echo("<p><b>Search Results (".$showing."):</b> &nbsp;&nbsp;&nbsp;<a href='".$_SERVER['PHP_SELF']."'>(Clear)</a></p>"); ?>

			<div class="pagination">				
				<?php
				
								
				// TODO: There must be a better way to output this navigation.
				// Output in reverse order due to the float:right css.
							
				if($totalResults > RESULTS_PER_PAGE){					
														
					// Last Page
					if($pagination != $lastPage){
						echo('<div class="pagebtn" onclick="javascript:changePage('.$lastPage.');"><a href="javascript:changePage('.$lastPage.');" title="Last Page">&gt;&gt;</a></div>');
					}else{
						echo('<div class="pagebtn-disabled">&gt;&gt;</div>');
					}

					// Next Page
					if($pagination >= 1 && $pagination < $lastPage){
						echo('<div class="pagebtn" onclick="javascript:changePage('.($pagination+1).');"><a href="javascript:changePage('.($pagination+1).');" title="Next Page">&gt;</a></div>');
					}else{
						echo('<div class="pagebtn-disabled">&gt;</div>');
					}


					// Determine start and end of page jumps:
					$end = $pagination + intval(floor((PAGE_JUMPS)/2));
					if(  intval(floor((PAGE_JUMPS)/2))  > $pagination-1){
						$end += intval(floor((PAGE_JUMPS-1)/2)) - ($pagination-1);
					}

					if($end > $lastPage){
						$end = $lastPage;
					}
					$left = PAGE_JUMPS - ($end - $pagination)-1;

					$start = $pagination - $left;
					if($start < 1){
						$start = 1;
					}

					// Individual Page Jump-To
					for($i = $end; $i>= $start; $i--){

						if($i==$pagination){
							echo('<div class="pagebtn-disabled pagebtn-active">'.$i.'</div>');
						}else{
							echo('<div class="pagebtn" onclick="javascript:changePage('.($i).');"><a href="javascript:changePage('.($i).');" title="Page '.$i.'">'.$i.'</a></div>');
						}
					}

					// Previous Page
					if($pagination > 1){
						echo('<div class="pagebtn" onclick="javascript:changePage('.($pagination-1).');"><a href="javascript:changePage('.($pagination-1).');" title="Previous Page">&lt;</a></div>');
					}else{
						echo('<div class="pagebtn-disabled">&lt;</div>');
					}

					// First Page
					if($pagination != 1){
						echo('<div class="pagebtn" onclick="javascript:changePage(1);"><a href="javascript:changePage(1);" title="First Page">&lt;&lt;</a></div>');
					}else{
						echo('<div class="pagebtn-disabled">&lt;&lt;</div>');
					}
				
				}


				// All
				if($pagination != 0 && $totalResults > RESULTS_PER_PAGE){
					echo('<div class="pagebtn" onclick="javascript:changePage(0);"><a href="javascript:changePage(0);" title="All Results (loading time may vary)">All</a></div>');
				}else{
					if($pagination==0){
						$css = "pagebtn-disabled pagebtn-active";
					}else{
						$css = "pagebtn-disabled";
					}
					echo('<div class="'.$css.'">All</div>');
				}


				echo('<div style="float:right;font-size:10pt;margin-top:5px;">'.$lastPage.' Page(s):&nbsp;&nbsp;</div>');

				?>				
			</div>
			
			<?php		   
			$color = "#EADEAE";			
			$color_flag = TRUE;
			   
			echo('<div style="clear:both;"></div><table class="searchtable" border="0" cellpadding="1" cellspacing="0" width="100%">');
			
			echo('<tr><th align="left" id="sortt" onclick="changeSort(\'Title\');" class="sortable">Title</th><th align="left" id="sorta" onclick="changeSort(\'Author\');" class="sortable">Author</th><th align="left" id="sortc" onclick="changeSort(\'Category\');" class="sortable">Category</th>');
			if($is_admin == 1){	
				echo('<th width="100">Manage</th><th width="64" id="sortr" onclick="changeSort(\'Read\');" class="sortable">Read?</th>');
			}
			echo('</tr>');
			
			foreach($results as $result){	
				   
				echo('<tr bgcolor="'.$color.'">');
				$color_flag = !$color_flag;
				
				echo("<td width='300'><a href='".$_SERVER['PHP_SELF']."?view_book=".intval($result['id'])."'><b>".htmlspecialchars($result['title'],ENT_QUOTES).'</b></a>&nbsp;&nbsp;&nbsp;('.htmlspecialchars($result['format'],ENT_QUOTES).')</td>');
				echo('<td>'.htmlspecialchars($result['author'],ENT_QUOTES).'</td>');
				echo('<td>'.htmlspecialchars($result['category_name'],ENT_QUOTES).'</td>');
	   
	
				if($is_admin == 1){			   
					
					echo("<td align='center'><a href='edit.php?id=".intval($result['id'])."'>[Edit]</a>&nbsp;&nbsp;&nbsp;");
					echo("<a href='delete.php?id=".intval($result['id'])."' onclick=\"return confirm('Are you sure you want to delete this?');\">[Delete]</a></td>");
					echo('<td align="center">');
					if(intval($result['self_read'])==0){
						echo("<img src='".LIBRARY_WEB."images/pip-notread.gif' border='0' alt='NOT READ' title='NOT READ' />");
					}elseif(intval($result['self_read'])==2){
						echo("<img src='".LIBRARY_WEB."images/pip-reading.gif' border='0' alt='READING' title='READING' />");
					}elseif(intval($result['self_read'])==1){
						echo("<img src='".LIBRARY_WEB."images/pip-read.gif' border='0' alt='READ' title='READ' />");
					}
					echo('</td>');
				}
	
				echo("</tr>");
				
				if($color_flag){
					$color = "#EADEAE";
				}else{
					$color = "#F5EED3";
				}	
				
			} // End Foreach.
			
			echo('</table>');

			echo('<script type="text/Javascript" language="Javascript">toggleSortHeaders("'.htmlspecialchars($_POST['sort']).'");</script>');
	
		}
	
		echo("<br /><br />");
	
	}
	
		
	
	if( isset($_GET['view_book']) ){	
		view_book($is_admin);	
	}
	
	
	// **************************************
	// Display the search form:
	require(LIBRARY_DIR."/lib/book_search_form.php");
	show_search_form($is_admin);
	// **************************************


}  // End function book_search().



/* Show the details for the selected book.
 */
function view_book($is_admin){

	global $dba;

	?>
	<br />
	<table border="0" cellpadding="2" cellspacing="0" width="100%" style="border:1px solid #BC9F63;">
	<tr><td bgcolor="#FFF9DF">	
	<b>Title:&nbsp;</b>
	
	<?php

	// Show details for the selected book.

	$book_id = intval($_GET['view_book']);

	$SQL = "select library.*,  lc2.name as category_name, library_format.long_name from library, library_format, library_category as lc2 WHERE
			(
			(library.id=".$book_id.")                    
				AND
			(lc2.id=library.category) 
				AND
			(library_format.id=library.format)
			)";

	$result = $dba->getRow($SQL);

	if($result === FALSE){
		echo("<p>Could not load book details.</p>");
	}else{

		echo("<span style='font-size:12pt;color:#9F854F;'><b>".htmlspecialchars($result['title'],ENT_QUOTES)."</b></span><br /><br />");
		
		// Show Small Cover Image
		if(empty($result['cover_sm']) || empty($result['cover_lrg']) ){
		
			if( !empty($result['isbn']) ){
				$flat_isbn = GetISBN10($result['isbn']);
				echo("<a href='http://images.amazon.com/images/P/".$flat_isbn.".01._SCLZZZZZZZ_.jpg'><img src='http://images.amazon.com/images/P/".$flat_isbn.".01.TZZZZZZZ.jpg' border='1' align='right' alt='Cover' style='border: 1px solid #000000' /></a>");
			}
		
		}else{
		
			// USE CACHED IMAGES
			echo("<a href='".LIBRARY_WEB."images/covers/lrg/".$result['cover_lrg']."'><img src='".LIBRARY_WEB."images/covers/sm/".$result['cover_sm']."' border='1' align='right' alt='Cover' style='border: 1px solid #000000' /></a>");
					
		}

		echo("<table border='0'>");

		echo("<tr><td align='left' width='80'><b>Author:</b> </td><td align='left'>".htmlspecialchars($result['author'],ENT_QUOTES)."</td>");
		echo("<td rowspan='3' width='40'>&nbsp;&nbsp;&nbsp;&nbsp;</td>");
		echo("<td align='left' width='80'><b>Format:</b>  </td><td align='left'>".htmlspecialchars($result['long_name'],ENT_QUOTES)."</td></tr>");

		echo("<tr><td align='left'><b>Category:</b> </td><td align='left'>".htmlspecialchars($result['category_name'],ENT_QUOTES)."</td>");
		echo("<td align='left'><b>Publisher:</b> </td><td align='left'>".htmlspecialchars($result['publisher'],ENT_QUOTES)."&nbsp;</td></tr>");

		echo("<tr><td align='left'><b>ISBN:</b> </td><td align='left'>".htmlspecialchars($result['isbn'],ENT_QUOTES)."&nbsp;</td>");
		echo("<td align='left'><b>Pages:</b> </td><td align='left'>".intval($result['pages'])."</td></tr>");
		
		if($is_admin == 1){
			$read_status = intval($result['self_read']);			
			if($read_status == 0){
				$imgStr = "<img src='".LIBRARY_WEB."images/pip-notread.gif' border='0' alt='NOT READ' title='NOT READ' />";
				$readStr = "<b>Not read.</b>";
			}elseif($read_status == 1){
				$imgStr = "<img src='".LIBRARY_WEB."images/pip-read.gif' border='0' alt='READ' title='READ' />";
				$readStr = "<b>Read.</b>";
			}elseif($read_status == 2){
				$imgStr = "<img src='".LIBRARY_WEB."images/pip-reading.gif' border='0' alt='READING' title='READING' />";
				$readStr = "<b>Reading.</b>";
			}else{
				$imgStr = "Unknown.";
				$readStr = "<b>Read Status:</b>";
			}
			echo("<tr><td align='left'>".$readStr." </td><td align='left'>".$imgStr."</td>");
			echo("<td width='40'>&nbsp;&nbsp;&nbsp;&nbsp;</td>");
			echo("<td align='left'><b>On Loan:</b> </td><td align='left'>".htmlspecialchars($result['self_loaned'],ENT_QUOTES)."&nbsp;</td></tr>");
		}

		echo("</table>");

		echo("<br /><b>Description:</b><br />".nl2br(htmlspecialchars($result['description'],ENT_QUOTES)).'<br /><br />');
			

		if(AMAZON_ENABLED){
			show_amazon_info($result['isbn']);	
		} // End Amazon Info Section.
			   

		if($is_admin == 1){		
			echo("<a href='edit.php?id=".intval($result['id'])."'>[Edit]</a> | ");
			echo("<a href='delete.php?id=".intval($result['id'])."' onclick=\"return confirm('Are you sure you want to delete this?');\">[Delete]</a>");
		}
			   
		// ***** Check If Covers Are Missing **********			   
		if(empty($result['cover_sm']) || empty($result['cover_lrg']) ){
			backup_covers($result['isbn'], $book_id);
		}
		// ******			  
			   
	}

	echo("<br /></td></tr></table><br />");

}

/* Actually show the info from Amazon about this book.
 * Requires an Amazon API key to be set in the config.
 */
function show_amazon_info($isbn)
{
    $xmlob = fetch_amazon_info($isbn);

    if( $xmlob !== FALSE && !empty($xmlob->Items->Item->ItemAttributes->Title) ) {
		/*
		// Debugging- show the Amazon XML:
		echo(htmlspecialchars($xml));
		echo("<br /><br /><pre>");
		var_dump($xmlob);
		echo("</pre><br />");
		*/

		echo("<b>Amazon Info For This Book:</b><br /><br />");
		echo("<b>Title:</b> ".$xmlob->Items->Item->ItemAttributes->Title."<br />");
		echo("<b>Author:</b> ".$xmlob->Items->Item->ItemAttributes->Author."<br />");
		echo("<b>Avg Customer Rating:</b> ".$xmlob->Items->Item->CustomerReviews->AverageRating." / 5.0 stars<br />");

		echo("<b>Amazon Page:</b> <a  target=\"_blank\" href=\"".$xmlob->Items->Item->DetailPageURL."\">Click Here</a><br /><br />");
	}
}

/* Query Amazon for some information about this book.
 * Requires an Amazon API key to be set in the config.
 * Uses the new signed request method with public and private keys.
 */
function fetch_amazon_info($isbn){

	if( AMAZON_ENABLED && !empty($isbn) ){

		// **** Get Amazon Webservice info: *****
		$method = "GET";
		$host = "ecs.amazonaws.com";
		$uri = "/onca/xml";
		$flat_isbn    = GetISBN10($isbn);
		$base         = 'http://'.$host.$uri;
		$query_string = '';
		
		$params = array(
			'Service' => 'AWSECommerceService',
			'AWSAccessKeyId'  => AMAZON_ACCESS_KEY,
            'AssociateTag' => AMAZON_ASSOCIATE_TAG,
			'Operation'  => 'ItemLookup',
			'ItemId' => $flat_isbn,
			'IdType' => 'ASIN',
			'ResponseGroup' => 'Large',
			'Timestamp' => gmdate("Y-m-d\TH:i:s\Z"),
			'SignatureVersion' => 2,
			'SignatureMethod' => 'HmacSHA256'
		);
		
		ksort($params);
		
		foreach ($params as $key => $value) { 
			$query_string .= $key."=".urlencode($value)."&";
		}
		
		$query_string = rtrim($query_string, "&");
		
		$string2sign = $method."\n".$host."\n".$uri."\n".$query_string;
		$signature = base64_encode(hash_hmac("sha256", $string2sign, AMAZON_SECRET_KEY, True));
		$signature = urlencode($signature);
		
		$url = $base.'?'.$query_string."&Signature=".$signature;
	
		try{
            //echo($url); // Use for debugging Amazon query.
			$xml = file_get_contents($url);
			$xmlob = simplexml_load_string($xml);
		}catch(Exception $e){
			$xmlob = FALSE;
		}
		
		return($xmlob);
	
	}else{
		return(FALSE);

	}
}


/* Backup the cover images by copying them from Amazon to the webserver.
 */
function backup_covers($isbn, $book_id){

	global $dba;

	echo ("<br />");

	$flat_isbn = GetISBN10($isbn);

	// Try backing up cover images to server.
	
	// *** SMALL IMAGE
	
	if($flat_isbn === FALSE){
	
    	$name_sm = "not_found_sm.gif";
		echo("Could not back up small cover image (Invalid or missing ISBN).<br />\n");
		
    }else{
	
    	$source_sm = "http://images.amazon.com/images/P/".$flat_isbn.".01.TZZZZZZZ.jpg";
    	$name_sm = "cover_".$book_id."_sm.jpg";
    	$dest_sm = COVERS_SM.$name_sm;
    	$book_id = intval($book_id);
    
    	@unlink($dest_sm);
    	
    	$status = copy($source_sm, $dest_sm);
    	
    	if($status){	
    		$size = @getimagesize($dest_sm);
    		if($size===false || $size[0]<2){
    			@unlink($dest_sm);
    			$name_sm = "not_found_sm.gif";
				echo "Could not back up small cover image (Not found at Amazon).<br />";
    		}else{
				echo "SUCCESS: Backed up small cover image.<br />";
			}	
    	}else{
    		echo "Could not back up small cover image.<br />\n";
    		$name_sm = "not_found_sm.gif";
    	}
		
	} // End if (Isbn is false).
 
	// Update database:		
	$SQL = "update library set cover_sm='".$name_sm."' where id=".$book_id;
	$result = $dba->Query($SQL);	
	if($result === FALSE){        
		echo("ERROR: Failed saving small cover info to database.<br />\n");        
	}  
  
	// *** LARGE IMAGE
	
	if($flat_isbn === FALSE){
	
    	$name_lrg = "not_found_sm.gif";
		echo("Could not back up large cover image (Invalid or missing ISBN).<br />\n");
		
    }else{
	
    	$source_lrg = "http://images.amazon.com/images/P/".$flat_isbn.".01._SCLZZZZZZZ_.jpg";
    	$name_lrg = "cover_".$book_id."_lrg.jpg";
    	$dest_lrg = COVERS_LRG.$name_lrg;
    	
    	@unlink($dest_lrg);
      	
    	$status = copy($source_lrg, $dest_lrg);
    	      
    	if($status){    		
    		$size = @getimagesize($dest_lrg);
    		if($size===false || $size[0]<2){
    			@unlink($dest_lrg);
    			$name_lrg = "not_found_lrg.gif";
				echo "Could not back up small cover image (Not found at Amazon).<br />";
    		}else{
				echo "SUCCESS: Backed up large cover image.<br />";
			}
    	}else{
    		echo "Could not back up large cover image.<br />";
    		$name_lrg = "not_found_lrg.gif";
    	}
 
 	} // End if (Isbn is false).
	
	// Update database:	
	$SQL = "update library set cover_lrg='".$name_lrg."' where id=".$book_id;
	$result = $dba->Query($SQL);	
	if($result === FALSE){        
		echo("ERROR: Failed saving large cover info to database.<br />\n");        
	}

} // End function.


/* Gets all the categories into an array ordered for inclusion into a dropdown.
 * Currently only support 2 layers of category depth.
 */
function get_categories(){

    global $dba;
    $cat = array();
    
    // First, get all root categories.
    
    $SQL = "select * from library_category where parent=1";
    $results = $dba->Select($SQL);
    
    if($results === FALSE){
       return FALSE;
    }
    
    $cat_cnt = 0;
    
    // Build an array of category names and ids:
    
    foreach($results as $category){
    
        $cat[$cat_cnt]['id'] = intval($category['id']) * 1000;
        $cat[$cat_cnt]['name'] = $category['name'];
	$cat[$cat_cnt++]['root'] = true;
         
        // Get sub categories
    
        $SQL2 = "select * from library_category where parent=".intval($category['id'])." order by name";
        $results2 = $dba->Select($SQL2);
       
        if($results2 === FALSE){    
            return FALSE;    
        }else{
           
            foreach($results2 as $subcategory){    
                $cat[$cat_cnt]['id'] = intval($subcategory['id']);
                $cat[$cat_cnt]['name'] = $subcategory['name'];
		$cat[$cat_cnt++]['root'] = false;
            }
    
        }
    
    } // End while    
    
    return $cat;

} // End function.


/* Gets all book formats from the DB and stores them in an array.
 */
function get_formats(){

    global $dba;
    $formats = array();
    
    // Select all formats from db:
    
    $SQL = "select * from library_format";
    $results = $dba->Select($SQL);
    
    if($results === FALSE){
       return FALSE;
    }    
    
    // Format the records into an array for use in making a drop down list.
    
    $cat_cnt = 0;
    
    foreach($results as $format){    
        $formats[$cat_cnt]['id'] = intval($format['id']);
        $formats[$cat_cnt++]['name'] = $format['long_name'];    
    }   
    
    return $formats;

} // End function.


/* Check the session variables to determine if this is a logged in admin user.
 */
function is_logged_in(){

	if(is_array($_SESSION) && array_key_exists('user', $_SESSION)){
		$username= $_SESSION['user'];
		$loginkey= $_SESSION['key'];
		$ip= $_SERVER['REMOTE_ADDR'];
	}else{
		return(FALSE);
	}

	if(!empty($username) && !empty($loginkey) ){
		$temp_key = sha1($username.$ip);
		if($temp_key == $loginkey){
			return(TRUE);
		}else{
			return(FALSE);
		}		
	}else{
		return(FALSE);
	}	
}

/* Check if a given login is correct. Log in the user if it is.
 */
function user_login($username, $password, $ip){
		
	$password = sha1($password);
		
	if( ($username == ADMIN_USERNAME) && ($password==ADMIN_PASSWORD) ){
										
		$login_str = sha1($username.$ip);	// This string used to verify logged in users. Md5 of username, and ip address.
		$_SESSION['user'] = $username;
		$_SESSION['key'] = $login_str;			
		return(TRUE);
	}else{
		return(FALSE);
	}
}


/* Function to convert ISBN-13 codes to ISBN-10 (to use with Amazon).
 * Source: Comments on http://weblogs.asp.net/fmarguerie/archive/2007/12/23/isbn-13-to-isbn-10.aspx
 */
function GetISBN10($isbn13) {
	
    $isbn13 = preg_replace('/[^0-9X]/','',$isbn13); // trash all but digits and 'X'
    
	if(strlen($isbn13) == 10){
		return($isbn13);
	}else if(strlen($isbn13) != 13){
		return(FALSE);
	}
	
	$isbn10 = substr($isbn13,3,9);

	$checksum = 0;
	$weight = 10;

	$isbnCharArray = str_split($isbn10);

	foreach($isbnCharArray as $char) {

		$checksum += $char * $weight;
		$weight--;
	}	

	$checksum = 11-($checksum % 11);

	if ($checksum == 10) { $isbn10 = $isbn10 . "X"; }
	else if ($checksum == 11) { $isbn10 .= "0"; }
	else $isbn10 .= $checksum;

	return $isbn10;
}
?>