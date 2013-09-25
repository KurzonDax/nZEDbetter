<?php
require_once(WWW_DIR."/lib/books.php");
require_once(WWW_DIR."/lib/category.php");

$book = new Books;
$cat = new Category;

if (!$users->isLoggedIn())
	$page->show403();


$boocats = $cat->getChildren(Category::CAT_PARENT_BOOKS);
$btmp = array();
foreach($boocats as $bcat) {
	$btmp[$bcat['ID']] = $bcat;
}
$category = Category::CAT_PARENT_BOOKS;
if (isset($_REQUEST["t"]) && array_key_exists($_REQUEST['t'], $btmp))
	$category = $_REQUEST["t"] + 0;
	
$catarray = array();
$catarray[] = $category;	

$page->smarty->assign('catlist', $btmp);
$page->smarty->assign('category', $category);

$browsecount = $book->getBookCount($catarray, -1, $page->userdata["categoryexclusions"]);

$offset = (isset($_REQUEST["offset"]) && ctype_digit($_REQUEST['offset'])) ? $_REQUEST["offset"] : 0;
$ordering = $book->getBookOrdering();
$orderby = isset($_REQUEST["ob"]) && in_array($_REQUEST['ob'], $ordering) ? $_REQUEST["ob"] : '';

$results = $books = array();
$results = $book->getBookRange($catarray, $offset, ITEMS_PER_PAGE, $orderby, -1, $page->userdata["categoryexclusions"]);

$maxwords = 75;
foreach($results as $result) {	
	if (!empty($result['overview'])) {
		$words = explode(' ', $result['overview']);
		if (sizeof($words) > $maxwords) {
			$newwords = array_slice($words, 0, $maxwords);
			$result['overview'] = implode(' ', $newwords).'...';	
		}
	}
	$books[] = $result;
}

$author = (isset($_REQUEST['author']) && !empty($_REQUEST['author'])) ? stripslashes($_REQUEST['author']) : '';
$page->smarty->assign('author', $author);

$title = (isset($_REQUEST['title']) && !empty($_REQUEST['title'])) ? stripslashes($_REQUEST['title']) : '';
$page->smarty->assign('title', $title);

$genre = (isset($_REQUEST['genre']) && !empty($_REQUEST['genre'])) ? stripslashes($_REQUEST['genre']) : '';
$page->smarty->assign('genre', $genre);

$publisher = (isset($_REQUEST['publisher']) && !empty($_REQUEST['publisher'])) ? stripslashes($_REQUEST['publisher']) : '';
$page->smarty->assign('publisher', $publisher);

$minRating = (isset($_REQUEST['minRating']) && !empty($_REQUEST['minRating'])) ? stripslashes($_REQUEST['minRating']) : '';
$page->smarty->assign('minRating', $minRating);

$browseby_link = '&amp;title='.$title.'&amp;author='.$author.'&amp;genre='.$genre.'&amp;publisher='.$publisher.'&amp;minRating='.$minRating;

$page->smarty->assign('pagertotalitems',$browsecount);
$page->smarty->assign('pageroffset',$offset);
$page->smarty->assign('pageritemsperpage',ITEMS_PER_PAGE);
$page->smarty->assign('pagerquerybase', WWW_TOP."/books?t=".$category.$browseby_link."&amp;ob=".$orderby."&amp;offset=");
$page->smarty->assign('pagerquerysuffix', "#results");

$pager = $page->smarty->fetch("pager.tpl");
$page->smarty->assign('pager', $pager);

if ($category == -1)
	$page->smarty->assign("catname","All");			
else
{
	$cat = new Category();
	$cdata = $cat->getById($category);
	if ($cdata)
		$page->smarty->assign('catname',$cdata["title"]);			
	else
		$page->show404();
}

foreach($ordering as $ordertype) 
	$page->smarty->assign('orderby'.$ordertype, WWW_TOP."/books?t=".$category.$browseby_link."&amp;ob=".$ordertype."&amp;offset=0");

$page->smarty->assign('results',$books);
if($category==Category::CAT_PARENT_BOOKS)
    $catname = "All Books";
else
    $catname = $cat->getTitle($category);
$page->meta_title = "Browse Books - ".$catname;
$page->meta_keywords = "browse,nzb,books,description,details";
$page->meta_description = "Browse for Books";
$page->smarty->assign('parentCat','books');
$page->content = $page->smarty->fetch('books.tpl');
$page->render();

?>
