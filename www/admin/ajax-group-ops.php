<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 9/26/13
 * Time: 7:41 PM
 * File: ajax-group-ops.php
 * 
 */
require_once("config.php");
require_once(WWW_DIR."/lib/adminpage.php");
require_once(WWW_DIR."/lib/groups.php");
require_once(WWW_DIR."/lib/framework/db.php");

$admin = new AdminPage;
$group = new Groups();

if(isset($_POST['action']) && $_POST['action']=='edit')
{
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
                if(!$db->queryOneRow("SELECT `name` FROM groups WHERE `name`=".$db->escapeString($_POST['name'])))
                {
                    $db->query("UPDATE groups SET name=".$db->escapeString($_POST['name'])." WHERE ID=".$groupID);
                    $displayName = preg_replace('/alt\.binaries/', 'a.b', $_POST['name']);
                    print $displayName;
                }
                else
                {
                    print '#!GROUP EXISTS';
                }
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
            elseif (empty($_POST['files']))
            {
                $db->query("UPDATE groups SET minfilestoformrelease=0 WHERE ID=".$groupID);
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
                    $numSize = trim(preg_replace('/,| ?MB/ig', '', $_POST['size']));
                    $textSize = number_format($numSize,2)." MB";
                    $numSize = $numSize * 1048576;
                }
                elseif(preg_match('/GB/i', $_POST['size']))
                {
                    $numSize = trim(preg_replace('/,|( )?GB/ig', '', $_POST['size']));
                    $textSize = number_format($numSize * 1024,2)." MB";
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
        elseif(isset($_POST['backfill']))
        {
            $db = new DB();
            if(!empty($_POST['backfill']) && is_numeric($_POST['backfill']))
            {
                $db->query("UPDATE groups SET backfill_target=".$_POST['backfill']." WHERE ID=".$groupID);
                print $_POST['backfill'];
            }
            elseif (empty($_POST['backfill']))
            {
                $db->query("UPDATE groups SET backfill_target=0 WHERE ID=".$groupID);
                print $_POST['backfill'];
            }
            else
            {
                $orgValue = $db->queryOneRow("SELECT backfill_target FROM groups WHERE ID=".$groupID);
                print $orgValue['backfill_target'];
            }
        }
        elseif(isset($_POST['group_status']))
        {
            $status = isset($_POST['group_status']) ? (int)$_POST['group_status'] : 0;
            print $group->updateGroupStatus($groupID, $status);
        }
        elseif(isset($_POST['backfill_status']))
        {
            $status = isset($_POST['backfill_status']) ? (int)$_POST['backfill_status'] : 0;
            print $group->updateBackfillStatus($groupID, $status);
        }

    }
    exit;
}
else if(isset($_POST['action']) && !empty($_POST['action']) && !empty($_POST['id']))
{
    foreach($_POST['id'] as $grpID)
        $groupIDs[] = $grpID;
    print $group->multiGroupAction($groupIDs, $_POST['action']);
    exit;
}

if(isset($_POST["checkname"]) && $_POST['checkname'] !='')
{
    $db = new DB();
    if(!$db->queryOneRow("SELECT `name` FROM groups WHERE `name`=".$db->escapeString($_POST['checkname'])))
    {
        print '#!VALID NAME';
    }
    else
    {
        print '#!GROUP EXISTS';
    }
}