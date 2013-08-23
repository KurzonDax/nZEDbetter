<?php
//To troubleshoot what's actually on usenet.
require("../../../www/config.php");
require_once(WWW_DIR."/lib/nntp.php");
require_once(WWW_DIR."/lib/consoletools.php");

$consoletools = new ConsoleTools();
$groupname = "";


START:
$getgroup = $consoletools->getUserInput("Please enter a group name: [".$groupname."]: ");
if (!$getgroup == "")
    $groupname = $getgroup;
$whichone = $consoletools->getUserInput("Press 1 to input a range of headers, or press 2 for a single message [2]: ");
if ($whichone == 2 or $whichone == "")
    $return = getSingleMessage($groupname);
else if ($whichone == 1)
    $return = getRangeOfHeaders($groupname);
else
{
    echo "\n Please enter a valid option...\n";
    goto START;
}
$goagain = $consoletools->getUserInput("Press enter to get another message or range of messages, or press ctrl-C to exit :");
if ($goagain == "")
    goto START;
else
    exit ("\nThank you for playing! \n");

function getRangeOfHeaders($groupname)
{
    $consoletools = new ConsoleTools();
    $lownumber = $consoletools->getUserInput("Enter the first message number: ");
    $highnumber = $consoletools->getUserInput("Enter the second message number: ");
    $nntp = new Nntp();
    $nntp->doConnect();
    $groupArr = $nntp->selectGroup($groupname); //since local we need the groupname here
    if(is_numeric($lownumber) && is_numeric($highnumber))
    {
        $msg = $nntp->getOverview($lownumber.'-'.$highnumber,true,false); //insert actual local part numbers here
        print_r($msg); //print out the array
        $nntp->doQuit();
        return true;
    }
    else return false;

}

function getSingleMessage($groupname)
{
    $consoletools = new ConsoleTools();
    $lownumber = $consoletools->getUserInput("Enter the message number to retrieve: ");
    $nntp = new Nntp();
    $nntp->doConnect();
    $groupArr = $nntp->selectGroup($groupname); //since local we need the groupname here
    if(is_numeric($lownumber))
    {
        $msg = $nntp->getHeader($lownumber);
        // $msg = $nntp->getXOverview($lownumber.'-'.$lownumber,true,false); //insert actual local part numbers here
        print_r($msg); //print out the array
        // echo $msg."\n";
        $nntp->doQuit();
        return true;
    }
    else return false;

}
