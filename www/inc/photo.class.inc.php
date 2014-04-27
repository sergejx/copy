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
