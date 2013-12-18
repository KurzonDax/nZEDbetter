<?php
/**
 * Project: nZEDbetter
 * User: Randy
 * Date: 12/15/13
 * Time: 1:33 PM
 * File: imdb.php
 * 
 */
require_once(WWW_DIR . "lib/site.php");
require_once(WWW_DIR . "lib/framework/db.php");
require_once(WWW_DIR . "lib/simple_html_dom.php");


/**
 * Class IMDB
 */
class IMDB
{


    const MOVIES = "s=tt&ttype=ft";
    const TVSHOWS = "&s=tt&ttype=tv";
    const TVEPISODES = "&s=tt&ttype=ep";
    const VIDEOGAMES = "&s=tt&ttype=vg";

    const BASEURL = "http://www.imdb.com/";
    const TITLEPATH = "title/";
    const SEARCHPATH = "find?q=";
    const NAMEPATH = "name/";


    /**
     * @param string    $searchText     Do not URLencode before passing
     * @param string    $searchType     Use IMDB:: constants
     * @param int       $maxResults     Default is 20
     *
     * @return array|bool               Returns result set in array or FALSE
     */
    public function searchIMDB($searchText, $searchType, $maxResults = 20)
    {
        $results = array();
        $url = imdb::BASEURL . imdb::SEARCHPATH . urlencode($searchText) . $searchType;
        $html = new simple_html_dom();
        $html->load($this->__getURL($url));
        if($html)
            $searchResults = $html->find("td[class='result_text']");
        else
        {
            echo "ERROR LOADING ".$url."\n";
            return false;
        }
        if(count($searchResults) > 0)
        {
            $resultsCount = 0;
            if($searchType === IMDB::TVEPISODES)
            {

                foreach ($searchResults as $item)
                {
                    $links = $item->find("a");
                    $tvEpisodeTitle = $links[0]->innertext;
                    preg_match('/\/(tt\d{7})\//', $links[0]->href, $tvEpisodeID);
                    preg_match('/\/(tt\d{7})\//', $links[1]->href, $tvSeriesID);
                    preg_match('/\(((19|20)\d\d)\)\s+\(TV Episode\)/', $item->innertext, $tvEpisodeYear);
                    preg_match('/\(((19|20)\d\d)\)\s+\(TV Series\)/', $item->innertext, $tvSeriesYear);
                    $tvSeriesTitle = $links[1]->innertext;

                    $results[] = array('EpisodeTitle' => $tvEpisodeTitle,
                                       'EpisodeID' => (isset($tvEpisodeID[1]) ? $tvEpisodeID[1] : -1),
                                       'EpisodeYear' => (isset($tvEpisodeYear[1]) ? $tvEpisodeYear[1] : -1),
                                       'SeriesTitle' => $tvSeriesTitle,
                                       'SeriesID' => (isset($tvSeriesID[1]) ? $tvSeriesID[1] : -1),
                                       'SeriesYear' => (isset($tvSeriesYear[1]) ? $tvSeriesYear[1] : -1)
                    );
                    $resultsCount ++;
                    if($resultsCount == $maxResults)
                        break;
                }
            }
            else
            {
                foreach ($searchResults as $item)
                {
                    preg_match('/\/(tt\d{7})\//', $item->innertext, $itemID);
                    preg_match('/\(((19|20)\d\d)\)/', $item->innertext, $year);
                    $titlehtml = $item->find("a");
                    $title = $titlehtml[0]->innertext;
                    $results[] = array('title' => $title, 'id' => (isset($itemID[1]) ? $itemID[1] : -1), 'year' => (isset($year[1]) ? $year[1] : -1));
                    $resultsCount++;
                    if ($resultsCount == $maxResults)
                        break;
                }
            }
        }
        else
            $results = false;

        $html->clear();
        unset($html);

        return $results;
    }

    /**
     * @param string    $imdbID     IMDB ID, with or without the 'tt', will be padded to 7 digits
     *
     * @return array|boolean        Returns associative array with movie info or FALSE
     */
    public function lookupMovie($imdbID)
    {
            preg_match('/(tt)?(?P<id>\d{1,7})/', $imdbID, $match);
            $imdbID = sprintf('tt%07d', $match['id']);


        $url = IMDB::BASEURL . IMDB::TITLEPATH . $imdbID . "/";
        // echo "\nURL: ".$url."\n";
        $html = new simple_html_dom();
        $html->load($this->__getURL($url));

        try
        {
            if(count($html))
            {
                $results = array();

                // IMDB ID

                $results['imdb_id'] = str_ireplace('tt', '', $imdbID);

                // Title
                $movieData = $html->find('span[itemprop=name]');
                $results['title'] = isset($movieData[0]) ? $movieData[0]->innertext : '';
                unset($movieData);

                // Year
                $movieData = $html->find('h1');
                if(isset($movieData[0]))
                {
                    preg_match('/((19|20)\d\d)/', $movieData[0]->innertext, $year);
                    $results['year'] = $year[1];
                }
                else
                    $results['year'] = -1;
                unset($movieData);

                // Image URL
                $movieData = $html->find('td[id="img_primary"] img');
                $results['cover'] = isset($movieData[0]) ? $movieData[0]->src : '';
                unset($movieData);

                // Actors
                $movieData = $html->find('table[class="cast_list"]');
                if(isset($movieData) && is_object($movieData))
                {
                    $actors = $movieData[0]->find('span[itemprop=name]');
                    $results['actors'] = array();
                    foreach ($actors as $actor)
                    {
                        $results['actors'][] = $actor->innertext;
                    }
                }
                unset($movieData);

                // Duration
                $movieData = $html->find("time[itemprop='duration']");
                $results['duration'] = isset($movieData[0]) ? trim(str_ireplace('min', '', $movieData[0]->innertext)) : '-1';
                unset($movieData);

                // Genres
                $movieData = $html->find("span[itemprop='genre']");
                if (isset($movieData[0]))
                {
                    foreach($movieData as $genre)
                    {
                        $results['genres'][] = $genre->innertext;
                    }
                }
                else
                    $results['genres'] = null;
                unset($movieData);

                // Rating
                $movieData = $html->find("span[itemprop='ratingValue']");
                $results['rating'] = isset($movieData[0]) ? $movieData[0]->innertext : '';
                unset($movieData);

                // Short Description
                $movieData = $html->find("meta[name='description']");
                $results['shortDescription'] = isset($movieData[0]) ? trim($movieData[0]->content) : '';
                unset($movieData);

                // Director
                $movieData = $html->find("div[class='txt-block']");
                if(isset($movieData))
                {
                    foreach($movieData as $block)
                    {
                        if($block->itemprop == 'director')
                        {
                            $name = $block->find("span[itemprop='name']");
                            $results['director'] = isset($name[0]) ? $name[0]->innertext : '';
                        }
                    }
                }
                else
                    $results['director'] = '';
                unset($movieData);

                // Description
                $movieData = $html->find("div[itemprop='description']");
                if(isset($movieData[0]))
                {
                    $text = $movieData[0]->innertext;
                    $results['description'] = trim(preg_replace('/<p>|<\/p>|(<em(.+)$)/si', '', $text));
                }
                else
                    $results['description'] = '';
                unset($movieData);

                // Tagline
                $movieData = $html->find("div[class='txt-block']");
                $results['tagline'] = '';
                foreach($movieData as $block)
                {
                    if(preg_match('/<h4 class="inline">Taglines\:<\/h4>/', $block->innertext))
                    {
                        $results['tagline'] = trim(preg_replace('/<h4.+<\/h4>|<span.+<\/span>/si', '', $block->innertext));
                        break;
                    }
                }
                unset($movieData);

                // MPAA Rating
                $movieData = $html->find("span[itemprop='contentRating']");
                foreach($movieData as $block)
                {
                    if(isset($block->content))
                        $results['MPAArating'] = $block->content;
                    else
                        $results['MPAAtext'] = $block->innertext;
                }
                unset($movieData);
            }
            else
                $results = false;
        }
        catch (Exception $e)
        {
            $results = false;
        }
        finally
        {
            $html->clear();
            unset($html);

            return $results;
        }
    }

    /**
     * @param string    $url    MUST be urlencoded
     *
     * @return mixed    returns html
     */
    private function __getURL($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
} 