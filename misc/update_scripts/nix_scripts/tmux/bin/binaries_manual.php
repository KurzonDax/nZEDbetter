<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 8/31/13
 * Time: 9:13 PM
 * File: binaries_manual.php
 *
 */
require_once(dirname(__FILE__)."/../../../config.php");
require_once(WWW_DIR."lib/binaries.php");

$binaries = new Binaries(true);
$binaries->partRepair($nntp=null, $groupArr='', $argv[1], '');