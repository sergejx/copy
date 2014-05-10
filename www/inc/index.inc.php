<?php
#############################
#   Overall Gallery Index    #
#############################
function render_index($galleries) {
    global $yearsince, $sortinmonth;
    # finish off navigation bar
    print "</div>\n\n<!-- listing galleries-->\n\n";

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
        print "      <p><a ";
        if (@$galleries[$foldername]->name) {
            print " href=\"{$galleries[$foldername]->url}\">";
            print $galleries[$foldername]->name;
            print "</a>";
        } else {
            print " href=\"{$galleries[$foldername]->url}\">$foldername</a>";
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
