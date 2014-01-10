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

$searchText = $consoletools->getUserInput("Enter text to search for on Amazon: ");
// $searchText = "Susan Stephens The Accidental Heir Royal Baby Collection";
$obj = new AmazonProductAPI($pubkey, $privkey, $asstag);
try{$result = $obj->getItemByAsin($searchText, "com", "ItemAttributes,Images");}
catch(Exception $e){$result = false;}
//if($result->Items->Item->CustomerReviews->HasReviews == 'true')
//    $rating = $obj->getAmazonCustomerRating($result->Items->Item->CustomerReviews->IFrameURL);

if(isset($result->Items->Item->ImageSets->ImageSet->LargeImage->URL) && !empty($result->Items->Item->ImageSets->ImageSet->LargeImage->URL))
    $imageURL = $result->Items->Item->ImageSets->ImageSet->LargeImage->URL;

if ($result !== false)
{

	echo "\nLooks like it is working alright.\n";
    echo "Image URL: " . $imageURL . "\n";
    // echo "Rating: " . $rating . "\n";
}
else
{
	print_r($e);
	exit("\nThere was a problem attemtping to query amazon. Maybe your keys or wrong, or you are being throttled.\n");
}
exit("\n");
