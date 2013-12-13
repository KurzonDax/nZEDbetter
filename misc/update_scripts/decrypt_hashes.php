<?php
require_once(dirname(__FILE__)."/config.php");
require_once(WWW_DIR."lib/category.php");
require_once(WWW_DIR."lib/groups.php");
require_once(WWW_DIR."lib/nfo.php");
require_once(WWW_DIR."lib/site.php");
require_once(WWW_DIR."lib/namecleaning.php");

preName();

function preName()
{
	$db = new DB();
	$consoletools = new ConsoleTools();
	$counter = 0;
	$loops = 1;
	$reset = 0;
    $affectedRows = 0;
	//$db->queryDirect("update releases set dehashstatus = -1 where dehashstatus = 0 and searchname REGEXP '[a-fA-F0-9]{32}'");

	$db->query("update releases set dehashstatus = -1 where dehashstatus = 0 and searchname REGEXP '[a-fA-F0-9]{32}'");
	if($res = $db->queryDirect("select ID, searchname from releases where dehashstatus between -6 and -1 and searchname REGEXP '[a-fA-F0-9]{32}|[a-fA-F0-9]{40}'"))
	{
		foreach ($res as $row)
		{
			$success = false;
            preg_match("/([a-f0-9]{32}|[a-f0-9]{40})/i", $row['searchname'], $match);
			if (isset($match[1]))
			{
				$hash = $db->escapeString($match[1]);

                if($res1 = $db->queryOneRow("select ID, title, source from predb where md5 =" . $hash . " OR md2=" . $hash . " OR md4=" . $hash . " OR ripemd128=" . $hash .
                    " OR tiger128_3=" . $hash . " OR tiger128_4=" . $hash . " OR haval128_3=" . $hash . " OR haval128_4=" . $hash . " OR haval128_5=" . $hash .
                    " OR ripemd160=" . $hash . " OR tiger160_3=" . $hash . " OR tiger160_4=" . $hash . " OR haval160_3=" . $hash . " OR haval160_4=" . $hash .
                    " OR haval160_5=" . $hash))
				{
					$db->query(sprintf("update releases set dehashstatus = 1, relnamestatus = 6, searchname = %s where ID = %d", $db->escapeString($res1['title']), $row['ID']));
                    $affectedRows = $db->getAffectedRows();
                    echo "Affected Rows: " . $affectedRows . "\n";
                    if ($affectedRows >= 1)
					{
						echo "Renamed hashed release: ".$res1['title']."\n";
						$success = true;
						$counter++;
					}
				}
			}
			if ($success == false)
				$db->query(sprintf("update releases set dehashstatus = dehashstatus - 1 where ID = %d", $row['ID']));
			$consoletools->overWrite("Attempting to dehash hashed releases:".$consoletools->percentString($loops++,mysqli_num_rows($res)));
		}
	}
	echo "\n".$counter. " release(s) names changed.\n";
}
