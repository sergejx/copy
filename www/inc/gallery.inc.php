<?php
##############################
#  Individual Gallery Index  #
##############################
function render_gallery($gallery) {
    global $gallery_dir;
    # finish off navigation header
    print "\n &gt; ";
    if ($gallery->name) {
        print $gallery->name;
    } else {
        print $gallery->id;
    }
    print "</div>\n\n";

    //thumbnails
    print "<p class=\"bigthumbnails\">\n";
    for ($num = 1; $num <= $gallery->get_photos_count(); $num++) {
        $photo = $gallery->get_photo($num);
        $thumb = $photo->thumbnail;
        $imgsize = getimagesize($thumb);
        //check for portraits
        $portrait = "false";
        $class = "";
        if($imgsize[0]<100) {
            //portraits need a special class for styling
            $class = "portrait";
        }

        print "   <a href=\"{$photo->url}\"";
        print " title=\"{$photo->name}\"";
        if ($class) print " class=\"$class\"";
        print ">";
        print "<img ";
        // scale portraits to 80 height
        if ($portrait) {
            //portrait
            print "width=\"";
            $scaled = round($imgsize[0] / 1.5);
            print $scaled;
            print "\" height=\"${imgsize[0]}\"";
        } else {
            //landscape
            print $imgsize[3];
        }
        print " src=\"$thumb\" ";
        print "alt=\"photo No. $num\" />";
        print "</a>\n";
    }
    print "</p>\n";

    //info
    print "<div id=\"info\">\n";
    if ($gallery->desc) {
        print "<p>";
        print "<span class=\"value\">";
        print $gallery->desc . "</span></p>\n";
    }
    if ($gallery->author) {
        print "<p><span class=\"key\">Author: </span>";
        print "<span class=\"value\">";
        print $gallery->author . "</span></p>\n";
    }
    print "</div>\n";
}

