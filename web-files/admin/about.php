<?php
require('../config.php');
require(LIBRARY_DIR."/lib/functions_admin.php");

session_start();

if(!is_logged_in()){
	header("Location: ".LIBRARY_WEB."admin/index.php");
	exit();
}

require(LIBRARY_DIR."/templates/header.php");

?>

<h3>About AV Book Library</h3>

<p>Current Version: <?=VERSION;?>.</p>

<p>AV Book Library is a very simple online book library database application. It allows you to store your book information under categories (like: Fiction, Health, etc) along with the key book details like title, author, publisher, ISBN, pages, whether you have read it or not, whether you've loaned it to someone, a description of the book, etc. The program will cache cover images for you, as well as provides functionality for getting additional book information from Amazon Web Services (tm).</p>

<p>This application was created by Sherri Wheeler. It is licensed under the <b>GPLv3</b> license. </p>

<p>The official project website can be found at: <a href="http://syntaxseed.com/project/avbooklibrary/">syntaxseed.com/project/avbooklibrary</a>.</p>

<p>You can support this project and enable active development by <a href="http://syntaxseed.com/about/donate/">Donating to AV Book Library</a>.</p>



<?php
require(LIBRARY_DIR."/templates/footer.php");
?>