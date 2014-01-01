<?php
require_once(WWW_DIR."/lib/movie.php");
require_once(WWW_DIR."/lib/category.php");

$movie = new Movie;
$cat = new Category;

if (!$users->isLoggedIn())
    $page->show403();

$resultsFiltered = false;

$moviecats = $cat->getChildren(Category::CAT_PARENT_MOVIE);
$mtmp = array();
foreach($moviecats as $mcat) {
    $mtmp[$mcat['ID']] = $mcat;
}
$mtmp[Category::CAT_PARENT_MOVIE] = "All Movies";
$category = 't%5B%5D=' . Category::CAT_PARENT_MOVIE . '&amp;';
$catarray = array();
if(isset($_GET['t']))
{
    foreach($_GET['t'] as $categoryNumber)
    {
        if(array_key_exists($categoryNumber, $mtmp))
        {
            $category = 't%5B%5D=' . $categoryNumber . '&amp;';
            $catarray[] = $categoryNumber;
            $resultsFiltered = ($categoryNumber != Category::CAT_PARENT_MOVIE);
        }
    }
}
else
    $catarray[] = Category::CAT_PARENT_MOVIE;

$page->smarty->assign('catlist', $mtmp);
$page->smarty->assign('categorySearchParams', $catarray);

$browsecount = $movie->getMovieCount($catarray, -1, $page->userdata["categoryexclusions"]);

$offset = (isset($_GET["offset"]) && ctype_digit($_GET['offset'])) ? $_GET["offset"] : 0;
$ordering = $movie->getMovieOrdering();
$orderby = isset($_GET["ob"]) && in_array($_GET['ob'], $ordering) ? $_GET["ob"] : 'posted_desc';

$results = $movies = array();
$results = $movie->getMovieRange($catarray, $offset, ITEMS_PER_PAGE, $orderby, -1, $page->userdata["categoryexclusions"]);
foreach($results as $result) {
    $result['genre'] = $movie->makeFieldLinks($result, 'genre');
    $result['actors'] = $movie->makeFieldLinks($result, 'actors');
    $result['director'] = $movie->makeFieldLinks($result, 'director');
    $result['languages'] = explode(", ", $result['language']);

    $movies[] = $result;
}

$titleSearchParams = (isset($_GET['title']) && !empty($_GET['title'])) ? stripslashes($_GET['title']) : '';
$browseby_link = 'title=' . urlencode($titleSearchParams) . '&amp;';
$page->smarty->assign('titleSearchParams', stripslashes($titleSearchParams));
if(isset($_GET['title']) && !empty($_GET['title']))
    $resultsFiltered = true;

$actorsSearchParams = array();
if (isset($_GET['actors']))
{
    foreach ($_GET['actors'] as $actorID)
    {
        if(!empty($actorID) && is_numeric($actorID))
        {
            $browseby_link .= 'actors%5B%5D=' . $actorID . '&amp;';
            $actorsSearchParams[] = array( 'id' => $actorID, 'text' => $movie->getActorName($actorID));
            $resultsFiltered = true;

        }
    }
}
$page->smarty->assign('actorsSearchParams', (count($actorsSearchParams) > 0 ? json_encode($actorsSearchParams) : ''));


$directorSearchParams = (isset($_GET['director']) && !empty($_GET['director'])) ? stripslashes($_GET['director']) : '';
$page->smarty->assign('directorSearchParams', $directorSearchParams);
$browseby_link .= 'director=' . urlencode($directorSearchParams) . '&amp;';
if(isset($_GET['director']) && !empty($_GET['director']))
    $resultsFiltered = true;

$ratings = range(1, 9);
rsort($ratings);
$ratingSearchParams = (isset($_GET['rating']) && in_array($_GET['rating'], $ratings)) ? $_GET['rating'] : '';
$page->smarty->assign('ratings', $ratings);
$page->smarty->assign('ratingSearchParams', $ratingSearchParams);
$browseby_link .= 'rating=' . $ratingSearchParams . '&amp;';
if(isset($_GET['rating']) && !empty($_GET['rating']))
    $resultsFiltered = true;

$genres = $movie->getGenres();
$genreSearchParams = array();
if(isset($_GET['genres']))
{
    foreach($_GET['genres'] as $genreString)
    {
        if(!empty($genreString) && in_array($genreString, $genres))
        {
            $genreSearchParams[] = $genreString;
            $browseby_link .= 'genres%5B%5D=' . urlencode($genreString) . '&amp;';
            $resultsFiltered = true;
        }
    }
}
$page->smarty->assign('genres', $genres);
$page->smarty->assign('genreSearchParams', $genreSearchParams);

$years = range(1903, (date("Y")+1));
rsort($years);
$yearSearchParams = array();
if(isset($_GET['years']))
{
    foreach($_GET['years'] as $yearNumber)
    {
        if(!empty($yearNumber) && is_numeric($yearNumber) && in_array($yearNumber, $years))
        {
            $yearSearchParams[] = $yearNumber;
            $browseby_link .= 'years%5B%5D=' . $yearNumber . '&amp;';
            $resultsFiltered = true;
        }
    }
}
$page->smarty->assign('years', $years);
$page->smarty->assign('yearSearchParams', $yearSearchParams);

$mpaaRatings = array('G', 'PG', 'PG-13', 'R', 'NC-17', 'NR', 'TV-Y', 'TV-Y7', 'TV-G', 'TV-14', 'TV-PG', 'TV-MA', 'None');
$mpaaSearchParams = array();
if(isset($_GET['MPAA']))
{
    foreach($_GET['MPAA'] as $mpaaString)
    {
        if(!empty($mpaaString) && in_array($mpaaString, $mpaaRatings))
        {
            $mpaaSearchParams[] = $mpaaString;
            $browseby_link .= 'MPAA%5B%5D=' . $mpaaString . '&amp;';
            $resultsFiltered = true;
        }

    }
}
$page->smarty->assign('mpaaSearchParams', $mpaaSearchParams);

$browseby_link .= 'action=search';

$page->smarty->assign('pagertotalitems',$browsecount);
$page->smarty->assign('pageroffset',$offset);
$page->smarty->assign('pageritemsperpage',ITEMS_PER_PAGE);
$page->smarty->assign('pagerquerybase', WWW_TOP."/movies?".$category.$browseby_link."&amp;ob=".$orderby."&amp;offset=");
$page->smarty->assign('pagerquerysuffix', "#results");
$page->smarty->assign('MPAAratings', $mpaaRatings);
$page->smarty->assign('resultsFiltered', $resultsFiltered);
$page->smarty->assign('orderBy', $orderby);
$pager = $page->smarty->fetch("pager.tpl");
$page->smarty->assign('pager', $pager);

if ($catarray[0] == -1)
    $page->smarty->assign("catname","All");
else
{
    $cat = new Category();
    $cdata = $cat->getById($catarray[0]);
    if ($cdata)
        $page->smarty->assign('catname',$cdata["title"]);
    else
        $page->show404();
}

foreach($ordering as $ordertype)
    $page->smarty->assign('orderby'.$ordertype, WWW_TOP."/movies?".$category.$browseby_link."&amp;ob=".$ordertype."&amp;offset=0");

$page->smarty->assign('results',$movies);
if($$catarray[0]==Category::CAT_PARENT_MOVIE)
    $catname = "All Movies";
else
    $catname = $cat->getTitle($category[0]);
$page->meta_title = "Browse Movies - ".$catname;
$page->meta_keywords = "browse,nzb,description,details,movies,downloads";
$page->meta_description = "Browse for Movie Nzbs";

$page->content = $page->smarty->fetch('movies.tpl');
$page->render();

?>
