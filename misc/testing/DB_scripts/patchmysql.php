<?php

/*
 * This inserts the patches into MYSQL.
 */

define('FS_ROOT', realpath(dirname(__FILE__)));
require_once(FS_ROOT."/../../../www/config.php");
require_once(FS_ROOT."/../../../www/lib/framework/db.php");
require_once(FS_ROOT."/../../../www/lib/site.php");

//
// Function from : http://stackoverflow.com/questions/1883079/best-practice-import-mysql-file-in-php-split-queries/2011454#2011454
//
function SplitSQL($file, $delimiter = ';')
{
    set_time_limit(0);

    if (is_file($file) === true)
    {
        $file = fopen($file, 'r');

        if (is_resource($file) === true)
        {
            $query = array();
			$db = new DB();
			
            while (feof($file) === false)
            {
                $query[] = fgets($file);

                if (preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1)
                {
                    $query = trim(implode('', $query));

                    if ($db->query($query) === false)
                    {
                        echo 'ERROR: ' . $query . "\n";
                    }

                    else
                    {
                        echo 'SUCCESS: ' . $query . "\n";
                    }

                    while (ob_get_level() > 0)
                    {
                        ob_end_flush();
                    }

                    flush();
                }

                if (is_string($query) === true)
                {
                    $query = array();
                }
            }

            return fclose($file);
        }
    }

    return false;
}

$os = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? "windows" : "unix";

if (isset($os) && $os == "unix")
{
	$s = new Sites();
	$site = $s->get();
	$currentversion = $site->sqlpatch;
    if(!preg_match('/v/', $currentversion))
        $currentversion = 'v0001';
    $currentversion = preg_replace('/v0*','',$currentversion);
	$patched = 0;
	$patches = array();

	// Open the patch folder.
	if ($handle = @opendir(FS_ROOT.'/../../../db/patches')) 
	{
		while (false !== ($patch = readdir($handle))) 
		{
			$patches[] = $patch;
		}
		closedir($handle);
	}
	else
		exit("ERROR: Have you changed the path to the patches folder, or do you have the right permissions?\n");

	$patchpath = preg_replace('/\/misc\/testing\/DB_scripts/i', '/db/patches/', FS_ROOT);
	sort($patches);
    echo "\nPreparing to apply SQL patched.  WARNING: This process may take quite a while to complete,";
    echo "\ndepending on the size of your database and the number/type of patches to be applied.\n";
    echo "\nPlease be patient, and do not start the tmux scripts, or restart the database during";
    echo "\nthe patching process.\n";
	foreach($patches as $patch)
	{
		if (preg_match('/\.sql$/i', $patch))
		{
			$filepath = $patchpath.$patch;
			$file = fopen($filepath, "r");
			$patch = fread($file, filesize($filepath));

			if (preg_match('/UPDATE `site` set `value` = \'v(\d{4})\' where `setting` = \'sqlpatch\'/i', $patch, $patchnumber))
			{
				if (ltrim($patchnumber['1'],'0') > $currentversion)
				{
                    echo "Applying patch ".$patch."\n";
                    SplitSQL($filepath);
					$patched++;
				}
			}
		}
	}
}
else
	exit("ERROR: It does not appear that you are running nZEDbetter on Linux.\nWindows operating systems are not supported.\n");

if ($patched > 0)
	exit("\n".$patched." patch(es) applied.\n");
if ($patched == 0)
	exit("Nothing to patch, you are already on patch version ".$currentversion.".\n");

?>
