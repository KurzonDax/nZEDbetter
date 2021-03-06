<?php
require_once(WWW_DIR."/lib/framework/db.php");
require_once(WWW_DIR."/lib/site.php");
require_once(WWW_DIR."/lib/category.php");

class NZB
{
	//
	// Writes out the nzb when processing releases. Moved out of smarty due to memory issues
	// of holding all parts in an array.
	//
	function writeNZBforReleaseId($relid, $relguid, $name, $catId, $path, $echooutput=false, $version=null, $cat=null)
	{
		// TODO: Need to modify this function so the RAM DISK path is both optional, and pulled from database settings
        // TODO: Make log file output optional
        // TODO: Add in logging performance metrics to database

        if ($relid == "" || $relguid == "" || $path == "")
		{
			return false;
		}
		$db = new DB();
		// $binaries = array(); Unused variable

        $outputTimes = false;


        if($outputTimes)
        {
            $timeTotal = microtime(true);
            $timeSQL = 0;
            $timePartLoop = 0;
            $timeGZwrite = 0;
            $timeTemp1 = 0;
        }
		if (!isset($cat))
		{
			$cat = new Category();
		}
		$catrow = $cat->getById($catId);
		if (!isset($version))
		{
			$site = new Sites();
			$version = $site->version();
		}

		$nzbstring = '';

        $nzbstring .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $nzbstring .= "<!DOCTYPE nzb PUBLIC \"-//newzBin//DTD NZB 1.1//EN\" \"http://www.newzbin.com/DTD/nzb/nzb-1.1.dtd\">\n";
        $nzbstring .= "<nzb xmlns=\"http://www.newzbin.com/DTD/2003/nzb\">\n\n";
        $nzbstring .= "<head>\n";
        if ($catrow)
            $nzbstring .= " <meta type=\"category\">".htmlspecialchars($catrow["title"], ENT_QUOTES, 'utf-8')."</meta>\n";
        if ($name != "")
            $nzbstring .= " <meta type=\"name\">".htmlspecialchars($name, ENT_QUOTES, 'utf-8')."</meta>\n";
        $nzbstring .= "</head>\n\n";
        $nzb_guid = "";
        if($outputTimes) $timePartLoop = microtime(true);
        $result = $db->queryDirect(sprintf("SELECT collections.*, UNIX_TIMESTAMP(date) AS unixdate, groups.name as groupname FROM collections inner join groups on collections.groupID = groups.ID WHERE collections.releaseID = %d", $relid));
        while ($binrow = $db->fetchAssoc($result))
        {
            $result2 = $db->queryDirect(sprintf("SELECT ID, name, totalParts from binaries where collectionID = %d order by filenumber", $binrow["ID"]));
            while ($binrow2 = $db->fetchAssoc($result2))
            {
                $nzbstring .= "<file poster=\"".htmlspecialchars($binrow["fromname"], ENT_QUOTES, 'utf-8')."\" date=\"".$binrow["unixdate"]."\" subject=\"".htmlspecialchars($binrow2["name"], ENT_QUOTES, 'utf-8')." (1/".$binrow2["totalParts"].")\">\n";
                $nzbstring .= " <groups>\n";
                $nzbstring .= "  <group>".$binrow["groupname"]."</group>\n";
                $nzbstring .= " </groups>\n";
                $nzbstring .= " <segments>\n";

                if($outputTimes) $timeTemp1 = microtime(true);
                $resparts = $db->queryDirect(sprintf("SELECT messageID, size, partnumber FROM parts WHERE binaryID = %d ORDER BY partnumber", $binrow2["ID"]));
                if($outputTimes) $timeSQL = $timeSQL + (microtime(true)-$timeTemp1);
                while ($partsrow = $db->fetchAssoc($resparts))
                {
                    if ($nzb_guid == "")
                        $nzb_guid = $partsrow["messageID"];

                    $nzbstring .= "  <segment bytes=\"".$partsrow["size"]."\" number=\"".$partsrow["partnumber"]."\">".htmlspecialchars($partsrow["messageID"], ENT_QUOTES, 'utf-8')."</segment>\n";
                }
                $nzbstring .= " </segments>\n</file>\n";
            }
        }
        $nzbstring .= "<!-- generated by nZEDbetter ".$version." -->\n</nzb>";
        if ($outputTimes)
        {
            $timePartLoop = microtime(true) - $timePartLoop;
            $timeGZwrite = microtime(true);
        }
        try
         {
                $tempfile = PROJ_ROOT."/nzbfiles/tmpunrar/".$relguid.".nzb.gz";
                $fp = gzopen($tempfile, 'w6');
                if (!$fp)
                {
                    echo "\nError creating the temp file for Release ID ".$relid."\n";
                    echo "Tempfile: {$tempfile}\n";
                    echo "Release ID: {$relid}  Release GUID: {$relguid}\n";
                    return false;
                }
                $uncompressedBytes = gzwrite($fp, $nzbstring);
                if (!$uncompressedBytes)
                {
                    echo "\nError occured while writing the data to disk for Release ID ".$relid."\n";
                    echo "Tempfile: ".$tempfile."\n";
                    return false;
                }

                $fileok = gzclose($fp);

                If(!$fileok)
                {
                    echo "\nFailure closing the NZB file for Release ID ".$relid."\n";
                    echo "Tempfile: ".$tempfile."\n";
                    return false;
                }

                if(file_exists($tempfile))
                {
                    $mvfile = rename($tempfile, $path);
                    if(!$mvfile)
                    {
                        echo "\nError moving NZB from temp folder for release ID {$relid}\n";
                        echo "Tempfile: {$tempfile}\nNZB File: {$path}\n";
                        return false;
                    }
                }
                if ($outputTimes)
                    $timeGZwrite = microtime(true)-$timeGZwrite;
                if (file_exists($path))
                {
                    chmod($path, 0777); // change the chmod to fix issues some users have with file permissions
                    if ($outputTimes)
                    {

                        $timeTotal = microtime(true) - $timeTotal;
                        $msg = "NZB created in ".$timeTotal." seconds.\n";
                        $msg .= "Time in loop:     ".$timePartLoop."\n";
                        $msg .= "Total SQL time:   ".$timeSQL."\n";
                        $msg .= "GZwrite time:     ".$timeGZwrite."\n";
                        $msg .= "------------------------------------------\n";
                        file_put_contents(WWW_DIR."/lib/logging/nzb_creat.log",$msg, FILE_APPEND);
                        echo "\n".$msg;
                    }
                    return $nzb_guid;
                }
                else
                {
                    echo $path." does not exist.\n";
                    return false;
                }



        }
        catch (Exception $e)
        {
            echo "\nError during NZB creation: {$e}\n";
            echo "Tempfile: {$tempfile}\nNZB File: {$path}\n";
            echo "Release ID: {$relid}  Release GUID: {$relguid}\n";
            $msg = "----------------------------------------------------------------\n";
            $msg .= "Tempfile: {$tempfile}\nNZB File: {$path}\n";
            $msg .= "Release ID: {$relid}  Release GUID: {$relguid}\n";
            $msg .= "Error: ".$e."\n";
            file_put_contents(WWW_DIR."/lib/logging/nzb_error.log",$msg, FILE_APPEND);
            return false;
        }
	}

	//
	// Compress a imported NZB and put it in the nzbfiles folder.
	//
	function copyNZBforImport($relguid, $nzb, $echooutput=false)
	{
		$page = new Page();
		$path = $this->getNZBPath($relguid, $page->site->nzbpath, true, $page->site->nzbsplitlevel);
		$fp = gzopen($path, 'w6');
		if ($fp && $nzb)
		{
			gzwrite ($fp, $nzb);
			gzclose($fp);
			chmod($path, 0777); // change the chmod to fix issues some users have with file permissions
			return true;
		}
		else
		{
			echo "ERROR: NZB already exists?\n";
			return false;
		}
	}

	function buildNZBPath($releaseGuid, $sitenzbpath = "", $createIfDoesntExist = false, $levelsToSplit = 1)
	{
		if ($sitenzbpath == "")
		{
			$s = new Sites();
			$site = $s->get();
			//echo "create site #2\n";
			$sitenzbpath = $site->nzbpath;
			if (substr($sitenzbpath, strlen($sitenzbpath) - 1) != '/')
				$sitenzbpath = $sitenzbpath."/";
			$levelsToSplit = $site->nzbsplitlevel;
		}

		$subpath = "";

		for ($i = 0; $i < $levelsToSplit; $i++)
			$subpath = $subpath . substr($releaseGuid, $i, 1) . "/";

		$nzbpath = $sitenzbpath . $subpath;

		if ($createIfDoesntExist && !file_exists($nzbpath))
				mkdir($nzbpath, 0777, true);

		return $nzbpath;
	}

	//
	// builds a full path to the nzb file on disk. nzbs are stored in a subdir of their first char.
	//
	function getNZBPath($releaseGuid, $sitenzbpath = "", $createIfDoesntExist = false, $levelsToSplit = 1)
	{
		$nzbpath = $this->buildNZBPath($releaseGuid, $sitenzbpath, $createIfDoesntExist, $levelsToSplit);
		return $nzbpath.$releaseGuid.".nzb.gz";
	}

	//
	// Check if the NZB is there, returns path, else false.
	//
	function NZBPath($releaseGuid, $sitenzbpath = "", $levelsToSplit = 1)
	{
		$nzbfile = $this->getNZBPath($releaseGuid, $sitenzbpath, false, $levelsToSplit);
		return !file_exists($nzbfile) ? false : $nzbfile;
	}

	function nzbFileList($nzb)
	{
		$result = array();

		$nzb = str_replace("\x0F", "", $nzb);
	   	$num_pars = 0;
		$xml = @simplexml_load_string($nzb);
		if (!$xml || strtolower($xml->getName()) != 'nzb')
		  return false;

		$i=0;
		foreach($xml->file as $file)
		{
			//subject
			//var_dump($file);
			$title = $file->attributes()->subject;
			if (preg_match('/\.par2/i', $title))
				$num_pars++;

			$result[$i]['title'] = "$title";

			if (preg_match('/\.(\d{2,3}|7z|ace|ai7|srr|srt|sub|aiff|asc|avi|audio|bin|bz2|c|cfc|cfm|chm|class|conf|cpp|cs|css|csv|cue|deb|divx|doc|dot|eml|enc|exe|file|gif|gz|hlp|htm|html|image|iso|jar|java|jpeg|jpg|js|lua|m|m3u|mm|mov|mp3|mpg|nfo|nzb|odc|odf|odg|odi|odp|ods|odt|ogg|par2|parity|pdf|pgp|php|pl|png|ppt|ps|py|r\d{2,3}|ram|rar|rb|rm|rpm|rtf|sfv|sig|sql|srs|swf|sxc|sxd|sxi|sxw|tar|tex|tgz|txt|vcf|video|vsd|wav|wma|wmv|xls|xml|xpi|xvid|zip7|zip)[" ](?!(\)|\-))/i', $file->attributes()->subject, $ext))
			{
				if (preg_match('/\.(r\d{2,3})/i', $ext[0], $extrar))
					$ext[1] = "rar";

				$result[$i]['ext'] = strtolower($ext[1]);
			}
			else
			{
				$result[$i]['ext'] = "";
			}

			//filesize
			$filesize = $numsegs = 0;
			foreach($file->segments->segment as $segment)
			{
				$filesize += $segment->attributes()->bytes;
				$numsegs++;
			}
			$result[$i]['size'] = $filesize;

			//file completion
			preg_match('/\((\d{1,4})\/(?P<total>\d{1,4})\)$/', $title, $parts);
			$result[$i]['partstotal'] = $parts['total'];
			$result[$i]['partsactual'] = $numsegs;

			if (!isset($result[$i]['groups']))
				$result[$i]['groups'] = array();
			if (!isset($result[$i]['segments']))
				$result[$i]['segments'] = array();

			foreach ($file->groups->group as $g)
			{
				array_push($result[$i]['groups'], (string)$g);
			}

			foreach ($file->segments->segment as $s)
			{
				array_push($result[$i]['segments'], (string)$s);
			}
			//var_dump($result);
			unset($result[$i]['segments']['@attributes']);
			$i++;
		}
		return $result;
	}

}
