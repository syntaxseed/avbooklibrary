<?php
 
/**
  * MySQL/PHP Database access class with NO abstraction layer.
  * @author Sherri Wheeler
  * @version  3.01
  * @copyright Copyright (c) 2013, Sherri Wheeler - Avinus - www.avinus.com
  * This software is available for use ONLY via the licence terms.
  */
 
 
/*********************************************************************
* DB ABSTRACTION OBJECT for MYSQL/PHP
*
* This class provides a basic abstraction of database access.
* Uses the PHP MySQLi Extension.
*
* Recommended to instantiate this in a config or top of page, then use
* global on the object to access it in functions and other objects.
*
***********************************************************************/
 
 
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
      * The database connection port.
      * @var  int        
      */
	var $port;
    
	/**
      * The database connection socket.
      * @var  string        
      */
	var $socket;
    
	/**
      * The most recent error message (For debugging).
      * @var  string        
      */
	var $err_msg;		
 
	/**
      * The most recent query passed to this class.
      * @var  string        
      */
	var $last_query;	

	/**
      * The number of milliseconds it took the query to execute.
      * @var  int        
      */
	var $query_time; 
 
	/**
	  * Initiates the instance variables
	  *
	  * @return void
	  * @access public
	  */
	function init() {
 
		// Initialize any vars.	
		$this->last_query = "";
		$this->query_time = 0;
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
	function set_db_settings($db_name, $user, $pass, $host, $port=NULL, $socket=NULL){		
 
		$this->db_name 	= $db_name;
		$this->host	= $host;
        $this->port	= $port;
        $this->socket = $socket;
		$this->user	= $user;
		$this->pass	= $pass;

        // DEPRECATED: Support for legacy Avinus apps which send in a socket as part of the host like so: localhost:/tmp/mysql5.sock
        if( $host == 'localhost:/tmp/mysql5.sock'){
            //trigger_error("Deprecated feature of custom Avinus DB class. Socket must be sent in separate from the host string.", E_USER_NOTICE);
            $this->host = 'localhost';
            $this->socket = '/tmp/mysql5.sock';
        }        
	}
 
 
	/**
	  * Connect to the db.
	  *
	  * @return boolean
	  * @access public
	  */
	function connect() {     

		// Connect to db.
        $this->dbc = new mysqli($this->host, $this->user, $this->pass, $this->db_name, $this->port, $this->socket);

		if (mysqli_connect_errno()) {
			$this->err_msg = "Failed connecting to database (" . mysqli_connect_errno() . ") " . mysqli_connect_error().".";
			return FALSE;
		}else{
            return TRUE;
        }
	}
 
 
	/**
	  * Disconnect from the db.
	  *
	  * @return void
	  * @access public
	  */
	function disconnect() {
        $status = mysqli_close($this->dbc);
        if (!$status) {
            $this->err_msg = "Failed closing connection to database.";
			return FALSE;
        }else{
            unset($this->dbc);
        }		
	}
  
 
	/**
	  * Run a NON select query on the db.
	  *
	  * @return boolean
	  * @param string $query
	  * @access public
	  */
	function query($query) {
 
 		$this->last_query = $query; 
		$this->query_time = 0;
		$timer = $this->getTime();

		// Run a query and return a fail/success flag. Set $err_msg if there was a problem.
 		$result = mysqli_query($this->dbc, $query );

 		$this->query_time = $this->getTime() - $timer;

		// ERROR CHECKING		
		if($result===FALSE){
			$this->err_msg = mysqli_error($this->dbc);
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
 
 		$this->last_query = $query; 
		$this->query_time = 0;
		$timer = $this->getTime();

        $result = mysqli_query($this->dbc, $query );


 		$this->query_time = $this->getTime() - $timer;

		// ERROR CHECKING		
		if($result===FALSE || $result == NULL){
			$this->err_msg = mysqli_error($this->dbc);
			return FALSE;		
		}
 		if( $result->num_rows==0 ){
			return FALSE;
		}

		// Parse into an array
		$resArray = array();
		while ($row = $result->fetch_assoc()) {
			$resArray[] = $row;
		}		
 
		return $resArray;		
	}
  
 
	/**
	  * Run a single row select query on the db.
      * Error or no result will return FALSE.
	  *
	  * @return array
	  * @param string $query
	  * @access public
	  */
	function getRow($query) {
 
		// Run a query and return the results. Set $err_msg if there was a problem.
 
 		$this->last_query = $query; 
		$this->query_time = 0;
		$timer = $this->getTime();

        $result = mysqli_query($this->dbc, $query);
 
 		$this->query_time = $this->getTime() - $timer;

		// ERROR CHECKING		
		if($result===FALSE || $result == NULL){
			$this->err_msg = mysqli_error($this->dbc);
			return FALSE;		
		}        
        if( $result->num_rows==0 ){
			return FALSE;
		}
 
 		return $result->fetch_assoc();
}
 
 
	/**
	  * Quickly grab one value from the db based on table name and key value.
	  * Error will return FALSE.
      * No result will return NULL.
	  *
	  * @return string
	  * @param string $tblname
	  * @param int $key_value
	  * @param string $col_name
	  * @param string $key_name
	  * @access public
	  */
	function getValue($tblname, $key_value, $col_name, $key_name="id"){			
 
		// ** Important: You must escape $key_value BEFORE calling this function. **
 
		$query = "select ".$col_name." from ".$tblname." WHERE ".$key_name."='".$key_value."' LIMIT 1";
 
 		$this->last_query = $query;
 		$this->query_time = 0;
		$timer = $this->getTime();

        $result = mysqli_query($this->dbc, $query);		
 
 		$this->query_time = $this->getTime() - $timer;

		// ERROR CHECKING		
		if($result===FALSE || $result == NULL){
			$this->err_msg = mysqli_error($this->dbc);
			return FALSE;		
		}        
        if( $result->num_rows==0 ){
			return NULL;
		}
        
        $result_array = $result->fetch_assoc();
        
 		return ( $result_array[$col_name]);		
	}	
 
 
	/**
	  * Return the id of the last inserted row or FALSE on fail.
	  *
	  * @return int
	  * @access public
	  */
	function insertedId() {	
		return( mysqli_insert_id($this->dbc));    
	}
 
 
 
	/**
	  * Return the number of affected rows.
	  *
	  * @return int
	  * @access public
	  */
	function affectedRows() {
		return( mysqli_affected_rows($this->dbc));
	}
 
 
	/**
	  * Escape the given string for this db connection. Second parameter tells it whether to stripslashes.
	  *
	  * @return string
	  * @access public
	  */
	function escapeStr($inStr, $isGPC=FALSE) {
		if($isGPC){
			if(get_magic_quotes_gpc()){
				$inStr = stripslashes($inStr);
			}
		}
        return mysqli_real_escape_string($this->dbc, $inStr);
	}
 
	private function getTime(){
        	$microtime = explode(' ', microtime());
        	return $microtime[1] . substr($microtime[0], 1);
	}
 
} // End Class DB.
?>