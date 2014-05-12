<?php


/**
  * @author Sherri Wheeler
  * @version  1.20
  * @access   public
  * @copyright Copyright (c) 2005 - All Rights Reserved.
  */


/*********************************************************************
* DB ABSTRACTION OBJECT for MYSQL
*
* This class provides a basic abstraction of database access.
* Currently abstracts ADODB ( !!! adodb directory must be in same dir as this class file !!!).
*
* Recommended to instantiate this in a config or top of page, then use
* global on the object to access it in functions and other objects.
*
***********************************************************************/


/**
  * Require in the adodb files.      
  */
require("adodb/adodb.inc.php");
 
  
/**
  * DB Class
  */
class DB {

	/**
      * The connection object.
      * @var  object         
      */
	var $dbc;
	
	/**
      * The database name.
      * @var  string        
      */
	var $db_name;	
	
	/**
      * The database user.
      * @var  string        
      */
	var $user;
	
	/**
      * The database password.
      * @var  string        
      */
	var $pass;

	/**
      * The database host name.
      * @var  string        
      */
	var $host;
	
	/**
      * The most recent error message (For debugging).
      * @var  string        
      */
	var $err_msg;	
	
	
	
	
	/**
	  * Initiates the instance variables
	  *
	  * @return void
	  * @access public
	  */
	function init() {
		
		// Initialize any vars.	
		
	}
	
	
	/**
	  * Sets the db variables.
	  *
	  * @return void
	  * @param string $db_name
	  * @param string $user
	  * @param string $pass
	  * @param string $host
	  * @access public
	  */
	function set_db_settings($db_name, $user, $pass, $host){		
		
		$this->db_name 	= $db_name;
		$this->host		= $host;
		$this->user		= $user;
		$this->pass		= $pass;		
		
	}
	
	
	/**
	  * Connect to the db.
	  *
	  * @return void
	  * @access public
	  */
	function connect() {
		
		// Connect to db.
		$this->dbc = NewADOConnection("mysql");
		$this->dbc->Connect( $this->host, $this->user, $this->pass, $this->db_name );
	}
	
	
	/**
	  * Disconnect from the db.
	  *
	  * @return void
	  * @access public
	  */
	function disconnect() {
		
		// DisConnect from db.
		$this->dbc->Close();
		unset($this->dbc);		
	}
	
	
	
	/**
	  * Run a NON select query on the db.
	  *
	  * @return object
	  * @param string $query
	  * @access public
	  */
	function query($query) {
		
		// Run a query and return the results. Set $err_msg if there was a problem.
		$result = $this->dbc->Execute($query);
				
		// ERROR CHECKING		
		if($result===FALSE){
			$this->err_msg = $this->dbc->ErrorMsg();
			return FALSE;		
		}else{		
			return TRUE;
		}
		
	}
	
	
	/**
	  * Run a multi row select query on the db.
	  *
	  * @return array
	  * @param string $query
	  * @access public
	  */
	function select($query) {
		
		// Run a query and return the results. Set $err_msg if there was a problem.
		
		$result = $this->dbc->GetAll($query);
		
		// ERROR CHECKING		
		if($result===FALSE || $result->EOF || $result == NULL){
			$this->err_msg = $this->dbc->ErrorMsg();
			return FALSE;		
		}			
		
		return $result;		
	}
	
	
	
	/**
	  * Run a single row select query on the db.
	  *
	  * @return array
	  * @param string $query
	  * @access public
	  */
	function getRow($query) {
		
		// Run a query and return the results. Set $err_msg if there was a problem.
		$result = $this->dbc->GetRow($query);
		
		// ERROR CHECKING		
		if($result===FALSE || $result->EOF || $result == NULL){
			$this->err_msg = $this->dbc->ErrorMsg();
			return FALSE;		
		}			
		return $result;			
	}
	
	
	/**
	  * Quickly grab one value from the db based on table name and key value.
	  * Error or no result will return NULL;
	  *
	  * @return string
	  * @param string $tblname
	  * @param int $key_value
	  * @param string $col_name
	  * @param string $key_name
	  * @access public
	  */
	function getValue($tblname, $key_value, $col_name, $key_name="id"){		
		
		$result = $this->dbc->GetRow("select ".$col_name." from ".$tblname." WHERE ".$key_name."='".$key_value."'");	

		// ERROR CHECKING		
		if($result===FALSE || $result->EOF || $result == NULL){
			$this->err_msg = $this->dbc->ErrorMsg();
			return FALSE;		
		}						
		
		return $result[$col_name];		
	}	
	
	
	/**
	  * Return the id of the last inserted row or FALSE on fail.
	  *
	  * @return int
	  * @access public
	  */
	function insertedId() {			
		return $this->dbc->Insert_ID();
	}
	
	
	
	/**
	  * Return the number of affected rows.
	  *
	  * @return int
	  * @access public
	  */
	function affectedRows() {		
		return $this->dbc->Affected_Rows();
	}


	/**
	  * Escape the given string for this db connection. Second parameter tells it whether to pass the magic quotes flag for get, post and cookie escaping.
	  *
	  * @return string
	  * @access public
	  */
	function escapeStr($inStr, $isGPC=FALSE) {
		if($isGPC){
			$str = $this->dbc->qstr($inStr, get_magic_quotes_gpc());
		}else{
			$str = $this->dbc->qstr($inStr, FALSE);
		}
		// Since the ADODB qstr function wraps single quotes around the string for us, we don't want this, in order to keep it
		// consistent with the NON-Adodb version of this class. So, we need to strip the single quotes off. Yuk.
		return substr( $str, 1, strlen($str)-2);
	}
	
	
} // End Class DB.
?>