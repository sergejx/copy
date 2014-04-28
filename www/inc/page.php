<?php
function page_header($title) {
      global $gallery_dir,$root, $snimek, $galerie, $ThisScript, $theme;

			header("Content-Type: text/html; charset=utf-8");// make sure we send in utf8
																											 // and override Apache

																											 // For some reason text/xml is
																											 // causing trouble with stylesheets
			echo "<?xml version=\"1.0\"?>\n";
			/*
			echo "<?xml-stylesheet type=\"text/css\" media=\"screen\""; // doesn't work yet :/
			echo " href=\"inc/style/dark/dark.css\" ?>\n";
			*/
      echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n";
      echo "   \"http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-strict.dtd\">\n";
      echo "<html>\n";
      echo "<head>\n";

        echo "<meta name=\"robots\" content=\"noindex, nofollow\">\n";

			#IE hacks
			echo "<!-- This makes IE6 suck less (a bit) -->\n";
			echo "<!--[if lt IE 7]>\n";
			echo "<link rel=\"stylesheet\" type=\"text/css\" ";
			echo "href=\"inc/styles/ie6workarounds.css\" />\n";
			echo "<script src=\"inc/styles/ie7/ie7-standard.js\" type=\"text/javascript\">\n";
			echo "</script>\n";
			echo "<![endif]-->\n";

      echo "   <title>$title</title>\n";
			echo "<link rel=\"icon\" href=\"stock_camera-16.png\" ";
			echo "type=\"image/png\" />\n";
			echo "<link rel=\"shortcut icon\" href=\"favicon.ico\" ";
			echo "type=\"image/x-icon\" />\n";
      # mozilla style links
		if ($snimek && $galerie) {
			#Top
			echo "   <link rel=\"Top\"      href=\"$ThisScript\" />\n";
			#First
			#Prev
			$predchozi = $snimek - 1;
			$dalsi = $snimek + 1;
   		if ($snimek > 1) {
				echo "   <link rel=\"First\" ";
				echo "   href=\"$ThisScript?galerie=$galerie&amp;photo=1\" />\n";
				echo "   <link rel=\"Previous\" ";
				echo "href=\"$ThisScript?galerie=$galerie&amp;photo=$predchozi\" />\n";
			}
			#Next
			if (is_file("$gallery_dir/$galerie/lq/img-$dalsi.jpg")) {
				echo "   <link rel=\"Next\" ";
				echo "    href=\"$ThisScript?galerie=$galerie&amp;photo=$dalsi\" />\n";
			}
			#Last
			$adr = opendir("$gallery_dir/$galerie/thumbs/");
			$i = -2;
			while ($file = readdir($adr)) {
				$i++;
			}
			if ($i!=$snimek) {
				echo "   <link rel=\"Last\" ";
				echo "    href=\"$ThisScript?galerie=$galerie&amp;photo=$i\" />\n";
			}
		}
      
         echo "<link type=\"text/css\" rel=\"stylesheet\"";
         echo " href=\"$theme\"";
         echo " media=\"screen\" />\n";
     
      //require("javascript.inc.php");
			echo "<script src=\"inc/global.js\" ";
			echo "type=\"text/javascript\"></script>\n";
      echo "</head>\n\n";
      echo "<body>\n";
}


function page_footer() {
      echo "</body>\n";
      echo "</html>\n";
}


function page_navigation ($gallery, $snapshot, $image) {
   global $gallery_dir, $root, $ThisScript, $textnav, $img, 
          $show_thumbs, $exif_style, $PNthumbScale;

   $next = $snapshot + 1;
   $prev = $snapshot - 1;

   if (!$image) { // this will render a navigation bar - max 3 buttons
      echo "\n<div class=\"navbuttons\">\n";
      echo "<div class=\"navbuttonsshell\">\n";
      if ($snapshot > 1) { //previous 
         echo "<a id=\"previcon\" href=\"$ThisScript?galerie=$gallery&amp;photo=$prev";
         echo "&amp;exif_style=$exif_style&amp;show_thumbs=$show_thumbs\"";
				 echo " accesskey=\"p\">";
         echo "&lt; <span class=\"accesskey\">P</span>revious</a>\n";
      }
      echo "&nbsp;";
      if (is_file("$gallery_dir/$gallery/lq/img-$next.jpg")) { //next
         echo "<a id=\"nexticon\" href=\"$ThisScript?galerie=$gallery&amp;photo=$next";
         echo "&amp;exif_style=$exif_style&amp;show_thumbs=$show_thumbs\"";
				 echo " accesskey=\"n\">";
         echo "<span class=\"accesskey\">N</span>ext &gt;</a>\n";
      }
      echo "</div>\n</div>\n";
   } elseif ($image=="prev") { //previous thumbnail
      if ($snapshot > 1) { //previous 
         echo "<div class=\"prevthumb\">";
         echo "<a href=\"$ThisScript?galerie=$gallery&amp;photo=$prev";
         echo "&amp;exif_style=$exif_style&amp;show_thumbs=$show_thumbs\">";
         /*if (file_exists("$gallery_dir/$gallery/thumbs/img-$prev.png")) {
            $Pthumb = "$gallery_dir/$gallery/thumbs/img-$prev.png";
         } else {
            $Pthumb = "$gallery_dir/$gallery/thumbs/img-$prev.jpg";
         }
         $v = getimagesize("$root/$Pthumb");
         echo "<img alt=\"Previous\" src=\"";
         echo $Pthumb . "\" width=\"" . round($v[0]/$PNthumbScale);
         echo "\" height=\"" . round($v[1]/$PNthumbScale) . "\" />";
         echo "<br />" . __('Previous');*/
         echo "</a></div>\n";
      }
   } else { //next thumbnail
      if (is_file("$gallery_dir/$gallery/lq/img-$next.jpg")) {
         echo "<div class=\"nextthumb\">";
         echo "<a href=\"$ThisScript?galerie=$gallery&amp;photo=$next";
         echo "&amp;exif_style=$exif_style&amp;show_thumbs=$show_thumbs\">";
         /*if (file_exists("$gallery_dir/$gallery/thumbs/img-$next.png")) {
            $Nthumb = "$gallery_dir/$gallery/thumbs/img-$next.png";
         } else {
            $Nthumb = "$gallery_dir/$gallery/thumbs/img-$next.jpg";
         }
         $v = getimagesize("$root/$Nthumb");
         echo "<img alt=\"Next\" src=\"";
         echo $Nthumb . "\" width=\"" . round($v[0]/$PNthumbScale);
         echo "\" height=\"" . round($v[1]/$PNthumbScale) . "\" />";
         //echo "<br /><span class=\"accesskey\">N</span>ext";
         echo "<br />" . __('Next') ;	 */
         echo "</a></div>\n";
      }
   }
}

# return dirs sorted
class SortDir {
   var $items;

   function SortDir($directory) {
      $handle=@opendir($directory);
			if (!$handle) return;
      while ($file = readdir($handle)) {
         if ($file != "." && $file != "..") {
            $this->items[]=$file;
         }
      }
      closedir($handle);
	    if ($this->items) {
         natsort($this->items);
	    }
   }

   function Read() {
			if ($this->items) {
				$getback= (pos($this->items));
				next($this->items);
				return $getback;
			}
   }
}

?>
