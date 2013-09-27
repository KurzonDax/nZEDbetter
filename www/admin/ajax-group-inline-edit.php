<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 9/26/13
 * Time: 7:41 PM
 * File: ajax-group-inline-edit.php
 * 
 */
require_once("config.php");
require_once(WWW_DIR."/lib/groups.php");
require_once(WWW_DIR."/lib/framework/db.php");

if(isset($_POST['id']) && !empty($_POST['id']))
{
    $groupID = $_POST['id'];
    // Modify the description field
    if(isset($_POST['desc']))
    {
        $db = new DB();
        $db->query("UPDATE groups SET description=".$db->escapeString($_POST['desc'])." WHERE ID=".$groupID);
        if(!empty($_POST['desc']))
        {
            print $_POST['desc'];
        }
    } // Modify the name field
    elseif(isset($_POST['name']))
    {
        $db = new DB();
        if(!empty($_POST['name']))
        {
            $db->query("UPDATE groups SET name=".$db->escapeString($_POST['name'])." WHERE ID=".$groupID);
            $displayName = preg_replace('/alt\.binaries/', 'a.b', $_POST['name']);
            print $displayName;
        }
        else
        {
            $originalName = $db->queryOneRow("SELECT `name` FROM groups WHERE ID=".$groupID);
            $displayName = preg_replace('/alt\.binaries/', 'a.b', $originalName['name']);
            print $displayName;
        }
    } // Modify minimum files
    elseif(isset($_POST['files']))
    {
        $db = new DB();
        if(!empty($_POST['files']) && is_numeric($_POST['files']))
        {
            $db->query("UPDATE groups SET minfilestoformrelease=".$_POST['files']." WHERE ID=".$groupID);
            print $_POST['files'];
        }
        else
        {
            $orgValue = $db->queryOneRow("SELECT minfilestoformrelease FROM groups WHERE ID=".$groupID);
            print $orgValue['minfilestoformrelease'];
        }
    } // Modify minimum file size
    elseif(isset($_POST['size']))
    {
        $db = new DB();

        if(!empty($_POST['size']) && preg_match('/MB|GB/i', $_POST['size']))
        {
            if(preg_match('/MB/i', $_POST['size']))
            {
                $numSize = trim(preg_replace('/ ?MB/i', '', $_POST['size']));
                $textSize = number_format($numSize,2)." MB";
                $numSize = $numSize * 1048576;
            }
            elseif(preg_match('/GB/i', $_POST['size']))
            {
                $numSize = trim(preg_replace('/( )?GB/i', '', $_POST['size']));
                $textSize = number_format($numSize * 1048576,2)." MB";
                $numSize = $numSize * 1073741824;
            }
            $db->query("UPDATE groups SET minsizetoformrelease=".$numSize." WHERE ID=".$groupID);
            print $textSize;
        }
        elseif(empty($_POST['size']) || is_numeric($_POST['size']) || $_POST['size']=="0")
        {
            $db->query("UPDATE groups SET minsizetoformrelease=".$_POST['size']." WHERE ID=".$groupID);
            $textSize = number_format($_POST['size'] / 1048576, 2)." MB";
            print $textSize;
        }
        else
        {
            $orgValue = $db->queryOneRow("SELECT minsizetoformrelease FROM groups WHERE ID=".$groupID);
            $textSize = number_format($orgValue['minsizetoformrelease'] / 1048576, 2)." MB";
            print $textSize;
        }
    }
}
