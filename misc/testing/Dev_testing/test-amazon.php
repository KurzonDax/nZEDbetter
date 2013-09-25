<?php
define('FS_ROOT', realpath(dirname(__FILE__)));
require_once(FS_ROOT."/../../../www/config.php");
require_once(FS_ROOT."/../../../www/lib/amazon.php");
require_once(FS_ROOT."/../../../www/lib/site.php");
require_once(FS_ROOT."/../../../www/lib/consoletools.php");

$s = new Sites();
$site = $s->get();
$pubkey = $site->amazonpubkey;
$privkey = $site->amazonprivkey;
$asstag = $site->amazonassociatetag;
$consoletools = new ConsoleTools();

// $searchText = $consoletools->getUserInput("Enter text to search for on Amazon: ");
$searchText = "Susan Stephens The Accidental Heir Royal Baby Collection";
$obj = new AmazonProductAPI($pubkey, $privkey, $asstag);
try{$result = $obj->searchProducts($searchText, AmazonProductAPI::BOOKS, "TITLE");}
catch(Exception $e){$result = false;}
if($result->Items->Item->CustomerReviews->HasReviews == 'true')
    $rating = $obj->getAmazonCustomerRating($result->Items->Item->CustomerReviews->IFrameURL);

if ($result !== false)
{
	print_r($result);
	exit("\nLooks like it is working alright.\nThe average customer rating is: ".(isset($rating) ? $rating : "not available")."\n");
}
else
{
	print_r($e);
	exit("\nThere was a problem attemtping to query amazon. Maybe your keys or wrong, or you are being throttled.\n");
}
?>
