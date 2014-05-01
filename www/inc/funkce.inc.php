<?php

function check($file) {
   global $gallery_dir, $page;
   
#   if (eregi("[^0-9a-z\_\-\ ]",$file) || !file_exists("$gallery_dir/$file")) {
#   if (eregi("CVS",$file) || !file_exists("$gallery_dir/$file")) {
   if (!file_exists("$gallery_dir/$file")) {
      echo "funkce.inc.php/check(): Bad input";
      $page->footer();
      exit;
   }
}

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

function get_photo_title($galerie, $id) {
  global $gallery_dir;
  if ($title = @file_get_contents("$gallery_dir/$galerie/comments/${id}.txt")) {
    $title = trim(preg_replace('/[\s\n\r]+/', ' ', strip_tags($title)));
    if (strlen($title) > 80)
      $title = trim(substr($title, 0, 77)) . "...";
  } else 
    $title = "Photo ${id}";
  return $title;
}

?>
