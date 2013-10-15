<?php

require_once("config.php");
require_once(WWW_DIR."/lib/adminresults.php");
require_once(WWW_DIR."/lib/groups.php");

$page = new AdminResults();
$groups = new Groups();

$gname = "";
$offset = isset($_POST["offset"]) ? $_POST["offset"] : 0;
$orderBy = (isset($_POST['ob']) && !empty($_POST['ob'])) ? $_POST['ob'] : 'name_ASC';
$totalGroups = $groups->getCount();

if (isset($_POST['groupname']) && !empty($_POST['groupname']))
{
    $gname = $_POST['groupname'];
    $groupcount = $groups->getCount($gname);
    $grouplist = $groups->getRange($offset, ITEMS_PER_PAGE, $gname);
}
elseif (isset($_POST['action']) && $_POST['action'] == 'search')
{
    $searchString = '';
    $releasesString = '';
        foreach($_POST as $param => $value)
        {
            switch ($param)
            {
                case 'name':
                    $searchString .= ' AND name '.$value;
                    break;
                case 'description':
                    $searchString .= ' AND description '.$value;
                    break;
                case 'firstpost':
                    $searchString .= ' AND first_record_postdate '.$value;
                    break;
                case 'lastpost':
                    $searchString .= ' AND last_record_postdate '.$value;
                    break;
                case 'lastupdated':
                    $searchString .= ' AND last_updated '.$value;
                    break;
                case 'active':
                    $searchString .= ' AND active '.$value;
                    break;
                case 'backfill':
                    $searchString .= ' AND backfill '.$value;
                    break;
                case 'releases':
                    $releasesString .= ' HAVING num_releases '.$value;
                    break;
                case 'minFiles':
                    $searchString .= ' AND minfilestoformrelease '.$value;
                    break;
                case 'minSize':
                    $searchString .= ' AND minsizetoformrelease '.$value;
                    break;
                case 'backfilldays':
                    $searchString .= ' AND backfill_target '.$value;
                    break;
                default:
                    $searchString .= '';
            }
        }
    $searchString .= $releasesString;
    $results = $groups->advancedGroupSearch($searchString, $orderBy, $offset, ITEMS_PER_PAGE);
    $grouplist = $results['resultSet'];
    $groupcount = $results['resultCount'];
}
else
{
    $grouplist = $groups->getAll();
    $groupcount = $totalGroups;
}


$page->smarty->assign('groupname',$gname);
$page->smarty->assign('pagertotalitems',$groupcount);
$page->smarty->assign('pageroffset',$offset);
$page->smarty->assign('pageritemsperpage',ITEMS_PER_PAGE);
$page->smarty->assign('totalgroups', $totalGroups);

// Fix the following:
$groupsearch = ($gname != "") ? 'groupname='.$gname.'&amp;' : '';
$page->smarty->assign('pagerquerybase', WWW_TOP."/group-list.php?".$groupsearch."offset=");
$pager = $page->smarty->fetch("pager-search.tpl");
$page->smarty->assign('pager', $pager);

// $grouplist = $groups->getRange($offset, ITEMS_PER_PAGE, $gname); <-- Old search method

$page->smarty->assign('grouplist',$grouplist);

$page->render();

?>
