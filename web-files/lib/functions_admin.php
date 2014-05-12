<?php
/**
 * Process form data and create the new book record in the db.
 */
function save_book(){

    global $dba;

    if( empty($_POST['title']) || empty($_POST['author']) || empty($_POST['format']) || empty($_POST['category']) || intval($_POST['category']) >= 1000 ){

        echo("<p><b>ERROR: Could not save book. Fill out req. fields.</b></p>");
        return;
    }

	// Check for duplicate title

	$SQL = "SELECT count(id) as cnt from library where (title='".$dba->escapeStr($_POST['title'],TRUE)."' AND format=".intval($_POST['format']).")";

    if(!empty($_POST['isbn'])){
    	$SQL .= "OR isbn='".$dba->escapeStr($_POST['isbn'],TRUE)."'";
    }

	$result = $dba->getRow($SQL);

	if($result === FALSE || intval($result['cnt']) > 0){
		echo("<p><b>ERROR: That title/isbn has already been added.</b></p>");
		return;
	}


	// Insert book.

    $SQL = "insert into library(title, author, publisher, isbn, format, description, pages, category, self_read, self_loaned)
              values(
              '".$dba->escapeStr($_POST['title'],TRUE)."',
              '".$dba->escapeStr($_POST['author'],TRUE)."', ";


              if(!empty($_POST['publisher'])){
                 $SQL .= "'".$dba->escapeStr($_POST['publisher'],TRUE)."', ";
              }else{
                  $SQL .= "NULL, ";
              }

              if(!empty($_POST['isbn'])){
                 $SQL .= "'".$dba->escapeStr($_POST['isbn'],TRUE)."', ";
              }else{
                  $SQL .= "NULL, ";
              }


     $SQL .= "'".intval($_POST['format'])."', ";

              if(!empty($_POST['description'])){
                 $SQL .= "'".$dba->escapeStr($_POST['description'],TRUE)."', ";
              }else{
                  $SQL .= "NULL, ";
              }



     $SQL .= "'".intval($_POST['pages'])."',
              '".intval($_POST['category'])."',
              '".intval($_POST['self_read'])."', ";


              if(!empty($_POST['on_loan'])){
                 $SQL .= "'".$dba->escapeStr($_POST['on_loan'],TRUE)."' ";
              }else{
                  $SQL .= "NULL ";
              }


       $SQL .= ")";


      $result = $dba->Query($SQL);

      if($result === FALSE){
         echo("<p><b>Save failed.</b></p>");
      }else{
		$insertedid = $dba->insertedId();
		echo("<p><b>Saved.</b>&nbsp;&nbsp;<a href='".LIBRARY_WEB."admin/index.php?view_book=".$insertedid."'>[view]</a></p>");
      }
}


/**
 * Process form data and update the book record in the db.
 */
function update_book(){

    global $dba;

    $book_id = intval($_POST['book_id']);

    if( empty($_POST['title']) || empty($_POST['author']) || empty($_POST['format']) || empty($_POST['category']) || intval($_POST['category']) >= 1000 ){

        echo("<p><b>ERROR: Could not save book. Fill out req. fields.</b></p>");
        return $book_id;

    }


	$SQL = "update library set
			title='".$dba->escapeStr($_POST['title'],TRUE)."',
			author='".$dba->escapeStr($_POST['author'],TRUE)."', ";


              if(!empty($_POST['publisher'])){
                 $SQL .= "publisher='".$dba->escapeStr($_POST['publisher'],TRUE)."', ";
              }else{
                  $SQL .= "publisher=NULL, ";
              }

              if(!empty($_POST['isbn'])){
                 $SQL .= "isbn='".$dba->escapeStr($_POST['isbn'],TRUE)."', ";
              }else{
                  $SQL .= "isbn=NULL, ";
              }


     $SQL .= "format='".intval($_POST['format'])."', ";

              if(!empty($_POST['description'])){
                 $SQL .= "description='".$dba->escapeStr($_POST['description'],TRUE)."', ";
              }else{
                  $SQL .= "description=NULL, ";
              }


    if(intval($_POST['clear_covers']) == 1){
        $SQL .= "cover_sm=NULL, cover_lrg=NULL, ";
        $coversMsg = "&nbsp;&nbsp;(Cover images set to re-cache.)";
	}else{
		$coversMsg = "";
	}



      $SQL .= "pages='".intval($_POST['pages'])."',
              category='".intval($_POST['category'])."',
              self_read='".intval($_POST['self_read'])."', ";


              if(!empty($_POST['on_loan'])){
                 $SQL .= "self_loaned='".$dba->escapeStr($_POST['on_loan'],TRUE)."' ";
              }else{
                  $SQL .= "self_loaned=NULL ";
              }


      $SQL .= " WHERE id=".$book_id;

      $result = $dba->Query($SQL);

      if($result === FALSE){
         echo("<p><b>Save failed.</b></p>");
         return $book_id;
      }else{
         echo("<p><b>Saved.</b>&nbsp;&nbsp;<a href='".LIBRARY_WEB."admin/index.php?view_book=".$book_id."'>[view]</a>".$coversMsg."</p>");
         return $book_id;
      }

}

?>