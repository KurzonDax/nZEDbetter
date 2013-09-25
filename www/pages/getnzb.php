
<!-- saved from url=(0062)https://raw.github.com/nZEDb/nZEDb/master/www/pages/getnzb.php -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body><pre style="word-wrap: break-word; white-space: pre-wrap;">&lt;?php
require_once(WWW_DIR."/lib/releases.php");
require_once(WWW_DIR."/lib/nzb.php");

$nzb = new NZB;
$rel = new Releases;
$uid = 0;

// Page is accessible only by the rss token, or logged in users.
if ($users-&gt;isLoggedIn())
{
	$uid = $users-&gt;currentUserId();
	$maxdls = $page-&gt;userdata["downloadrequests"];
}
else
{

	if ($page-&gt;site-&gt;registerstatus == Sites::REGISTER_STATUS_API_ONLY)
		$res = $users-&gt;getById(0);
	else
	{
		if ((!isset($_GET["i"]) || !isset($_GET["r"])))
			$page-&gt;show403();

		$res = $users-&gt;getByIdAndRssToken($_GET["i"], $_GET["r"]);
		if (!$res)
			$page-&gt;show403();
	}
	$uid = $res["ID"];
	$maxdls = $res["downloadrequests"];
}

// Remove any suffixed id with .nzb which is added to help weblogging programs see nzb traffic.
if (isset($_GET["id"]))
	$_GET["id"] = preg_replace("/\.nzb/i", "", $_GET["id"]);

// Check download limit on user role.
$dlrequests = $users-&gt;getDownloadRequests($uid);
if ($dlrequests['num'] &gt; $maxdls)
	$page-&gt;show503();

// User requested a zip of guid,guid,guid releases.
if (isset($_GET["id"]) &amp;&amp; isset($_GET["zip"]) &amp;&amp; $_GET["zip"] == "1")
{
	$guids = explode(",", $_GET["id"]);
	if ($dlrequests['num']+sizeof($guids) &gt; $maxdls)
		$page-&gt;show503();

	$zip = $rel-&gt;getZipped($guids);
	if (strlen($zip) &gt; 0)
	{
		$users-&gt;incrementGrabs($uid, count($guids));
		foreach ($guids as $guid)
		{
			$rel-&gt;updateGrab($guid);
			$users-&gt;addDownloadRequest($uid);

			if (isset($_GET["del"]) &amp;&amp; $_GET["del"]==1)
				$users-&gt;delCartByUserAndRelease($guid, $uid);
		}

		$filename = date("Ymdhis").".nzb.zip";
		header("Content-type: application/octet-stream");
		header("Content-disposition: attachment; filename=".$filename);
		echo $zip;
		die();
	}
	else
		$page-&gt;show404();
}

if (isset($_GET["id"]))
{
	$reldata = $rel-&gt;getByGuid($_GET["id"]);
	$nzbpath = $nzb-&gt;getNZBPath($_GET["id"], $page-&gt;site-&gt;nzbpath, false, $page-&gt;site-&gt;nzbsplitlevel);

	if (!file_exists($nzbpath))
		$page-&gt;show404();

	if ($reldata)
	{
		$rel-&gt;updateGrab($_GET["id"]);
		$users-&gt;addDownloadRequest($uid);
		$users-&gt;incrementGrabs($uid);
		if (isset($_GET["del"]) &amp;&amp; $_GET["del"]==1)
			$users-&gt;delCartByUserAndRelease($_GET["id"], $uid);
	}
	else
		$page-&gt;show404();

	header("Content-type: application/x-nzb");
	header("X-DNZB-Name: ".$reldata["searchname"]);
	header("X-DNZB-Category: ".$reldata["category_name"]);
	header("X-DNZB-MoreInfo: "); //TODO:
	header("X-DNZB-NFO: "); //TODO:
	header("Content-Disposition: attachment; filename=".str_replace(",", "_", str_replace(" ", "_", $reldata["searchname"])).".nzb");

	readgzfile($nzbpath);
}

?&gt;
</pre><div class="extLives"></div></body></html>