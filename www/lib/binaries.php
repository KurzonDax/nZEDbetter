<?php
require_once(WWW_DIR."lib/framework/db.php");
require_once(WWW_DIR."lib/nntp.php");
require_once(WWW_DIR."lib/groups.php");
require_once(WWW_DIR."lib/backfill.php");
require_once(WWW_DIR."lib/consoletools.php");
require_once(WWW_DIR."lib/site.php");
require_once(WWW_DIR."lib/namecleaning.php");

class Binaries
{
	const BLACKLIST_FIELD_SUBJECT = 1;
	const BLACKLIST_FIELD_FROM = 2;
	const BLACKLIST_FIELD_MESSAGEID = 3;

	function Binaries()
	{
		$this->n = "\n";

		$s = new Sites();
		$site = $s->get();
		$this->compressedHeaders = ($site->compressedheaders == "1") ? true : false;
		$this->messagebuffer = (!empty($site->maxmssgs)) ? $site->maxmssgs : 20000;
		$this->NewGroupScanByDays = ($site->newgroupscanmethod == "1") ? true : false;
		$this->NewGroupMsgsToScan = (!empty($site->newgroupmsgstoscan)) ? $site->newgroupmsgstoscan : 50000;
		$this->NewGroupDaysToScan = (!empty($site->newgroupdaystoscan)) ? $site->newgroupdaystoscan : 3;

        $this->NewGroupMaxMsgs = (!empty($site->newGroupMaxMsgs)) ? $site->newGroupMaxMsgs : 0;

        $this->DoPartRepair = $site->partrepair;  // Why would you turn off part repair if site->partrepair is set to 2?? <- That was based on the original code
        /*
         * I have added a new option to partrepair.  If partrepair = 3, then we'll store
         * the missing parts, but we won't run the part repair routine.  Instead, the site
         * admin will need to run part repair on their own, separate from the tmux scripts.
         */
		$this->DoPartRepairMsg = ($site->partrepair == "2") ? false : true;
		$this->partrepairlimit = (!empty($site->maxpartrepair)) ? $site->maxpartrepair : 15000;
		$this->hashcheck = (!empty($site->hashcheck)) ? $site->hashcheck : 0;
		$this->debug = ($site->debuginfo == "0") ? false : true;

		// Cache of our black/white list.
		$this->blackList = array();
		$this->message = array();
		$this->blackListLoaded = false;
	}

	function updateAllGroups()
	{
		if ($this->hashcheck == 0)
		{
			echo "We have updated the way collections are created, the collection table has to be updated to use the new changes, if you want to run this now, type yes, else type no to see how to run manually.\n";
			if(trim(fgets(fopen("php://stdin","r"))) != 'yes')
				exit("If you want to run this manually, there is a script in misc/testing/DB_scripts/ called resetCollections.php\n");
			$relss = new Releases();
			$relss->resetCollections();
		}
		$n = $this->n;
		$groups = new Groups();
		$res = $groups->getActive();
		$s = new Sites();
		$counter = 1;

		if ($res)
		{
			$alltime = microtime(true);
			echo $n."\033[01;37mUpdating: ".sizeof($res)." group(s) - Using compression? ".(($this->compressedHeaders)?'Yes':'No')."\033[00;37m".$n;

			foreach($res as $groupArr)
			{
				$this->message = array();
				echo "\nStarting group ".$counter." of ".sizeof($res).$n;
				$this->updateGroup($groupArr);
				$counter++;
			}

			echo 'Updating completed in '.number_format(microtime(true) - $alltime, 2).' seconds'.$n;
		}
		else
			echo "No groups specified. Ensure groups are added to nZEDb's database for updating.\n";
	}

	function updateGroup($groupArr)
	{
		$db = new DB();
		$backfill = new Backfill();
		$n = $this->n;
		$this->startGroup = microtime(true);
		$nntp = new Nntp();
		$nntp->doConnect();
        $consoletools = new ConsoleTools();

		echo "\033[01;37mProcessing ".$groupArr['name']."\033[00;37m".$n;

		// Select the group.
		$data = $nntp->selectGroup($groupArr['name']);
		// Attempt to reconnect if there is an error.
		if (PEAR::isError($data))
		{
			echo "\n\nError {$data->code}: {$data->message}\nAttempting to reconnect to usenet.\n";
			$nntp->doQuit();
			unset($nntp);
			$nntp = new Nntp;
			$nntp->doConnect();
			$data = $nntp->selectGroup($groupArr['name']);
			if (PEAR::isError($data))
			{
				echo "Error {$datac->code}: {$datac->message}\nSkipping group: {$groupArr['name']}\n";
				$nntp->doQuit();
				return;
			}
		}

		// Attempt to repair any missing parts before grabbing new ones.
		if ($this->DoPartRepair == 1)
		{
			echo "Part Repair Enabled... Starting repair for ".$groupArr['name']."\n";
			$this->partRepair($nntp, $groupArr);
		}
		elseif ($this->DoPartRepair == 0)
			echo "Part Repair Disabled... Skipping...\n";
        elseif ($this->DoPartRepair == 3)
        {
            echo "\033[01;31mAutomatic part repair disabled, but missing parts will still be stored.".$n;
            echo "Do not forget to manually run part repair on your own.\033[00;37m".$n;
        }

		// Get first and last part numbers from newsgroup.
		$last = $grouplast = $data['last'];

		// For new newsgroups - determine here how far you want to go back.
		if ($groupArr['last_record'] == 0)
		{
			if ($this->NewGroupScanByDays)
			{
				$first = $backfill->daytopost($nntp, $groupArr['name'], $this->NewGroupDaysToScan, true);
				if ($first === '')
				{
					echo "Skipping group: {$groupArr['name']}\n";
					return;
				}
                elseif ($first === "-1")
                {
                    echo "Attempting to retrieve the last ".number_format($this->NewGroupMsgsToScan)." posts to populate ".$groupArr['name'].$n;
                    if ($data['first'] > ($data['last'] - $this->NewGroupMsgsToScan))
                    {
                        echo "Group has less than the requested number of posts available. Filling all available posts. (".number_format(($data['last']-$data['first']))." posts available)".$n;
                        $first = $data['first'];
                    }
                    else
                        $first = $data['last'] - $this->NewGroupMsgsToScan;
                }
                if (($this->NewGroupMaxMsgs != 0) && $this->NewGroupScanByDays)
                {
                    if (($last-$first) > $this->NewGroupMaxMsgs)
                        $first = $last - $this->NewGroupMaxMsgs;
                }
			}
			else
			{
                if ($data['first'] > ($data['last'] - $this->NewGroupMsgsToScan))
					$first = $data['first'];
				else
					$first = $data['last'] - $this->NewGroupMsgsToScan;
			}
			$first_record_postdate = $backfill->postdate($nntp, $first, false, $groupArr['name']);
			$db->query(sprintf("UPDATE groups SET first_record = %s, first_record_postdate = FROM_UNIXTIME(".$first_record_postdate.") WHERE ID = %d", $db->escapeString($first), $groupArr['ID']));
		}
		else
			$first = $groupArr['last_record'] + 1;

		// Generate postdates for first and last records, for those that upgraded.
		if ((is_null($groupArr['first_record_postdate']) || is_null($groupArr['last_record_postdate'])) && ($groupArr['last_record'] != "0" && $groupArr['first_record'] != "0"))
			 $db->query(sprintf("UPDATE groups SET first_record_postdate = FROM_UNIXTIME(".$backfill->postdate($nntp,$groupArr['first_record'],false,$groupArr['name'])."), last_record_postdate = FROM_UNIXTIME(".$backfill->postdate($nntp,$groupArr['last_record'],false,$groupArr['name']).") WHERE ID = %d", $groupArr['ID']));

		// Calculate total number of parts.
		$total = $grouplast - $first + 1;

		// If total is bigger than 0 it means we have new parts in the newsgroup.
		if($total > 0)
		{
			echo "Group ".$data["group"]." has ".number_format($total)." new articles.\n";
			echo "Server oldest: ".number_format($data['first'])." Server newest: ".number_format($data['last'])." Local newest: ".number_format($groupArr['last_record']).$n.$n;
			if ($groupArr['last_record'] == 0)
				echo "New group starting with ".(($this->NewGroupScanByDays) ? $this->NewGroupDaysToScan." days" : $this->NewGroupMsgsToScan." messages")." worth.\n";

			$done = false;

			// Get all the parts (in portions of $this->messagebuffer to not use too much memory).
			while ($done === false)
			{
				$this->startLoop = microtime(true);
                // Make sure the group hasn't been deactivated.
                $active = $db->queryOneRow("SELECT active FROM groups WHERE ID=".$groupArr['ID']);
                If ($active['active'] == 0)
                {
                    // If it has been deactivated, break out of loop.
                    echo "\033[01;31mGroup ".$groupArr['name']." has been deactivated.  Stopping thread.\033[00;37m\n";
                    break;
                }
				if ($total > $this->messagebuffer)
				{
					if ($first + $this->messagebuffer > $grouplast)
						$last = $grouplast;
					else
						$last = $first + $this->messagebuffer;
				}

				echo $n."Getting ".number_format($last-$first+1)." articles (".number_format($first)." to ".number_format($last).") from \033[01;36m".$data["group"]." - ".number_format($grouplast - $last)."\033[00;37m in queue.\n";
				flush();

				// Get article headers from newsgroup.
                // TODO: Bug here that can cause a group to repeatedly grab the same articles.
                // Seems to happen when the server does not return a large number of messages.  Need to
                // investigate further.
				$lastId = $this->scan($nntp, $groupArr, $first, $last);
                If ($lastId == $first)
                {
                    echo "\033[01;31mWARNING!! Server not sending updated messages. Group: ".$groupArr['name'];
                    echo "Skipping group ".$groupArr['name']."\033[00;37m\n";
                    // $db->query("UPDATE groups SET active=0, backfill=0 WHERE ID=".$groupArr['ID']);
                    $done = true;
                }
				if ($lastId === false)
				{
					// Scan failed - skip group.
					return;
				}
				$db->query(sprintf("UPDATE groups SET last_record = %s, last_updated = now() WHERE ID = %d", $db->escapeString($lastId), $groupArr['ID']));

				if ($last == $grouplast)
					$done = true;
				else
				{
					$last = $lastId;
					$first = $last + 1;
				}
			}

			$last_record_postdate = $backfill->postdate($nntp,$last,false,$groupArr['name']);
			// Set group's last postdate.
			$db->query(sprintf("UPDATE groups SET last_record_postdate = FROM_UNIXTIME(".$last_record_postdate."), last_updated = now() WHERE ID = %d", $groupArr['ID']));
			$timeGroup = number_format(microtime(true) - $this->startGroup, 2);
			echo $data['group']." processed in ".$timeGroup." seconds.\n\n";
		}
		else
			echo "No new articles for ".$data['group']." (first ".number_format($first)." last ".number_format($last)." total ".number_format($total).") grouplast ".number_format($groupArr['last_record']).$n.$n;
	}

	function scan($nntp, $groupArr, $first, $last, $type='update')
	{


        $db = new Db();
		$namecleaning = new nameCleaning();
		$s = new Sites;
		$site = $s->get();
		$tmpPath = $site->tmpunrarpath."/";
		$n = $this->n;

		if ($this->debug)
			$consoletools = new ConsoleTools();

		if (!isset($nntp))
		{
			$nntp = new Nntp();
			$nntp->doConnect();
		}

		$this->startHeaders = microtime(true);
		$this->startLoop = microtime(true);

		// Select the group.
		$data = $nntp->selectGroup($groupArr['name']);
		// Attempt to reconnect if there is an error.
		if (PEAR::isError($data))
		{
			echo "\n\nError {$data->code}: {$data->message}\nAttempting to reconnect to usenet.\n";
			$nntp->doQuit();
			unset($nntp);
			$nntp = new Nntp;
			$nntp->doConnect();
			$data = $nntp->selectGroup($groupArr['name']);
			if (PEAR::isError($data))
			{
				echo "Error {$data->code}: {$data->message}\nSkipping group: {$groupArr['name']}\n";
				$nntp->doQuit();
				return;
			}
		}
		
		// Download the headers.
		$msgs = $nntp->getOverview($first."-".$last, true, false);
		if ($type != 'partrepair')
		{
			if(PEAR::isError($msgs))
			{
				// This is usually a compression error, so lets try disabling compression.
				echo "\n\nThe server has not returned any data, we will try disabling compression temporarily and retry.\n";
				$nntp->doQuit();
				unset($nntp, $msgs);
				$nntp = new Nntp;
				$nntp->doConnectNC();
				$data = $nntp->selectGroup($groupArr['name']);
				if (PEAR::isError($data))
				{
					$nntp->doQuit();
					echo "Error {$data->code}: {$data->message}\nSkipping group: {$groupArr['name']}\n";
					return;
				}
				else
				{
					$msgs = $nntp->getOverview($first."-".$last, true, false);
					if(PEAR::isError($msgs))
					{
						$nntp->doQuit();
						echo "Error {$msgs->code}: {$msgs->message}\nSkipping group: ${groupArr['name']}\n";
						return;
					}
				}
			}
		}

		$timeHeaders = number_format(microtime(true) - $this->startHeaders, 2);
		$rangerequested = range($first, $last);
		$msgsreceived = $msgsblacklisted = $msgsignored = $msgsnotinserted = array();

		$this->startCleaning = microtime(true);
		if (is_array($msgs))
		{
			// For looking at the difference between $subject and $cleansubject.
			if ($this->debug)
				$colnames = $orignames = array();
			// Loop articles, figure out files/parts.


            if ($insPartsStmt = $db->Prepare("INSERT IGNORE INTO parts (binaryID, number, messageID, partnumber, size, collectionID, parthash) VALUES (?, ?, ?, ?, ?, ?, ?)"))
                $insPartsStmt->bind_param('dssssds', $pBinaryID, $pNumber, $pMessageID, $pPartNumber, $pSize, $collectionID, $partHash);

			foreach($msgs AS $msg)
			{
				if (!isset($msg['Number']))
					continue;

				if (isset($msg['Bytes']))
					$bytes = $msg['Bytes'];
				else
					$bytes = $msg[':bytes'];

				$msgsreceived[] = $msg['Number'];

				// Not a binary post most likely.. continue.
				if (!isset($msg['Subject']) || !preg_match('/yEnc \((\d+)\/(\d+)\)$/i', $msg['Subject'], $matches))
				{
					$msgsignored[] = $msg['Number'];
					continue;
				}

				// Filter subject based on black/white list.
				if ($this->isBlackListed($msg, $groupArr['name']))
				{
					$msgsblacklisted[] = $msg['Number'];
					continue;
				}

				// Attempt to find the file count. If it is not found, set it to 0.
				$nofiles = false;
				$partless = preg_replace('/\((\d+)\/(\d+)\)$/', '', $msg['Subject']);
                // Added some negative lookahead and lookbehind conditions to try and prevent matching the filenumber on what
                // is really a year i.e. ( Prism Albums 12x - By Dready Niek (1977-2008) )  ( ** By Dready Niek ** ) [00/37] - "Prism Albums 12x - By Dready Niek (1977-2008).part001.rar" yEnc
				if (!preg_match('/(\[|\((?!.+\[)|(?<!\([A-Za-z] )\s(?!.+\[|.+\()(?<!\([A-Za-z] ))(\d{1,5})(\/|(\s|_)of(\s|_)|\-)(\d{1,5})(\]|\)|\s|$|:)/i', $partless, $filecnt))
				{
                    // Changed this from zero to one.  Found that several groups did not regularly include
                    // any identifier of the number of files, just total parts.  In all cases that I observed, there was
                    // only one binary associated with the 'release'.  Will monitor this for negative impacts
                    // specifically with NZB creation and accuracy.
					$filecnt[2] = $filecnt[6] = "1";
					$nofiles = true;
				}

				if(is_numeric($matches[1]) && is_numeric($matches[2]))
				{
					array_map('trim', $matches);
					// Inserted into the collections table as the subject.
					$subject = utf8_encode(trim($partless));

					// Used for the sha1 hash (see below).
                    // Note: original code has the order of the parameters wrong in the function call below
					$cleansubject = $namecleaning->collectionsCleaner($subject, $nofiles, $groupArr['ID']);
					
					// For looking at the difference between $subject and $cleansubject.
					if ($this->debug)
					{
						if (!in_array($cleansubject, $colnames))
						{
							$colnames[] = $cleansubject;
							$orignames[] = $msg['Subject'];
						}
					}

					// Set up the info for inserting into parts/binaries/collections tables.
					if(!isset($this->message[$subject]))
					{
						$this->message[$subject] = $msg;
						$this->message[$subject]['MaxParts'] = (int)$matches[2];
						$this->message[$subject]['Date'] = strtotime($this->message[$subject]['Date']);
						// Ties articles together when forming the release/nzb.
						$this->message[$subject]['CollectionHash'] = sha1($cleansubject.$msg['From'].$groupArr['ID'].$filecnt[6]);
						$this->message[$subject]['MaxFiles'] = (int)$filecnt[6];
						$this->message[$subject]['File'] = (int)$filecnt[2];
						
					}

					if($site->grabnzbs != 0 && preg_match('/".+?\.nzb" yEnc$/', $subject))
					{
							$db->queryDirect(sprintf("INSERT IGNORE INTO `nzbs` (`message_id`, `group`, `article-number`, `subject`, `collectionhash`, `filesize`, `partnumber`, `totalparts`, `postdate`, `dateadded`) values (%s, %s, %s, %s, %s, %d, %d, %d, FROM_UNIXTIME(%s), now())", $db->escapeString(substr($msg['Message-ID'],1,-1)), $db->escapeString($groupArr['name']), $db->escapeString($msg['Number']), $db->escapeString($subject), $db->escapeString($this->message[$subject]['CollectionHash']), (int)$bytes, (int)$matches[1], (int)$matches[2], $db->escapeString($this->message[$subject]['Date'])));
							$db->queryDirect(sprintf("UPDATE `nzbs` SET `dateadded` = NOW() WHERE collectionhash = %s", $db->escapeString($this->message[$subject]['CollectionHash'])));
					}

					if((int)$matches[1] > 0)
						$this->message[$subject]['Parts'][(int)$matches[1]] = array('Message-ID' => substr($msg['Message-ID'],1,-1), 'number' => $msg['Number'], 'part' => (int)$matches[1], 'size' => $bytes);
				}
			}

			// For looking at the difference between $subject and $cleansubject.
			if ($this->debug && count($colnames) > 1 && count($orignames) > 1)
			{
				$arr = array_combine($colnames, $orignames);
				ksort($arr);
				print_r($arr);
			}
			$timeCleaning = number_format(microtime(true) - $this->startCleaning, 2);
			unset($msg,$msgs);
			$maxnum = $last;
			$rangenotreceived = array_diff($rangerequested, $msgsreceived);


			echo "\033[01;32m".date('H:i:s').": Received ".number_format(sizeof($msgsreceived))." articles of ".(number_format($last-$first+1))." requested, ".sizeof($msgsblacklisted)." blacklisted, ".sizeof($msgsignored)." not yEnc. Group: ".$groupArr['name']."\n\033[00;37m";
	

			if (sizeof($rangenotreceived) > 0)
			{
				switch($type)
				{
					case 'backfill':
						//don't add missing articles
						//break;
					case 'partrepair':
					case 'update':
					default:
						if ($this->DoPartRepair)
                        {
                            echo "\nServer did not return ".sizeof($rangenotreceived)." articles for group ".$groupArr['name']." - adding to part repair DB.\n";

                            $this->addMissingParts($rangenotreceived, $groupArr['ID']);
                        }

					break;
				}


			}

			$this->startUpdate = microtime(true);
            $partsAdded = 0;
			if(isset($this->message) && count($this->message))
			{
				$maxnum = $first;

				// Insert collections, binaries and parts into database. When collection exists, only insert new binaries, when binary already exists, only insert new parts.


				$lastCollectionHash = $lastBinaryHash = "";
				$lastCollectionID = $lastBinaryID = -1;

				$db->setAutoCommit(false);

				foreach($this->message AS $subject => $data)
				{
					if(isset($data['Parts']) && count($data['Parts']) > 0 && $subject != '')
					{
						$collectionHash = $data['CollectionHash'];
						$partSizeTotal = 0;
                        $partCountTotal = 0;
                        // Get part count and total size
                        foreach($data['Parts'] AS $partdata)
                        {
                            $partCountTotal ++;
                            $partSizeTotal += $partdata['size'];
                        }
                        if ($lastCollectionHash == $collectionHash)
                        {
							$collectionID = $lastCollectionID;
                            $db->query("UPDATE collections SET filesize = filesize+".$partSizeTotal." WHERE ID=".$collectionID);
                        }
                        else
						{
							$lastCollectionHash = $collectionHash;
							$lastBinaryHash = "";
							$lastBinaryID = -1;
                            // Another bug busted below.  INSERT would fail occasionally because Xref was > 255. We cut it short below.
                            // Will have to monitor to see if this creates any other issues, but I don't think Xref is really used
                            // elsewhere in the project.
                            if (strlen($db->escapeString($data['Xref'])) > 254)
                                $data['Xref'] = substr($db->escapeString($data['Xref']),0, 254);
                            // And yet three more bugs.  Subjects that are too long, subjects that contain backslashes, and subjects (or From's) that contain non-UTF8 characters
                            if (strlen($db->escapeString($subject)) > 254)
                                $subject = substr($db->escapeString($subject),0,254);
                            // TODO: Get rid of the garbage in the subject
                            // Getting an error on the following.  Since it's a pretty limited use case, going to disable for now
                            //$subject = preg_replace('/[\u007B-\uFEFC]|[\uFF5B-\uFFFD]/u',"",$db->escapeString($subject));
                            $subject = $namecleaning->cleanUnicode($subject);
                            // TODO: Clean up the 'From' field
                            // $from = preg_replace('/[\u007B-\uFEFC]|[\uFF5B-\uFFFD]/',"",$db->escapeString($data['From']));
                            $data['From'] = $namecleaning->cleanUnicode($data['From']);
                            $csql = sprintf("INSERT INTO collections (subject, fromname, date, xref, groupID, totalFiles, collectionhash, dateadded, filesize) VALUES (%s, %s, FROM_UNIXTIME(%s), %s, %d, %s, %s, now(), %d) ON DUPLICATE KEY UPDATE dateadded=now(), filesize=filesize+%d", $db->escapeString($subject), $db->escapeString($data['From']), $db->escapeString($data['Date']), $db->escapeString($data['Xref']), $groupArr['ID'], $db->escapeString($data['MaxFiles']), $db->escapeString($collectionHash), $partSizeTotal, $partSizeTotal);
							$colInsertResult = $db->queryInsert($csql);
                            $colsInserted = $db->getAffectedRows();
                            // Must perform a DB Commit here so we don't get screwed up collections due to multiple threads updating the same group
                            // Strange behavior from getAffectedRows... If no new row was added because of a non-unique hash, getAffectedRows returns a value of 2
                            // If a new row was added, then it returns a value of 1
                            if($colsInserted==1)  $db->Commit();
                            $collectionrow = $db->queryOneRow("SELECT ID FROM collections WHERE collectionhash=".$db->escapeString($collectionHash));

                            if(!$collectionrow)
                            {
                                echo "\033[1;31m\nWARNING: Error occurred inserting collection. Collection hash: ".$db->escapeString($collectionHash)."\033[0;37m\n";
                                file_put_contents(WWW_DIR."/lib/logging/collections_insert.log","-----------------\n".date("H:i:s A")." Result ID: ".$colInsertResult." Group: ".$groupArr['ID']." Hash: ".$collectionHash." Subject: ".$db->escapeString($subject)."\n".$csql."\n", FILE_APPEND);
                                $db->Commit();
                                break;
                            }
                            else
                            {
                                $collectionID = $collectionrow['ID'];
                                $lastCollectionID = $collectionID;
                            }
						}

						$binaryHash = md5($subject.$data['From'].$groupArr['ID']);

						if ($lastBinaryHash == $binaryHash)
                        {
                            $binaryID = $lastBinaryID;
                            $db->query("UPDATE binaries SET binarysize=binarysize+".$partSizeTotal.", partsInDB=partsInDB+".$partCountTotal." WHERE ID=".$binaryID);
                        }
                        else
						{
							$lastBinaryHash = $binaryHash;
                            if(strlen($subject)>254)
                                $subject = substr($db->escapeString($subject),0,254);
                            $subject = $namecleaning->cleanUnicode($subject);
                            $bsql = sprintf("INSERT INTO binaries (binaryhash, name, collectionID, totalParts, filenumber, binarySize, partsInDB) VALUES (%s, %s, %d, %s, %s, %d, %d) ON DUPLICATE KEY UPDATE partcheck=0, binarySize=binarySize+%d, partsInDB=partsInDB+%d", $db->escapeString($binaryHash), $db->escapeString($subject), $collectionID, $db->escapeString($data['MaxParts']), $db->escapeString(round($data['File'])), $partSizeTotal, $partCountTotal, $partSizeTotal, $partCountTotal);
							$binInsertResult = $db->queryInsert($bsql);
                            $binsInserted = $db->getAffectedRows();
                            // Have to do a database Commit here so we don't get duplicate binaries due to running multiple threads
                            if ($binsInserted==1) $db->Commit();
                            $binaryRow = $db->queryOneRow(sprintf("SELECT ID FROM binaries WHERE binaryhash = %s", $db->escapeString($binaryHash)));
                            if(!$binaryRow)
                            {
                                echo "\033[1;31m\nWARNING: Error occurred inserting binary. Binary hash: ".$db->escapeString($binaryHash)."\033[0;37m\n";
                                file_put_contents(WWW_DIR."/lib/logging/binaries_insert.log","-----------------\n".date("H:i:s A")." Result ID: ".$binInsertResult." Group: ".$groupArr['ID']." Hash: ".$binaryHash." Subject: ".$db->escapeString($subject)."\n".$bsql."\n", FILE_APPEND);
                                $db->Commit();
                                break;
                            }
                            else
                            {
                                $binaryID = $binaryRow["ID"];
                                $lastBinaryID = $binaryID;
                            }
						}

						foreach($data['Parts'] AS $partdata)
						{
							$pBinaryID = $binaryID;
							$pMessageID = $partdata['Message-ID'];
							$pNumber = $partdata['number'];
							$pPartNumber = round($partdata['part']);
							$pSize = $partdata['size'];
                            $partHash = sha1($pMessageID.$groupArr['name']);

							$maxnum = ($partdata['number'] > $maxnum) ? $partdata['number'] : $maxnum;

                            // $pSQL = sprintf("INSERT IGNORE INTO parts (binaryID, number, messageID, partnumber, size, collectionID, parthash) VALUES (%d, %d, %s, %d, %d, %d, %s)",$pBinaryID , $pNumber, $db->escapeString($pMessageID), $pPartNumber, $pSize, $collectionID, $db->escapeString($partHash));
                            // $partInsertResult = $db->queryDirect($pSQL);

                            if (!$insPartsStmt->execute())
                            {
                                $msgsnotinserted[] = $partdata['number'];
                                // file_put_contents(WWW_DIR."/lib/logging/parts_insert.log","---------------------------\n".$pSQL."\n", FILE_APPEND);
                            }
                            else
                            {
                                $partsAdded++;
                                $partCountTotal++;
                                $partSizeTotal += $pSize;
                            }

						}

					}



                }
				if (sizeof($msgsnotinserted) > 0)
				{
					echo 'WARNING: '.sizeof($msgsnotinserted).' parts failed to insert'.$n;
					if ($this->DoPartRepair)
						$this->addMissingParts($msgsnotinserted, $groupArr['ID']);
				}
				$db->Commit();
				$db->setAutoCommit(true);
			}
            // $insPartsStmt->close;  This statement returns an error and not sure why.
			$timeUpdate = number_format(microtime(true) - $this->startUpdate, 2);
			$timeLoop = number_format(microtime(true)-$this->startLoop, 2);

			if ($type != 'partrepair')
			{
				echo "\033[01;33m".$timeHeaders."s to download articles, ".$timeCleaning."s to process articles, ".$timeUpdate."s to insert ".number_format($partsAdded)." articles, ".$timeLoop."s total. Group: ".$groupArr['name']."\033[00;37m\n";
			}
			unset($this->message, $data);
			return $maxnum;
		}
		else
		{
			if ($type != 'partrepair')
			{
				echo "Error: Can't get parts from server (msgs not array)".$n;
				echo "Skipping group: ${groupArr['name']}".$n;
				return false;
			}
		}
	}

	public function partRepair($nntp, $groupArr, $groupID='', $partID='')
	{
		$n = $this->n;
		$groups = new Groups();

		// Get all parts in partrepair table.
		$db = new DB();
		if ($partID=='')
			$missingParts = $db->query(sprintf("SELECT * FROM partrepair WHERE groupID = %d AND attempts < 5 ORDER BY numberID ASC LIMIT %d", $groupArr['ID'], $this->partrepairlimit));
		else
		{
			$groupArr = $groups->getByID($groupID);
			$missingParts = array(array('numberID' => $partID, 'groupID' => $groupArr['ID']));
		}
		$partsRepaired = $partsFailed = 0;

		if (sizeof($missingParts) > 0)
		{
			if ($partID=='')
				echo "Attempting to repair ".sizeof($missingParts)." parts...".$n;

			// Loop through each part to group into ranges.
			$ranges = array();
			$lastnum = $lastpart = 0;
			foreach($missingParts as $part)
			{
				if (($lastnum+1) == $part['numberID']) {
					$ranges[$lastpart] = $part['numberID'];
				} else {
					$lastpart = $part['numberID'];
					$ranges[$lastpart] = $part['numberID'];
				}
				$lastnum = $part['numberID'];
			}

			$num_attempted = 0;
			$consoleTools = new ConsoleTools();

			// Download missing parts in ranges.
			foreach($ranges as $partfrom=>$partto)
			{
				$this->startLoop = microtime(true);

				$num_attempted += $partto - $partfrom + 1;
				if ($partID=='')
				{
					echo $n;
					$consoleTools->overWrite("Attempting repair: ".$consoleTools->percentString($num_attempted,sizeof($missingParts)).": ".$partfrom." to ".$partto);
				}
				else
					echo "Attempting repair: ".$partfrom.$n;

				// Get article from newsgroup.
				$this->scan($nntp, $groupArr, $partfrom, $partto, 'partrepair');

				// Check if the articles were added.
				$articles = implode(',', range($partfrom, $partto));
				$sql = sprintf("SELECT pr.ID, pr.numberID, p.number from partrepair pr LEFT JOIN parts p ON p.number = pr.numberID WHERE pr.groupID=%d AND pr.numberID IN (%s) ORDER BY pr.numberID ASC", $groupArr['ID'], $articles);

				$result = $db->queryDirect($sql);
				while ($r = $db->fetchAssoc($result))
				{
					if (isset($r['number']) && $r['number'] == $r['numberID'])
					{
						$partsRepaired++;

						// Article was added, delete from partrepair.
						$db->query(sprintf("DELETE FROM partrepair WHERE ID=%d", $r['ID']));
					}
					else
					{
						$partsFailed++;

						// Article was not added, increment attempts.
						$db->query(sprintf("UPDATE partrepair SET attempts=attempts+1 WHERE ID=%d", $r['ID']));
					}
				}
			}

			if ($partID=='')
				echo $n;
			echo $partsRepaired.' parts repaired.'.$n;
		}

		// Remove articles that we cant fetch after 5 attempts.
		$db->query(sprintf("DELETE FROM partrepair WHERE attempts >= 5 AND groupID = %d", $groupArr['ID']));

	}

	private function addMissingParts($numbers, $groupID)
	{
		$db = new DB();
		$insertStr = "INSERT IGNORE INTO partrepair (numberID, groupID) VALUES ";
		foreach($numbers as $number)
		{
			$insertStr .= sprintf("(%d, %d), ", $number, $groupID);
		}
		$insertStr = substr($insertStr, 0, -2);
		$insertStr .= " ON DUPLICATE KEY UPDATE attempts=attempts+1";
		return $db->queryInsert($insertStr, false);
	}

	public function retrieveBlackList()
	{
		if ($this->blackListLoaded) { return $this->blackList; }
		$blackList = $this->getBlacklist(true);
		$this->blackList = $blackList;
		$this->blackListLoaded = true;
		return $blackList;
	}

	public function isBlackListed($msg, $groupName)
	{
		$blackList = $this->retrieveBlackList();
		$field = array();
		if (isset($msg["Subject"]))
			$field[Binaries::BLACKLIST_FIELD_SUBJECT] = $msg["Subject"];

		if (isset($msg["From"]))
			$field[Binaries::BLACKLIST_FIELD_FROM] = $msg["From"];

		if (isset($msg["Message-ID"]))
			$field[Binaries::BLACKLIST_FIELD_MESSAGEID] = $msg["Message-ID"];

		$omitBinary = false;

		foreach ($blackList as $blist)
		{
			if (preg_match('/^'.$blist['groupname'].'$/i', $groupName))
			{
				//blacklist
				if ($blist['optype'] == 1)
				{
					if (preg_match('/'.$blist['regex'].'/i', $field[$blist['msgcol']])) {
						$omitBinary = true;
					}
				}
				else if ($blist['optype'] == 2)
				{
					if (!preg_match('/'.$blist['regex'].'/i', $field[$blist['msgcol']])) {
						$omitBinary = true;
					}
				}
			}
		}

		return $omitBinary;
	}

	public function search($search, $limit=1000, $excludedcats=array())
	{
		$db = new DB();

		//
		// if the query starts with a ^ it indicates the search is looking for items which start with the term
		// still do the like match, but mandate that all items returned must start with the provided word
		//
		$words = explode(" ", $search);
		$searchsql = "";
		$intwordcount = 0;
		if (count($words) > 0)
		{
			foreach ($words as $word)
			{
				//
				// see if the first word had a caret, which indicates search must start with term
				//
				if ($intwordcount == 0 && (strpos($word, "^") === 0))
					$searchsql.= sprintf(" and b.name like %s", $db->escapeString(substr($word, 1)."%"));
				else
					$searchsql.= sprintf(" and b.name like %s", $db->escapeString("%".$word."%"));

				$intwordcount++;
			}
		}

		$exccatlist = "";
		if (count($excludedcats) > 0)
			$exccatlist = " and b.categoryID not in (".implode(",", $excludedcats).") ";

		$res = $db->query(sprintf("
					SELECT b.*,
					g.name AS group_name,
					r.guid,
					(SELECT COUNT(ID) FROM parts p WHERE p.binaryID = b.ID) as 'binnum'
					FROM binaries b
					INNER JOIN groups g ON g.ID = b.groupID
					LEFT OUTER JOIN releases r ON r.ID = b.releaseID
					WHERE 1=1 %s %s order by DATE DESC LIMIT %d ",
					$searchsql, $exccatlist, $limit));

		return $res;
	}

	public function getForReleaseId($id)
	{
		$db = new DB();
		return $db->query(sprintf("select binaries.* from binaries WHERE releaseID = %d order by relpart", $id));
	}

	public function getById($id)
	{
		$db = new DB();
		return $db->queryOneRow(sprintf("select binaries.*, collections.groupID, groups.name as groupname from binaries, collections left outer join groups on collections.groupID = groups.ID WHERE binaries.ID = %d ", $id));
	}

	public function getBlacklist($activeonly=true)
	{
		$db = new DB();

		$where = "";
		if ($activeonly)
			$where = " WHERE binaryblacklist.status = 1 ";

		return $db->query("SELECT binaryblacklist.ID, binaryblacklist.optype, binaryblacklist.status, binaryblacklist.description, binaryblacklist.groupname AS groupname, binaryblacklist.regex,
												groups.ID AS groupID, binaryblacklist.msgcol FROM binaryblacklist
												left outer JOIN groups ON groups.name = binaryblacklist.groupname
												".$where."
												ORDER BY coalesce(groupname,'zzz')");
	}

	public function getBlacklistByID($id)
	{
		$db = new DB();
		return $db->queryOneRow(sprintf("select * from binaryblacklist WHERE ID = %d ", $id));
	}

	public function deleteBlacklist($id)
	{
		$db = new DB();
		return $db->query(sprintf("DELETE FROM binaryblacklist WHERE ID = %d", $id));
	}

	public function updateBlacklist($regex)
	{
		$db = new DB();

		$groupname = $regex["groupname"];
		if ($groupname == "")
			$groupname = "null";
		else
		{
			$groupname = preg_replace("/a\.b\./i", "alt.binaries.", $groupname);
			$groupname = sprintf("%s", $db->escapeString($groupname));
		}

		$db->query(sprintf("update binaryblacklist set groupname=%s, regex=%s, status=%d, description=%s, optype=%d, msgcol=%d WHERE ID = %d ", $groupname, $db->escapeString($regex["regex"]), $regex["status"], $db->escapeString($regex["description"]), $regex["optype"], $regex["msgcol"], $regex["id"]));
	}

	public function addBlacklist($regex)
	{
		$db = new DB();

		$groupname = $regex["groupname"];
		if ($groupname == "")
			$groupname = "null";
		else
		{
			$groupname = preg_replace("/a\.b\./i", "alt.binaries.", $groupname);
			$groupname = sprintf("%s", $db->escapeString($groupname));
		}

		return $db->queryInsert(sprintf("INSERT IGNORE INTO binaryblacklist (groupname, regex, status, description, optype, msgcol) values (%s, %s, %d, %s, %d, %d) ",
			$groupname, $db->escapeString($regex["regex"]), $regex["status"], $db->escapeString($regex["description"]), $regex["optype"], $regex["msgcol"]));
	}

	public function delete($id)
	{
		$db = new DB();
		$bins = $db->query(sprintf("select ID from binaries WHERE collectionID = %d", $id));
		foreach ($bins as $bin)
			$db->query(sprintf("DELETE FROM parts WHERE binaryID = %d", $bin["ID"]));
		$db->query(sprintf("DELETE FROM binaries WHERE collectionID = %d", $id));
		$db->query(sprintf("DELETE FROM collections WHERE ID = %d", $id));
	}
}
