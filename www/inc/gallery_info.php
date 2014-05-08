<?php
function sorted_directory($directory) {
    $items = array();
    $handle=@opendir($directory);
    if (!$handle) return;
    while ($file = readdir($handle)) {
        if ($file != "." && $file != "..") {
            $items[]=$file;
        }
    }
    closedir($handle);
    sort($items, SORT_NATURAL);
    return $items;
}


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
        $infofile = "{$this->path}/info.yaml";
        if (!file_exists($infofile))
            throw new DomainException(__("No such gallery"));
        //read from info.txt
        try {
            list($info_array, $photos) = $this->parse_info_file($infofile);
            if ($info_array["date"]) {
                $tstamp = strtotime($info_array["date"]);
            } else {
                $tstamp = filemtime($this->path); // Get from filesystem
            }
            $this->year = date("Y", $tstamp);
            $this->month = date("m", $tstamp);
            $this->day = date("d", $tstamp);

            if (@$info_array["description"]) {
                $this->desc = $info_array["description"];
            }

            if (@$info_array["author"]) {
                $this->author = $info_array["author"];
            }

            if (@$info_array["name"]) {
                $this->name = $info_array["name"];
            }

            if (@$info_array["restricted_user"]) {
                $this->login = $info_array["restricted_user"];
                $this->pw = $info_array["restricted_password"];
            }
            $num = 0;
            foreach ($photos as $img => $caption) {
                $num++;
                $this->photos[$num] = new Photo($this, $num, $img, $caption);
            }
        } catch (InfoFormatException $e) {
            throw new DomainException("Corrupted gallery", 0, $e);
        }
    }
    
    /* Info-file parser.
     *
     * File format is based on YAML and may be parseble by standard YAML parser.
     * File must contain two YAML documents. The first one contains a associative
     * array with gallery informations. The second document contains associative
     * array with file names of photos as keys and their capitons as values.
     */
    private function parse_info_file($filename) {
        $lines = file($filename);
        $gallery = array();
        $photos = array();
        // States: 0 - before gallery info, 1 - gallery info, 2 - photos
        $state = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) == 0)
                continue;
            if ($line[0] == '#')
                continue; // Comment
            if ($line == '---') {
                $state++;
                if ($state > 2)
                    break; // Don't read more then 2 documents
                continue;
            }
            if ($state == 0)
                throw new InfoFormatException("Missing document start symbol (---)");
            
            list($key, $value) = explode(':', $line, 2);
            $key = rtrim($key);
            $value = ltrim($value);
            
            if ($state == 1) {
                $gallery[$key] = $value;
            } elseif ($state == 2) {
                $photos[$key] = explode('|', $value);
            } else {
                throw new InfoFormatException("Unexpected error :-(");
            }
            
        }
        return array($gallery, $photos);
    }

    private function read_photos() {
        $path = "{$this->path}/thumbs";
        $imgfiles = sorted_directory($path);
        $this->photos = array();
        foreach ($imgfiles as $i => $filename) {
            $number = $i+1;
            $this->photos[$number] = new Photo($this, $filename, $number);
        }
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

    function __construct($gallery, $number, $file, $caption) {
        $this->file = $file;
        $this->number = $number;
        $this->gallery = $gallery;
        $this->url = "{$gallery->url}&amp;photo=$number";
        //preview
        $this->preview = "{$gallery->path}/mq/" . $this->file;
        if (!file_exists($this->preview))
            throw new DomainException(__('No such image'));
        $this->thumbnail = "{$gallery->path}/thumbs/" . $this->file;
        $this->previewsize = getimagesize($this->preview);
        //MQ
        if (file_exists("{$gallery->path}/mq/" . $this->file)) {
            $this->mq = "{$gallery->path}/mq/" . $this->file;
        }
        //HQ
        if (file_exists("{$gallery->path}/hq/" . $this->file)) {
            $this->hq = "{$gallery->path}/hq/" . $this->file;
        }

        $this->name = $caption[0];
        if (isset($caption[1]))
            $this->caption = $caption[1];
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
        return $this->number < count($this->gallery->photos);
    }

    function get_prev() {
        return $this->gallery->get_photo($this->number - 1);
    }

    function get_next() {
        return $this->gallery->get_photo($this->number + 1);
    }
}


class InfoFormatException extends Exception {
}
