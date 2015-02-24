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

        print "   <a href=\"{$photo->url}\"";
        print " title=\"{$photo->name}\"";
        print ">";
        print "<img ";
            print $imgsize[3];
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

