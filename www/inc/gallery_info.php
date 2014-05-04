<?php
class Gallery {
    var $id;
    var $year, $month, $day;
    var $desc, $author, $name;
    var $login, $pw;
    var $path;
    var $url;
    var $photos;

    function __construct($id) {
        global $ThisScript, $gallery_dir;
        $this->id = $id;
        $this->url = "$ThisScript?gallery=$id";
        $this->path = "$gallery_dir/$id";
        if (!file_exists($this->path))
            throw new DomainException(__("No such gallery"));
        $infofile = "{$this->path}/info.txt";
        if (file_exists($infofile)) {
            //read from info.txt
            $info_array = $this->infoParse($infofile);
            if ($info_array["date"]) {
                // try to be a little smarter about format
                if (ereg("([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})",
                    $info_array["date"])) {
                        // remain compatible - DD.MM.YYYY
                        list($day,$month,$year) = split("\.", $info_array["date"]);
                        $year = rtrim($year);
                        $month = rtrim($month);
                        $day = rtrim($day);
                        $info_array["date"] = "$year-$month-$day"; //make it US date
                    }
                // US date format at this point
                $tstamp = strtotime($info_array["date"]);
            } else {
                $tstamp = filemtime("$gallery_dir/$id");// Get from filesystem
            }
            $this->year = date("Y", $tstamp);
            $this->month = date("m", $tstamp);
            $this->day = date("d", $tstamp);

            if (@$info_array["description"]) {
                $this->desc = rtrim($info_array["description"]);
            }

            if (@$info_array["author"]) {
                $this->author = rtrim($info_array["author"]);
            }

            if (@$info_array["name"]) {
                $this->name = rtrim($info_array["name"]);
            }

            if (@$info_array["restricted_user"]) {
                $this->login = rtrim($info_array["restricted_user"]);
                $this->pw = rtrim($info_array["restricted_password"]);
            }
        } else { // Get Dates from modification stamp
            $mtime = filemtime("$gallery_dir/$id");
            $this->year = date("Y", $mtime);
            $this->month = date("m", $mtime); //F
            $this->day = date("d", $mtime);
        }
        // Read list of photos
        $path = "{$this->path}/thumbs";
        $imgfiles = new SortDir("$path");
        $this->photos = array();
        foreach ($imgfiles->items as $i => $filename) {
            $number = $i+1;
            $this->photos[$number] = new Photo($this, $filename, $number);
        }
    }
    
    function infoParse ($infofile) {
        $info_array = file($infofile);
        foreach ($info_array as $line) {
            list($key,$value) = split("\|",$line);
            $result[$key]=$value;
        }
        return $result;
    }
    
    function get_photo($number) {
        return $this->photos[$number];
    }
}

function cmp_galleries_by_day($g1, $g2) {
    return - strcmp($g1->day, $g2->day);
}

/* Photo class for dealing with individual images
*/

class Photo {
	var $id;
	var $preview;
	var $previewsize;
	var $mq;
	var $hq;
    var $thumbnail;
	var $name;
	var $caption;
	var $file;
	var $number;
    var $gallery;
    var $url;

    function __construct($gallery, $file, $number) {
		$this->file = $file;
		$this->number = $number;
        $this->gallery = $gallery;
        $this->url = "{$gallery->url}&amp;photo=$number";
		//init from filesystem
		//preview
        $this->preview = "{$gallery->path}/mq/img-" . $this->number . ".jpg";
        if (!file_exists($this->preview))
            throw new DomainException(__('No such image'));
        $this->thumbnail = "{$gallery->path}/thumbs/img-" . $this->number . ".jpg";
		$this->previewsize = getimagesize($this->preview);
		//MQ
        if (file_exists("{$gallery->path}/mq/img-" . $this->number . ".jpg")) {
            $this->mq = "{$gallery->path}/mq/img-" . $this->number . ".jpg";
		}
		//HQ
        if (file_exists("{$gallery->path}/hq/img-" . $this->number . ".jpg")) {
            $this->hq = "{$gallery->path}/hq/img-" . $this->number . ".jpg";
		}
		$this->readCaption();
	}

	function readCaption() {
		  $buffer = "";
        $captionfile = "{$this->gallery->path}/comments/" . $this->number . ".txt";
			$fh = @fopen($captionfile, "r");
			if ($fh) {
				 while (!feof($fh)) {
						 $buffer .= fgets($fh, 4096);
				 }
				 fclose($fh);
			} else { // no caption file
				$this->name = __("Photo ") . $this->number;
				return;
			}
			//parse buffer
			if(eregi("^<span>(.*)</span>( - )?(.*)", $buffer, $x)) {
				$this->name = $x[1]; //mostly "Photo"
				$this->caption = chop($x[3]);
			} else {
				$this->caption = $buffer;
			}
	}
	
	function renderBigSize() {

   if ($this->mq || $this->hq) {
		 print "<div id=\"mqhq\">";
		 if ($this->mq) {
				print "<a href=\"" . $this->mq . "\">". __('MQ') . "</a> ";
		 }
		 if ($this->hq) {
				print "<a href=\"" . $this->hq . "\">" . __('HQ') . "</a>";
		 }
		 print "</div>\n";
	 }
	}

	function renderPreview() {

   $divheight = $this->previewsize[1] + 10;
   print "<div id=\"image\" style=\"height: ${divheight}px\">\n"; // extra kludge 
                                                                 // because of tall 
                                                                 // images

        print "<img id=\"preview\" " . $this->previewsize[3] . " src=\"". $this->preview;
	 print "\" alt=\"$this->caption\" />\n";
	}

	function renderCaption() {
	
		print "<div class=\"comment\">";
		print "<span>" . $this->name . "</span>";
		if ($this->caption) {
			print " &ndash; ";
			print $this->caption;
			print "</div>";
		}
	}

    function has_prev() {
        return $this->number > 1;
    }

    function has_next() {
        $next = $this->number + 1;
        return is_file("{$this->gallery->path}/mq/img-$next.jpg");
    }

    function get_prev() {
        return $this->gallery->get_photo($this->number - 1);
    }

    function get_next() {
        return $this->gallery->get_photo($this->number + 1);
    }
}
?>
