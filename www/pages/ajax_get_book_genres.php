<?php
/**
 * Project: nZEDb
 * User: Randy
 * Date: 9/21/13
 * Time: 8:14 AM
 * File: ajax_get_book_genres.php
 * 
 */

require_once("config.php");
require_once(WWW_DIR."/lib/framework/db.php");
$db = new DB();

if(isset($_GET['type']) && $_GET['type']=='genres')
{
    $sql = "SELECT DISTINCT(genre) FROM bookinfo WHERE 1 ORDER BY genre ASC";
    $genreResult = $db->queryDirect($sql);
    while($genre=$db->fetchAssoc($genreResult))
    {
        $suggestions[] = $genre['genre'];
    }
    header('Content-type: application/json');
    echo json_encode($suggestions);
}

if(isset($_GET['type']) && $_GET['type']=='authors')
{
    $sql = "SELECT DISTINCT(author) FROM bookinfo WHERE 1 ORDER BY author ASC";
    $authorResult = $db->queryDirect($sql);
    while($author=$db->fetchAssoc($authorResult))
    {
        $suggestions[] = $author['author'];
    }
    header('Content-type: application/json');
    echo json_encode($suggestions);
}

if(isset($_GET['type']) && $_GET['type']=='publishers')
{
    $sql = "SELECT DISTINCT(publisher) FROM bookinfo WHERE 1 ORDER BY publisher ASC";
    $publisherResult = $db->queryDirect($sql);
    while($publisher=$db->fetchAssoc($publisherResult))
    {
        $suggestions[] = $publisher['publisher'];
    }
    header('Content-type: application/json');
    echo json_encode($suggestions);
}