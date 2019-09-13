<?php
/*
************************************************************************************
AV Book Library - v 1.3.2 - A PHP web database for a book collection.
Copyright (C) 2013  Sherri Wheeler
Contact Info: avdeveloper at users.sourceforge.net
License Info: license.txt
Project Info: http://sourceforge.net/projects/avbooklibrary/

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

************************************************************************************
*/


define("VERSION", "1.3.2"); // Current version of AV Book Library.


// ******* Configuration data for the library installation ********

date_default_timezone_set('America/Toronto');
 
// Paths
define("LIBRARY_DIR", "/home/user/htdocs/library/");	// Server path to folder that the library is in.
define("LIBRARY_WEB", "http://www.example.com/library/");	// Root url to the library.

// Where to save the cached cover images to (make these folders writable by the web server).
define("COVERS_SM", LIBRARY_DIR."images/covers/sm/");
define("COVERS_LRG", LIBRARY_DIR."images/covers/lrg/");

// Website title of the library:
define("LIBRARY_TITLE", "My Library");

// Turn on/off Amazon Web Service Info:
define("AMAZON_ENABLED", false);
define("AMAZON_ACCESS_KEY", "XXXXXXXXXXX");   // Get your own access key from: http://aws.amazon.com
define("AMAZON_SECRET_KEY", "XXXXXXXXXXXXXXXXXXXXX");   // Get your own secret key from: http://aws.amazon.com
define("AMAZON_ASSOCIATE_TAG", "avbooklibrary-20");   // Retrieve an Associate Tag from Amazon Associates, or use this one which will support this project.


// Set to true, to add META tags to the page headers to ask search engines not to index the site.
define("BAN_SEARCH_ROBOTS", true);


// ** Search Pagination Settings: ***

// The number of search results to display on each page.
define("RESULTS_PER_PAGE", 20);

// The number of direct page links on either side of the current page in the pagination. Works best as an odd number.
define("PAGE_JUMPS", 5);



// ** Admin Login **
define("ADMIN_USERNAME", "username");
define("ADMIN_PASSWORD", "5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8"); // Sha1() hash of a password. Reset this to your own choice. (ex: http://www.zappersoftware.com/Help/md5.php)


// **** Make connection to the database/configure adodb settings. ****

// Database connection settings:
$db_driver		= "mysql";
$db_name 		= "dbname";
$db_host		= "localhost";
$db_port		= NULL;
$db_socket		= NULL;
$db_user		= "dbuser";
$db_pass		= "dbpassword";

// Instantiate db access object.

require(LIBRARY_DIR."lib/db.php"); 
$dba = new DB();
$dba->set_db_settings($db_name, $db_user, $db_pass, $db_host, $db_port, $db_socket);
$dba->connect();


// **** Include library functions: ****

require(LIBRARY_DIR."lib/functions.php");

?>