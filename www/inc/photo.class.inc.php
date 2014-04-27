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
	var $counter;
	var $album;

	function C_photo($file, $number) {
		global $root, $gallery_dir, $galerie, $db;
		
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
		if ($GLOBALS['have_sqlite']) { //query just once
			require_once("$root/inc/db.class.inc.php");
			$sql = "select * from photo where ";
			$sql .= "number=" . $this->number . " and ";
			$sql .= "album='" . $this->album . "'";
			$db->query($sql);
		}
		$this->readCaption();
		$this->readCounter(); //reads access log number
		if ($GLOBALS['have_sqlite']) { //need to get photo id first
			if (!$db->count()) {//no record for this photo, let's update the record
				//FIXME - if no photo data in db, create a unique index for it
				//and add number, album, caption and views.
				$sql = "insert into photo (name, caption, counter, number, album)";
				$sql .= " values (";
				$sql .= "\"" . sqlite_escape_string($this->name) . "\", ";
				$sql .= "\"" . sqlite_escape_string(strtr($this->caption,"\"","'")) . "\", ";
				$sql .= $this->counter . ", ";
				$sql .= $this->number . ", ";
				$sql .= "\"" . $this->album . "\"";
				$sql .= ")";
				$db->query($sql);
				print "\n\n<!-- We've moved the data to the database.-->";
				//now we still need to query for the id
				$sql = "select id from photo where ";
				$sql .= "number=" . $this->number . " and ";
				$sql .= "album='" . $this->album . "'";
				$db->query($sql);
			}
			$db->rewind();
			$resultarray = sqlite_fetch_array($db->result);
			$this->id = $resultarray["id"];
			print "\n\n<!-- image id: " . $this->id . " -->\n";
		}
	}

	function readCaption() {
		global $have_sqlite, $root, $gallery_dir, $galerie, $db;
		
		/* reads name and caption of a photo
		   - either from sqlite database or filesystem
		 */
		 if ($have_sqlite) {
				//try reading from sqlite
				if ($db->count()) {
					$result = sqlite_fetch_array($db->result);
					$this->name = $result["name"];
					$this->caption = $result["caption"];
					return; //no need to fallback anymore
				}
		 } 
		 
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
	
	function readCounter() {
		global $log_access, $root, $gallery_dir, $galerie, $db;

		if ($GLOBALS['have_sqlite']) {
			//try reading from sqlite
			if ($db->count()) {
				$db->rewind();
				$result = sqlite_fetch_array($db->result);
				$this->counter = $result["counter"];
				return; //no need to fallback anymore
			}
		} 
		//we fallback to filesystem :/
		 if (is_writable("$root/$gallery_dir/$galerie/comments")) { // needs perms
			 $log = "$root/$gallery_dir/$galerie/comments/log_" . $this->number . ".txt";
			 if (file_exists($log)){
				 $fh = @fopen($log, "r");
				 $this->counter = rtrim(fgets($fh));
				 fclose($fh);
			 } else {
				 $this->counter = 0;
			 }
		 } else {
			 //doesn't do anything if no perms
			 print "<!-- ". __('WARNING: comment dir not writable') . "-->\n";
			 return 0; //failure
		 }
		 return 1; //success
	}

	function renderCounter() {
		
		 print "\n<div id=\"log\">\n";
		 print __('This image has been viewed') . " ";
		 print "<strong>" . $this->counter . "</strong>". " " . __('times') . ".";
		 print "</div>\n\n";
		 $this->writeCounter(); //save state

	}

	function writeCounter() {
		global $log_access, $root, $gallery_dir, $galerie, $page, $db;

		$this->counter++; //we add to counter
		if ($GLOBALS['have_sqlite']) {
			//we have SQLite
			$sql = "update photo set counter=" . $this->counter;
			$sql .= " where id=" . $this->id;
			$db->query($sql);
			return; //no need to fallback anymore
		} 
		 //fallback to filesystem
		 if (is_writable("$root/$gallery_dir/$galerie/comments")) { // needs perms
			 $log = "$root/$gallery_dir/$galerie/comments/log_". $this->number .".txt";
			 if (file_exists($log) && !is_writable($log)) {
				 print "\n\n\n<!-- cannot open $log. Check permissions.";
				 print "\nAborting counter write -->\n";
				 return 0;
			 }
			 $fh = fopen($log,"w");
			 if (!fwrite($fh, $this->counter . "\n")) {
					$page->error( __('Could not write to') . $log . "!");
					$page->footer();
					exit; //stop everything
			 }
			 fclose($fh);
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
	 print "\" alt=\"$snimek\" />\n";
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
