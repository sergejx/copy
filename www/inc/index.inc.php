<?php
#############################
#   Overall Gallery Index    #
#############################
function render_index($adr) {
    global $root, $gallery_dir, $yearsince, $sortinmonth;
    # finish off navigation bar
    print "</div>\n\n<!-- listing galleries-->\n\n";
    # I've nuked date.txt to replace it with a more generic info.txt
    # It optionally supplies i18n name, camera model, author and date
    # TODO: imgconv script support
    while ($file = $adr->Read()) {
        // checking for inc is only really needed when gallery_dir == $root
        // hopefully not many galleries will be named inc ;)
        if (is_dir("$gallery_dir/$file") && !ereg("\.", $file) && $file!="inc") {
            // Use date file for gallery date if avaliable
            // info.txt format described in README
            $galleries[$file] = new Gallery($file);
        }
    }

    //sort within month depending on $sortinmonth
    if ($sortinmonth) { //alphabetically
        ksort($galleries);
    } else {//by date
        uasort($galleries, 'cmp_galleries_by_day');
    }
    reset($galleries);


    $thisyear = 0;
    if (!isset($yearto)) $yearto = date("Y");
    for ($i = $yearto; $i >= $yearsince; $i--) {
        for ($thismonth=12; $thismonth>0; $thismonth--) { // go year by year, month by month
                                                          // down
            foreach ($galleries as $foldername => $info) { //using $galerieday (for when sorted)
                if ($info->month == $thismonth && $info->year == $i) { //such Y/M exists

                    $galerieyearordered["$foldername"]=$info->year;
                    $galeriemonthordered["$foldername"]=$info->month;
                }
            }
        }
    }

    $months = array(__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'),
        __('September'), __('October'), __('November'), __('December'));
    $one_out = false;
    foreach ($galerieyearordered as $foldername => $year) {
        $one_out = true;
        if (@$thisyear!=$year) { #if the year is not equal to the current year
            #This is the first year
            if (@$thisyear) { print "   </div>\n</div>\n";}// end last year if this is
                                                           // not the first one
            #This is a new year
            unset($thismonth);
            print "<div class=\"year\"><h3>$year</h3>\n";
            print "";
        }
        $month=$galleries["$foldername"]->month;
        # now months
        if (@$thismonth!=$month) {
            #first one
            if (@$thismonth) { print "   </div>\n"; } // end of last month if
                                                      // this is not the first one
            #new month
            $monthindex = $month - 1;
            $monthname = $months[$monthindex];
            print "   <div class=\"month\"><h4>$monthname</h4>\n";
        }
        #galleries within month
        if ($galleries[$foldername]->login) {
            print "      <p class=\"restricted\"><a ";
        } else {
            print "      <p><a ";
        }
        if (@$galleries[$foldername]->name) {
            print " href=\"$ThisScript?galerie=$foldername\">";
            print $galleries[$foldername]->name;
            print "</a>";
        } else {
            print " href=\"$ThisScript?galerie=$foldername\">$foldername</a>";
        }
        if (@$galleries[$foldername]->desc) {
            print "<span class=\"desc\">" . $galleries[$foldername]->desc;
            print "</span>\n";
        }
        if (@$galleries[$foldername]->author) {
            print "<span class=\"author\">by&nbsp;" . $galleries[$foldername]->author;
            print "</span>\n";
        }
        if (@$galleries[$foldername]->day) {
            print "<span class=\"date\">";
            print "$monthname&nbsp;" . $galleries[$foldername]->day;
            print "</span>\n";
        }
        print "</p>\n";
        $thisyear=$year;
        $thismonth=$month;
    }
    if ($one_out) print ("   </div>\n</div>\n\n");
}
