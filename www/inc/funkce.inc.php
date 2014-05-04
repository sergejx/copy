<?php
function access_check($login, $password,$realm) {
   if (!($_SERVER['PHP_AUTH_USER']=="$login" && $_SERVER['PHP_AUTH_PW']=="$password")) {
      header("WWW-authenticate: Basic Realm=$realm");
      Header("HTTP/1.0 401 Unauthorized");
			$err = new C_www;
      $err->header("Access Denied");
			echo "<div class=\"error\">\n";
			echo "<h1>Access Denied</h1>\n";
			echo "<p>Sorry, this gallery is restricted</p>\n";
			echo "<p><a href=\"index.php\">Return to index</a></p>\n";
			echo "</div>\n";
			$err->footer();
      exit;
   }

}
