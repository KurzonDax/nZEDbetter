<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 12/30/13
 * Time: 2:27 PM
 * File: ajax-movies.php
 * 
 */
require_once("config.php");
require_once(WWW_DIR . "/lib/framework/db.php");
$db = new DB();
$suggestions = array();
if (isset($_GET['action']) && $_GET['action'] == 'titles')
{
    $sql = "SELECT DISTINCT(title) FROM movieinfo WHERE 1 ORDER BY title ASC";
    $titlesResult = $db->queryDirect($sql);
    while ($title = $db->fetchAssoc($titlesResult))
    {
        $suggestions[] = $title['title'];
    }
    header('Content-type: application/json');
    echo json_encode($suggestions);
}

if (isset($_GET['action']) && $_GET['action'] == 'directors')
{
    $sql = "SELECT DISTINCT(director) FROM movieinfo WHERE 1 ORDER BY director ASC";
    $directorsResult = $db->queryDirect($sql);
    while ($director = $db->fetchAssoc($directorsResult))
    {
        $suggestions[] = $director['director'];
    }
    header('Content-type: application/json');
    echo json_encode($suggestions);
}

if (isset($_GET['action']) && $_GET['action'] == 'actors')
{
    $sql = "SELECT ID, name FROM movieActors ORDER BY name ASC";
    $actorsResults = $db->queryDirect($sql);
    while ($actor = $db->fetchAssoc($actorsResults))
    {
        $suggestions[$actor['ID']] = $actor['name'];
    }
    header('Content-type: application/json');
    echo json_encode($suggestions);
}