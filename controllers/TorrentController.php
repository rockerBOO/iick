<?php

require(ROOT_PATH . 'classes/Bencoder.php');

class TorrentController extends Controller
{
    public $input = array();
    
    public function _list()
    {
        $torrents = loadMany('Torrent', array('_limit' => 20));
        
        $vars = array();
        $vars['torrents'] = array();
        
        foreach ($torrents as $torrent)
        {
            $vars['torrents'][] = $torrent;
        }
        
        $vars['user'] = new User;
        
        echo $this->l('Template')->load('torrent/list', 'base', $vars)->parse()->output();
    }
    
    public function details()
    {
        $torrent = load('Torrent', array('_id' => $this->url_matches['id']));
        
        if ($torrent->hasError())
        {
            die('404');
        }
        
        $vars = array(
            'torrent' => array(
                'id' => $torrent->get('_id'),
                'name' => $torrent->get('name'),
            ),
        );
        
        $vars['user'] = new User;
        
        echo $this->l('Template')->load('torrent/details', 'base', $vars)->parse()->output();
    }
    
    public function setup_announce_vars()
    {
        /* Array
        (
            [info_hash] => kA?{???O??U?R0?>?
            [peer_id] => -TR2040-ws7vexdrn341
            [port] => 53215
            [uploaded] => 0
            [downloaded] => 0
            [left] => 0
            [numwant] => 80
            [key] => iub29u6b
            [compact] => 1
            [supportcrypto] => 1
            [requirecrypto] => 1
            [event] => started
        ) */
        
        
        // String variables
        foreach (array("passkey","info_hash","peer_id","event","ip", "localip") as $x)
            if (isset($_GET[$x]))
                $this->input[$x] = (string)$_GET[$x];

        // Numeric variables
        foreach (array("port","downloaded","uploaded","left") as $x)
            if (isset($_GET[$x]))
                $this->input[$x] = (int)$_GET[$x];

        // Check these vars
        foreach (array("info_hash","peer_id") as $x)
            if (strlen($this->input[$x]) != 20) 
                Bencoder::err("Invalid $x (" . strlen($this->input[$x]) . " - " . urlencode($this->input[$x]) . ")");
                
        $this->input['rsize'] = 50;

        foreach (array("num want", "numwant", "num_want") as $k)
        {
        	if (isset($_GET[$k]))
        	{
        		$this->input['rsize'] = 0 + $_GET[$k];
        		break;
        	}
        }

        $this->input['agent'] = $_SERVER["HTTP_USER_AGENT"];
        
        error_log($_SERVER['REQUEST_URI'] . ' - ' . print_r($_GET, true), 3, '/tmp/announce_input.log');
    }
    
    public function upload()
    {
        if (isset($_POST['form_action']))
        {
        
            foreach (explode(":","descr:type:name") as $v) 
            {
            	if (!isset($_POST[$v]))
            		bark("missing form data");
            }

            if (!isset($_FILES["file"]))
            	bark("missing form data");

            $f = $_FILES["file"];
            $fname = unesc($f["name"]);
            if (empty($fname))
            	bark("Empty filename!");

            $nfofile = $_FILES['nfo'];
            if ($nfofile['name'] == '')
              bark("No NFO!");

            if ($nfofile['size'] == 0)
              bark("0-byte NFO");

            if ($nfofile['size'] > 65535)
              bark("NFO is too big! Max 65,535 bytes.");

            $nfofilename = $nfofile['tmp_name'];

            if (@!is_uploaded_file($nfofilename))
              bark("NFO upload failed");

            $descr = unesc($_POST["descr"]);
            if (!$descr)
              bark("You must enter a description!");

            $catid = (0 + $_POST["type"]);
            if (!is_valid_id($catid))
            	bark("You must select a category to put the torrent in!");

            if (!validfilename($fname))
            	bark("Invalid filename!");
            if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
            	bark("Invalid filename (not a .torrent).");
            $shortfname = $torrent = $matches[1];
            if (!empty($_POST["name"]))
            	$torrent = unesc($_POST["name"]);

            $tmpname = $f["tmp_name"];
            if (!is_uploaded_file($tmpname))
            	bark("eek");
            if (!filesize($tmpname))
            	bark("Empty file!");

            $dict = bdec_file($tmpname, $max_torrent_size);
            if (!isset($dict))
            	bark("What the hell did you upload? This is not a bencoded file!");





            list($ann, $info) = dict_check($dict, "announce(string):info");
            list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");

            if (!in_array($ann, $announce_urls, 1))
            	bark("invalid announce url! must be <b>" . $announce_urls[0] . "</b>");

            if (strlen($pieces) % 20 != 0)
            	bark("invalid pieces");

            $filelist = array();
            $totallen = Bencoder::dict_get($info, "length", "integer");
            if (isset($totallen)) {
            	$filelist[] = array($dname, $totallen);
            	$type = "single";
            }
            else {
            	$flist = Bencoder::dict_get($info, "files", "list");
            	if (!isset($flist))
            		bark("missing both length and files");
            	if (!count($flist))
            		bark("no files");
            	$totallen = 0;
            	foreach ($flist as $fn) {
            		list($ll, $ff) = Bencoder::dict_check($fn, "length(integer):path(list)");
            		$totallen += $ll;
            		$ffa = array();
            		foreach ($ff as $ffe) {
            			if ($ffe["type"] != "string")
            				bark("filename error");
            			$ffa[] = $ffe["value"];
            		}
            		if (!count($ffa))
            			bark("filename error");
            		$ffe = implode("/", $ffa);
            		$filelist[] = array($ffe, $ll);
            	}
            	$type = "multi";
            }

            $infohash = pack("H*", sha1($info["string"]));


            // Replace punctuation characters with spaces

            $torrent = str_replace("_", " ", $torrent);
            
            $id = add('Torrent', array(
                'filename' => $fname, 
                'owner' => $owner, 
                'visible' => 'no', 
                'info_hash' => $infohash, 
                'name' => $torrent,
                'size' => ,
                'numfiles' => ,
                'type' => ,
                'descr' => ,
                'added' => ,
                'last_action' => new MongoDate(),
                )
            );
            
            foreach ($filelist as $file)
            {
                add('File', array('torrent' => $id, 'filename' => $file[0], 'size' => $file[1]));
            }

            move_uploaded_file($tmpname, TORRENT_PATH . '/' . $id . '.torrent');
        }
        
    }
    
    public function announce()
    {
        $this->setup_announce_vars();
        
        if (
            preg_match("#^Mozilla\\/#", $this->input['agent']) || 
            preg_match("#^Opera\\/#", $this->input['agent']) || 
            preg_match("#^Links #", $this->input['agent']) || 
            preg_match("#^Lynx\\/#", $this->input['agent'])
        )
        {
            // Bencoder::err("torrent not registered with this tracker");
        }

        $user = load('User', array('passkey' => $this->input['passkey']));
        
        if ($user->hasError())
        {
            Bencoder::err("Invalid passkey! Re-download the .torrent from " . SITE_DOMAIN);
        }
        
        $torrent = load('Torrent', array('info_hash' => $this->input['info_hash']));
        
        if ($torrent->hasError())
        {
            Bencoder::err("Torrent not registered with this tracker");
        }
        
        $peers = loadMany('Peer', array('torrent_id' => $torrentId, 'is_connectable' => 'yes', '_limit' => $this->input['rsize']));
        
        // $peers
        
        $resp = "d" . Bencoder::benc_str("interval") . "i" . $announce_interval . "e" . Bencoder::benc_str("peers") . "l";

        while ($peers)
        {
        	$row["peer_id"] = hash_pad($row["peer_id"]);

        	if ($row["peer_id"] === $peer_id)
        	{
        		$userid = $row["userid"];
        		$self = $row;
        		continue;
        	}

        	$resp .= "d" .
        		Bencoder::benc_str("ip") . Bencoder::benc_str($row["ip"]) .
        		Bencoder::benc_str("peer id") . Bencoder::benc_str($row["peer_id"]) .
        		Bencoder::benc_str("port") . "i" . $row["port"] . "e" .
        		"e";
        }

        $resp .= "ee";
        
        // foreach (array("passkey","info_hash","peer_id","event","ip", "localip") as $x)
        // {
        //     $input[
        //     
        //     if (isset($_GET["$x"]))
        //         $GLOBALS[$x] = "" . $_GET[$x];
        // }
        // 
        // foreach (array("port","downloaded","uploaded","left") as $x)
        //     $GLOBALS[$x] = 0 + $_GET[$x];
        // 
        // if (strpos($passkey, "?")) 
        // {
        //  $tmp = substr($passkey, strpos($passkey, "?"));
        //  $passkey = substr($passkey, 0, strpos($passkey, "?"));
        //  $tmpname = substr($tmp, 1, strpos($tmp, "=")-1);
        //  $tmpvalue = substr($tmp, strpos($tmp, "=")+1);
        //  $GLOBALS[$tmpname] = $tmpvalue;
        // }
        // 
        // 
        // 
        // foreach (array("passkey","info_hash","peer_id","port","downloaded","uploaded","left") as $x)
        //     if (!isset($x)) err("Missing key: $x");
        // 
        // foreach (array("info_hash","peer_id") as $x)
        // 
        // if (strlen($GLOBALS[$x]) != 20) err("Invalid $x (" . strlen($GLOBALS[$x]) . " - " . urlencode($GLOBALS[$x]) . ")");
        // 
        // if (strlen($passkey) != 32) err("Invalid passkey (" . strlen($passkey) . " - $passkey)");
        // 
        // 
        // 
        // //if (empty($ip) || !preg_match('/^(d{1,3}.){3}d{1,3}$/s', $ip))
        // 
        // $ip = getip();
        // 
        // $rsize = 50;
        // foreach(array("num want", "numwant", "num_want") as $k)
        // {
        //  if (isset($_GET[$k]))
        //  {
        //      $rsize = 0 + $_GET[$k];
        //      break;
        //  }
        // }
        // 
        // $agent = $_SERVER["HTTP_USER_AGENT"];
        // 
        // // Deny access made with a browser...
        // if (ereg("^Mozilla\\/", $agent) || ereg("^Opera\\/", $agent) || ereg("^Links ", $agent) || ereg("^Lynx\\/", $agent))
        //  err("torrent not registered with this tracker");
        // 
        // if (!$port || $port > 0xffff)
        //  err("invalid port");
        // 
        // if (!isset($event))
        //  $event = "";
        // 
        // $seeder = ($left == 0) ? "yes" : "no";
        // 
        // dbconn(false);
        // 
        // hit_count();
        // 
        // $valid = @mysql_fetch_row(@mysql_query("SELECT COUNT(*) FROM users WHERE passkey=" . sqlesc($passkey)));
        // 
        // if ($valid[0] != 1) err("Invalid passkey! Re-download the .torrent from $BASEURL");
        // 
        // $res = mysql_query("SELECT id, banned, seeders + leechers AS numpeers, UNIX_TIMESTAMP(added) AS ts FROM torrents WHERE " . hash_where("info_hash", $info_hash)) or err('nog een query');
        // 
        // $torrent = mysql_fetch_assoc($res);
        // if (!$torrent)
        //  err("torrent not registered with this tracker");
        // 
        // $torrentid = $torrent["id"];
        // 
        // $fields = "seeder, peer_id, ip, port, uploaded, downloaded, userid";
        // 
        // $numpeers = $torrent["numpeers"];
        // $limit = "";
        // if ($numpeers > $rsize)
        //  $limit = "ORDER BY RAND() LIMIT $rsize";
        // $res = mysql_query("SELECT $fields FROM peers WHERE torrent = $torrentid AND connectable = 'yes' $limit") or err('nog iets');
        // 
        // $resp = "d" . benc_str("interval") . "i" . $announce_interval . "e" . benc_str("peers") . "l";
        // unset($self);
        // while ($row = mysql_fetch_assoc($res))
        // {
        //  $row["peer_id"] = hash_pad($row["peer_id"]);
        // 
        //  if ($row["peer_id"] === $peer_id)
        //  {
        //      $userid = $row["userid"];
        //      $self = $row;
        //      continue;
        //  }
        // 
        //  $resp .= "d" .
        //      benc_str("ip") . benc_str($row["ip"]) .
        //      benc_str("peer id") . benc_str($row["peer_id"]) .
        //      benc_str("port") . "i" . $row["port"] . "e" .
        //      "e";
        // }
        // 
        // $resp .= "ee";
        // 
        // $selfwhere = "torrent = $torrentid AND " . hash_where("peer_id", $peer_id);
        // 
        // if (!isset($self))
        // {
        //  $sql = "SELECT $fields FROM peers WHERE $selfwhere";
        //  $res = mysql_query($sql) or err('fout');
        //  $row = mysql_fetch_assoc($res);
        //  if ($row)
        //  {
        //      $userid = $row["userid"];
        //      $self = $row;
        //  }
        // }
        // 
        // 
        // //// Up/down stats ////////////////////////////////////////////////////////////
        // if (!isset($self))
        // {
        //  $valid = @mysql_fetch_row(@mysql_query("SELECT COUNT(*) FROM peers WHERE torrent=$torrentid AND passkey=" . sqlesc($passkey))) or err('mistake');
        //  if ($valid[0] >= 1 && $seeder == 'no') err("Connection limit exceeded! You may only leech from one location at a time.");
        //  if ($valid[0] >= 3 && $seeder == 'yes') err("Connection limit exceeded!");
        // 
        //  $rz = mysql_query("SELECT id, uploaded, downloaded, class FROM users WHERE passkey=".sqlesc($passkey)." AND enabled = 'yes' ORDER BY last_access DESC LIMIT 1") or err("Tracker error 2");
        //  if ($MEMBERSONLY && mysql_num_rows($rz) == 0)
        // 
        //  err("Unknown passkey. Please redownload the torrent from $BASEURL.");
        //  $az = mysql_fetch_assoc($rz);
        //  $userid = $az["id"];
        // 
        // //   if ($left > 0 && $az["class"] < UC_VIP)
        //  if ($az["class"] < UC_VIP)
        //  {
        //      $gigs = $az["uploaded"] / (1024*1024*1024);
        //      $elapsed = floor((gmtime() - $torrent["ts"]) / 3600);
        //      $ratio = (($az["downloaded"] > 0) ? ($az["uploaded"] / $az["downloaded"]) : 1);
        //      if ($ratio < 0.5 || $gigs < 5) $wait = 48;
        //      elseif ($ratio < 0.65 || $gigs < 6.5) $wait = 24;
        //      elseif ($ratio < 0.8 || $gigs < 8) $wait = 12;
        //      elseif ($ratio < 0.95 || $gigs < 9.5) $wait = 6;
        //      else $wait = 0;
        //      if ($elapsed < $wait)
        //              err("Not authorized (" . ($wait - $elapsed) . "h) - READ THE FAQ!");
        //  }
        // }
        // else
        // {
        //  $upthis = max(0, $uploaded - $self["uploaded"]);
        //  $downthis = max(0, $downloaded - $self["downloaded"]);
        // 
        //  if ($upthis > 0 || $downthis > 0)
        //      mysql_query("UPDATE users SET uploaded = uploaded + $upthis, downloaded = downloaded + $downthis WHERE id=$userid") or err("Tracker error 3");
        // }
        // 
        // ///////////////////////////////////////////////////////////////////////////////
        // 
        // 
        // 
        // $updateset = array();
        // 
        // if ($event == "stopped")
        // {
        //  if (isset($self))
        //  {
        //      mysql_query("DELETE FROM peers WHERE $selfwhere");
        //      if (mysql_affected_rows())
        //      {
        //          if ($self["seeder"] == "yes")
        //              $updateset[] = "seeders = seeders - 1";
        //          else
        //              $updateset[] = "leechers = leechers - 1";
        //      }
        //  }
        // }
        // else
        // {
        //  if ($event == "completed")
        //      $updateset[] = "times_completed = times_completed + 1";
        // 
        //  if (isset($self))
        //  {
        //      mysql_query("UPDATE peers SET uploaded = $uploaded, downloaded = $downloaded, to_go = $left, last_action = NOW(), seeder = '$seeder'"
        //          . ($seeder == "yes" && $self["seeder"] != $seeder ? ", finishedat = " . time() : "") . " WHERE $selfwhere");
        //      if (mysql_affected_rows() && $self["seeder"] != $seeder)
        //      {
        //          if ($seeder == "yes")
        //          {
        //              $updateset[] = "seeders = seeders + 1";
        //              $updateset[] = "leechers = leechers - 1";
        //          }
        //          else
        //          {
        //              $updateset[] = "seeders = seeders - 1";
        //              $updateset[] = "leechers = leechers + 1";
        //          }
        //      }
        //  }
        //  else
        //  {
        //      if (portblacklisted($port))
        //          err("Port $port is blacklisted.");
        //      else
        //      {
        //          $sockres = @fsockopen($ip, $port, $errno, $errstr, 5);
        //          if (!$sockres)
        //              $connectable = "no";
        //          else
        //          {
        //              $connectable = "yes";
        //              @fclose($sockres);
        //          }
        //      }
        // 
        //  $ret = mysql_query("INSERT INTO peers (connectable, torrent, peer_id, ip, port, uploaded, downloaded, to_go, started, last_action, seeder, userid, agent, uploadoffset, downloadoffset, passkey) VALUES ('$connectable', $torrentid, " . sqlesc($peer_id) . ", " . sqlesc($ip) . ", $port, $uploaded, $downloaded, $left, NOW(), NOW(), '$seeder', $userid, " . sqlesc($agent) . ", $uploaded, $downloaded, " . sqlesc($passkey) . ")") or err('tracker error');
        //      if ($ret)
        //      {
        //          if ($seeder == "yes")
        //              $updateset[] = "seeders = seeders + 1";
        //          else
        //              $updateset[] = "leechers = leechers + 1";
        //      }
        //  }
        // }
        // 
        // if ($seeder == "yes")
        // {
        //  if ($torrent["banned"] != "yes")
        //      $updateset[] = "visible = 'yes'";
        //  $updateset[] = "last_action = NOW()";
        // }
        // 
        // if (count($updateset))
        //  mysql_query("UPDATE torrents SET " . join(",", $updateset) . " WHERE id = $torrentid");
        // 
        // benc_resp_raw($resp);
    }
    
    public function port_blacklisted($port)
    {
    	// direct connect
    	if ($port >= 411 && $port <= 413) return true;

    	// bittorrent
    	if ($port >= 6881 && $port <= 6889) return true;

    	// kazaa
    	if ($port == 1214) return true;

    	// gnutella
    	if ($port >= 6346 && $port <= 6347) return true;

    	// emule
    	if ($port == 4662) return true;

    	// winmx
    	if ($port == 6699) return true;

    	return false;
    }
}

// <?

// ob_start("ob_gzhandler");
// 
// require_once("include/bittorrent.php");
// require_once("include/benc.php");

?>