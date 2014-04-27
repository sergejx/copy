<?php
/* Photo class for dealing with individual images

*/

class C_photo {
	var $id;
	var $preview;
	var $previewsize;
	var $mq;
	var $hq;
	var $name;
	var $caption;
	var $file;
	var $number;
	var $album;

	function C_photo($file, $number) {
		global $root, $gallery_dir, $galerie;
		
		$this->file = $file;
		$this->number = $number;
		$this->album = $galerie;
		//init from filesystem
		//preview
		$this->preview = "$gallery_dir/$galerie/mq/img-" . $this->number . ".jpg";
		$this->previewsize = getimagesize($this->preview);
		//MQ
		if (file_exists("$root/$gallery_dir/$galerie/mq/img-" . $this->number . ".jpg")) {
			$this->mq = "$gallery_dir/$galerie/mq/img-" . $this->number . ".jpg";
		}
		//HQ
		if (file_exists("$root/$gallery_dir/$galerie/hq/img-" . $this->number . ".jpg")) {
			$this->hq = "$gallery_dir/$galerie/hq/img-" . $this->number . ".jpg";
		}
		$this->readCaption();
	}

	function readCaption() {
		global $root, $gallery_dir, $galerie;
		
		 //we falback to filesystem
		  $buffer = "";
			$captionfile = "$root/$gallery_dir/$galerie/comments/" . $this->number . ".txt";
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

   print "<img id=\"preview\" " . $this->previewsize[3] . " src=\"". $this->file;
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
}
?>
