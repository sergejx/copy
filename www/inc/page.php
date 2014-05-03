<?php
function page_header($title) {
    global $gallery_dir, $snimek, $galerie, $ThisScript, $theme;

    header("Content-Type: text/html; charset=utf-8");// make sure we send in utf8
?>
<!DOCTYPE html>
<html>
<head>

<meta name="robots" content="noindex, nofollow">

<!-- This makes IE6 suck less (a bit) -->
<!--[if lt IE 7]>
<link rel="stylesheet" type="text/css" href="inc/styles/ie6workarounds.css">
<script src="inc/styles/ie7/ie7-standard.js" type="text/javascript">
</script>
<![endif]-->

<title><?php echo $title; ?></title>

<link rel="icon" href="stock_camera-16.png" type="image/png">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<?php if ($snimek && $galerie) sequence_links($galerie, $snimek); ?>
<link type="text/css" rel="stylesheet" href="<?php echo $theme; ?>" media="screen">
     
<script src="inc/global.js" type="text/javascript"></script>
</head>
<body>
<h1 class="title"><a href="<?php echo $ThisScript; ?>">Photo Gallery<span></span></a></h1>
<?php
}


function page_footer() {
    global $copy, $app;
?>
<div class="footer">
    <?php echo $copy; ?><br>
    <?php echo __('Generated by'); ?>
    <em><a href="<?php echo $app["url"]; ?>"><?php echo $app["name"]; ?></a>
    ver. <?php echo $app["version"]; ?></em>
</div>
</body>
</html>
<?php
}


function sequence_links($gallery, $snapshot) {
    global $gallery_dir, $ThisScript;
    $prev = $snapshot - 1;
    $next = $snapshot + 1;
    if ($snapshot > 1) {
        echo "<link rel=\"prev\" ";
        echo "href=\"$ThisScript?galerie=$gallery&amp;photo=$prev\">\n";
    }
    if (is_file("$gallery_dir/$gallery/lq/img-$next.jpg")) {
        // Prefetch next page and image
        echo "<link rel=\"next prefetch\" ";
        echo "href=\"$ThisScript?galerie=$gallery&amp;photo=$next\">\n";
        echo "<link rel=\"prefetch\" ";
        echo "href=\"${ThisScript}galleries/$gallery/mq/img-$next.jpg\">\n";
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
