<?php
function page_header($title, $photo=null) {
    global $ThisScript, $theme;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<meta name="robots" content="noindex, nofollow">

<title><?php echo $title; ?></title>

<link rel="icon" href="stock_camera-16.png" type="image/png">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<?php if ($photo) sequence_links($photo); ?>
<link type="text/css" rel="stylesheet" href="<?php echo $theme; ?>" media="screen">
<link rel="stylesheet" href="lib/justifiedGallery.min.css">

<script src="lib/jquery.min.js"></script>
<script src="lib/jquery.justifiedGallery.min.js"></script>
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
    <em><a href="<?php echo APP_URL; ?>"><?php echo APP_NAME; ?></a>
    ver. <?php echo APP_VERSION; ?></em>
</div>
</body>
</html>
<?php
}


function sequence_links($photo) {
    if ($photo->has_prev()) {
        echo "<link rel=\"prev\" href=\"{$photo->get_prev()->url}\">\n";
    }
    if ($photo->has_next()) {
        // Prefetch next page and image
        echo "<link rel=\"next prefetch\" href=\"{$photo->get_next()->url}\">\n";
        echo "<link rel=\"prefetch\" href=\"{$photo->get_next()->preview}\">\n";
    }
}
