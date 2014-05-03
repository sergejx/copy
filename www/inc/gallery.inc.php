<?php
##############################
#  Individual Gallery Index  #
##############################
function render_gallery($galerie) {
    global $ThisScript, $root, $gallery_dir, $galleries;
    # finish off navigation header

    print "\n &gt; ";
    if ($galleries[$galerie]->name) {
        print $galleries[$galerie]->name;
    } else {
        print $galerie;
    }
    print "</div>\n\n";

    //thumbnails
    print "<p class=\"bigthumbnails\">\n";
    $path = "$gallery_dir/$galerie/thumbs";
    $imgfiles = new SortDir($path);
    check($galerie); // check for nasty input
    while ($file = $imgfiles->read()) {
        if (is_file("$path/$file") && eregi("^img-([0-9]+)\.(png|jpe?g)", $file, $x)) {
            $thumb = "$gallery_dir/$galerie/thumbs/img-${x[1]}.${x[2]}";
            $imgsize = getimagesize("$root/$thumb");
            //check for portraits
            $portrait = "false";
            $class = "";
            if($imgsize[0]<100) {
                //portraits need a special class for styling
                $class = "portrait";
            }
            if (file_exists("$gallery_dir/$galerie/comments/${x[1]}.txt") &&
                $title = file_get_contents("$gallery_dir/$galerie/comments/${x[1]}.txt")) {
                $title = ereg_replace("(\"|\')","",trim(strip_tags($title)));
                $title = ereg_replace("(.{77}).*","\\1",$title);
            } else
                $title = "Photo ${x[1]}";

            print "   <a href=\"$ThisScript?galerie=$galerie&amp;photo=${x[1]}\"";
            print " title=\"$title\"";
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
            print "alt=\"photo No. ${x[1]}\" />";
            print "</a>\n";
        }
    }
    print "</p>\n";

    //info
    print "<div id=\"info\">\n";
    if ($galleries[$galerie]->desc) {
        print "<p>";
        print "<span class=\"value\">";
        print $galleries[$galerie]->desc . "</span></p>\n";
    }
    if ($galleries[$galerie]->author) {
        print "<p><span class=\"key\">Author: </span>";
        print "<span class=\"value\">";
        print $galleries[$galerie]->author . "</span></p>\n";
    }
    print "</div>\n";

    //and links to archived images:
    print "\n<p class=\"archives\">\n";
    if (file_exists("$gallery_dir/$galerie/zip/mq.zip")) {
        print "[ <a href=\"$gallery_dir/$galerie/zip/mq.zip\">" . __('zipped MQ images') . "</a> ] ";
    }
    if (file_exists("$gallery_dir/$galerie/zip/mq.tar.bz2")) {
        print "[ <a href=\"$gallery_dir/$galerie/zip/mq.tar.bz2\">" . __('MQ images tarball') . "</a> ] ";
    }
    if (file_exists("$gallery_dir/$galerie/zip/hq.zip")) {
        print "[ <a href=\"$gallery_dir/$galerie/zip/hq.zip\">" . __('zipped HQ images') . "</a> ]";
    }
    if (file_exists("$gallery_dir/$galerie/zip/hq.tar.bz2")) {
        print "[ <a href=\"$gallery_dir/$galerie/zip/hq.tar.bz2\">" . __('HQ images tarball') . "</a> ]";
    }
    print "</p>";
}

