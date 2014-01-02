{if {$site->adbrowse} != ''}
    <div class="row">
        <div class="container" style="width:500px;">
            <fieldset class="adbanner div-center">
                <legend class="adbanner">Advertisement</legend>
                {$site->adbrowse}
            </fieldset></div></div>
    <br>
{/if}
<script type="text/javascript"
        src="{$smarty.const.WWW_TOP}/themes/{$site->style}/scripts/movies.js"></script>
{if $actorsSearchParams != ''}
    <script type="text/javascript">
        var actorsInit = {$actorsSearchParams};
    </script>
{/if}
<div class="accordion" id="searchtoggle">
    <div class="accordion-group">
        <div class="accordion-heading">
            <div class="row" style="text-align:right;margin-bottom:10px;">
                <div class="pull-left">
                    <a class="accordion-toggle btn btn-mini {if $resultsFiltered==true}btn-success{else}btn-info{/if} collapsed" data-toggle="collapse"
                       data-parent="#searchtoggle" href="#searchfilter"
                       style="height: 25px; padding: 2px; padding-left: 4px; padding-right: 4px;">
                        Advanced Search&nbsp;{if $resultsFiltered==true}(Results Currently Filtered){/if}&nbsp;<i class="icon-chevron-sign-down"></i></a>
                </div>
                <div class="pull-right" style="margin-right: 10px">
                    View:
                    <span><i class="icon-th-list"></i></span>&nbsp;&nbsp;
                    <a href="{$smarty.const.WWW_TOP}/browse?t={*$category[0]*}"><i class="icon-align-justify"></i></a>
                </div>
            </div>
        </div>

        <div id="searchfilter" class="accordion-body collapse">
            <div class="accordion-inner advancedSearch" style="width: 80%;">
                <div class="form-inline">
                    <div class="row col-xs-12" style="margin: 5px;">
                        <span style="width: 48%; display: inline-block; top: -8px; position: relative; margin-bottom: -8px;">
                            <input class="form-control typeahead" id="movietitle" type="text" name="title"
                                   value="{$titleSearchParams}" placeholder="Title">
                        </span>
                        <select class="form-control chosen" style="width: 30%;" id="MPAA" multiple name="MPAA"
                                data-placeholder="MPAA Rating">
                            <option value=""></option>
                            {foreach from=$MPAAratings item=MPAA}
                                <option {if in_array($MPAA, $mpaaSearchParams)}selected="selected"{/if}
                                        value="{$MPAA}">{$MPAA}</option>
                            {/foreach}
                        </select>
                        <select class="form-control chosen" style="width: 19%;" id="rating"
                                name="rating" data-placeholder="Min Rating...">
                            <option value=""></option>
                            {foreach from=$ratings item=rate}
                                <option {if $ratingSearchParams==$rate}selected="selected"{/if} value="{$rate}">{$rate}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="row col-xs-12" style="margin: 5px;">
                        <!-- Width for the movieactors field set in movies.js -->
                        <input class="form-control chosen" id="movieactors" type="hidden" name="actors" {if $actorsSearchParams != ''}value='-1'{/if}>
                        <span style="width: 18%; display: inline-block; top: -8px; position: relative; margin-bottom: -8px; margin-left: -4px;">
                            <input class="form-control typeahead"  id="moviedirector" type="text" name="director"
                                   value="{$directorSearchParams}" placeholder="Director">
                        </span>

                    </div>
                    <div class="row col-xs-12" style="margin: 5px;">


                        <select class="form-control chosen" style="width: 43%;" id="genre" multiple name="genre" data-placeholder="Genres...">
                            <option value=""></option>
                            {foreach from=$genres item=gen}
                                <option {if in_array($gen, $genreSearchParams)}selected="selected"{/if} value="{$gen}">{$gen}</option>
                            {/foreach}
                        </select>
                        <select class="form-control chosen" style="width: 17.5%;" multiple id="year" name="year" data-placeholder="Years...">
                            <option value=""></option>
                            {foreach from=$years item=yr}
                                <option {if in_array($yr, $yearSearchParams)}selected="selected"{/if} value="{$yr}">{$yr}</option>
                            {/foreach}
                        </select>
                        <select class="form-control chosen" style="width: 30.5%;" multiple id="category" name="t" data-placeholder="Categories...">
                            <option value="2000">All Movies</option>
                            {foreach from=$catlist item=ct}
                                <option {if in_array($ct.ID, $categorySearchParams)}selected="selected"{/if} value="{$ct.ID}">{$ct.title}</option>
                            {/foreach}
                        </select>
                        <input id="orderBy" type="hidden" value="{$orderBy}">
                        <button id="btnAdvancedSearch" class="btn btn-success" style="padding: 8px 14px;"><i class="icon-search"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{if $results|@count > 0}

    <form id="nzb_multi_operations_form" action="get">
    <div class="nzb_multi_operations">
        <div class="row" style="text-align:right;margin-bottom:5px;">
            {if $isadmin || $ismod}
                &nbsp;&nbsp;
                Admin: <input type="button" class="btn btn-warning nzb_multi_operations_edit" value="Edit">
                <input type="button" class="btn btn-danger nzb_multi_operations_delete" value="Delete">
            {/if}
        </div>
        {include file='multi-operations.tpl'}
    </div>

    <table class="table table-condensed data highlight icons" id="coverstable">
        <thead>
        <tr>
            <th><input type="checkbox" class="nzb_check_all"></th>
            <th>title <a id="sort-title_desc" title="Sort Descending" href="{$orderbytitle_desc}"><i class="icon-chevron-down icon-black"{if $orderBy=='title_desc'} style="color: #A0E01F"{/if}></i></a>
                <a id="sort-title_asc" title="Sort Ascending" href="{$orderbytitle_asc}"><i class="icon-chevron-up icon-black"{if $orderBy=='title_asc'} style="color: #A0E01F"{/if}></i></a>
            </th>
            <th>year <a id="sort-year_desc" title="Sort Descending" href="{$orderbyyear_desc}"><i class="icon-chevron-down icon-black"{if $orderBy=='year_desc'} style="color: #A0E01F"{/if}></i></a>
                <a id="sort-year_asc" title="Sort Ascending" href="{$orderbyyear_asc}"><i class="icon-chevron-up icon-black"{if $orderBy=='year_asc'} style="color: #A0E01F"{/if}></i></a>
            </th>
            <th>rating <a id="sort-rating_desc" title="Sort Descending" href="{$orderbyrating_desc}"><i class="icon-chevron-down icon-black"{if $orderBy=='rating_desc'} style="color: #A0E01F"{/if}></i></a>
                <a id="sort-rating_asc" title="Sort Ascending" href="{$orderbyrating_asc}"><i class="icon-chevron-up icon-black"{if $orderBy=='rating_asc'} style="color: #A0E01F"{/if}></i></a>
                <span style="padding-left: 15%;">
                    posted <a id="sort-posted_desc" title="Sort Descending" href="{$orderbyposted_desc}"><i class="icon-chevron-down icon-black"{if $orderBy=='posted_desc'} style="color: #A0E01F"{/if}></i></a>
                    <a id="sort-posted_asc" title="Sort Ascending" href="{$orderbyposted_asc}"><i class="icon-chevron-up icon-black"{if $orderBy=='posted_asc'} style="color: #A0E01F"{/if}></i></a>
                </span>
            </th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$results item=result}
            {$result.imdbID = $result.imdbID|string_format:"%07d"}

            <tr>
                <td style="vertical-align: top"><center>
                        <div class="movcover">
                            <a target="_blank" href="{$site->dereferrer_link}{if $result.imdbID > 1}http://www.imdb.com/title/tt{$result.imdbID}/{else}http://www.themoviedb.org/movie/{$result.tmdbID}{/if}" name="name{$result.imdbID}" title="View movie info" class="modal_imdb thumbnail" rel="movie" >
                                <img class="shadow" style="margin: 3px 0;" src="{if $result.cover != '0' && $result.cover != ''}{$smarty.const.WWW_TOP}/covers/movies/{$result.cover}{else}{$smarty.const.WWW_TOP}/themes/{$site->style}/images/movie-no-cover.jpg{/if}" width="160" border="0" alt="{$result.title|escape:"htmlall"}">
                            </a>
                            <div class="relextra" style="margin-top: 10px;"><center>
                                    <span class="label label-inverse"><a target="_blank" href="{$site->dereferrer_link}{if $result.imdbID > 1}http://www.imdb.com/title/tt{$result.imdbID}/{else}http://www.themoviedb.org/movie/{$result.tmdbID}{/if}"
                                                                         name="name{$result.imdbID}" title="View movie info" class="modal_imdb" rel="movie" >Cover</a></span>
                                    <span class="label label-inverse"><a target="_blank" href="{$site->dereferrer_link}{if $result.imdbID > 1}http://www.imdb.com/title/tt{$result.imdbID}/{else}http://www.themoviedb.org/movie/{$result.tmdbID}{/if}"
                                                                         name="imdb{$result.imdbID}" title="View {if $result.imdbID > 1}imdb{else}tmdb{/if} page">{if $result.imdbID > 1}Imdb{else}Tmdb{/if}</a></span>
                                    {* <span class="label label-inverse"><a target="_blank" href="{$site->dereferrer_link}http://trakt.tv/search/imdb?q=tt{$result.imdbID}/" name="trakt{$result.imdbID}" title="View trakt page">Trakt</a></span> *}
                                    {*<span class="label label-inverse"><a target="blackhole" href="#" name="CP{$result.imdbID}" title="Send to Sabnzbd - NYI">Sab</a></span>
                                    <span class="label label-inverse"><a target="blackhole" href="#" name="CP{$result.imdbID}" title="Add to CouchPotato - NYI">CP</a></span>
                                    <span class="label label-inverse"><a target="blackhole" href="{$site->dereferrer_link}{$site->CPurl}/api/{$site->CPapikey}/movie.add/?identifier=tt{$result.imdbID}&title={$result.title}" name="CP{$result.imdbID}" title="Add to CouchPotato">CouchPotato</a></span>*}</center>
                            </div>
                        </div></center>
                </td>
                <td colspan="3" class="left">
                    <h2>{$result.title|stripslashes|escape:"htmlall"} (<a class="title" title="{$result.year}" href="{$smarty.const.WWW_TOP}/movies?year={$result.year}">{$result.year}</a>) {if $result.rating != ''}{$result.rating}/10{/if}
                        {if $result.MPAArating != null && $result.MPAArating !="APPROVED" && $result.MPAArating != "PASSED" &&
                            $result.MPAArating != "GP" && $result.MPAArating != "NOT RATED" && $result.MPAArating != "M" && $result.MPAArating != "X"}
                            <a href="/movies?MPAA%5B%5D={$result.MPAArating}"><img src="{$smarty.const.WWW_TOP}/themes/{$site->style}/images/MPAA/{$result.MPAArating}.png"
                                 height="25px" style="margin-top: -5px;"></a>
                        {elseif $result.MPAArating == "NOT RATED"}
                            <img src="{$smarty.const.WWW_TOP}/themes/{$site->style}/images/MPAA/NR.png"
                                 height="25px" style="margin-top: -5px;">
                        {/if}

                        {* foreach from=$result.languages item=movielanguage}
                            {release_flag($movielanguage, browse)}
                        {/foreach *}</h2>
                    {if $result.tagline != ''}<b>{$result.tagline|stripslashes}</b><br>{/if}
                    {if $result.plot != ''}{$result.plot|stripslashes}<br>{/if}
                    <br>
                    {if $result.MPAAtext != NULL}<b>MPAA Rating:</b>  {$result.MPAAtext}<br>{/if}
                    {if $result.genre != ''}<b>Genre:</b> {$result.genre|stripslashes}<br>{/if}
                    {if $result.director != ''}<b>Director:</b> {$result.director}<br>{/if}
                    {if $result.actors != ''}<b>Starring:</b> {$result.actors}<br>{/if}
                    {if $result.duration != 0}<b>Duration:</b> {$result.duration} minutes<br>{/if}
                    <br>
                    <div class="relextra">
                        <table class="table table-condensed table-hover">
                            {assign var="msplits" value=","|explode:$result.grp_release_id}
                            {assign var="mguid" value=","|explode:$result.grp_release_guid}
                            {assign var="mnfo" value=","|explode:$result.grp_release_nfoID}
                            {assign var="mgrp" value=","|explode:$result.grp_release_grpname}
                            {assign var="mname" value="#"|explode:$result.grp_release_name}
                            {assign var="mpostdate" value=","|explode:$result.grp_release_postdate}
                            {assign var="msize" value=","|explode:$result.grp_release_size}
                            {assign var="mtotalparts" value=","|explode:$result.grp_release_totalparts}
                            {assign var="mcomments" value=","|explode:$result.grp_release_comments}
                            {assign var="mgrabs" value=","|explode:$result.grp_release_grabs}
                            {assign var="mpass" value=","|explode:$result.grp_release_password}
                            {assign var="minnerfiles" value=","|explode:$result.grp_rarinnerfilecount}
                            {assign var="mhaspreview" value=","|explode:$result.grp_haspreview}
                            <tbody>
                            {foreach from=$msplits item=m}
                                <tr id="guid{$mguid[$m@index]}" {if $m@index > 1}class="mlextra"{/if}>
                                    <td style="width: 27px;">
                                        <input id="chk-{$mguid[$m@index]}" type="checkbox" class="nzb_check" value="{$mguid[$m@index]}" data-guid="{$mguid[$m@index]}">
                                    </td>
                                    <td class="name">
                                        <a href="{$smarty.const.WWW_TOP}/details/{$mguid[$m@index]}/{$mname[$m@index]|escape:"htmlall"}"><b>{$mname[$m@index]|escape:"htmlall"}</b></a><br>
                                        <div class="container">
                                            <div class="pull-left">Posted {$mpostdate[$m@index]|timeago},  {$msize[$m@index]|fsize_format:"MB"},  <a title="View file list" href="{$smarty.const.WWW_TOP}/filelist/{$mguid[$m@index]}">{$mtotalparts[$m@index]} files</a>,  <a title="View comments for {$mname[$m@index]|escape:"htmlall"}" href="{$smarty.const.WWW_TOP}/details/{$mguid[$m@index]}/{$mname[$m@index]|escape:"htmlall"}#comments">{$mcomments[$m@index]} cmt{if $mcomments[$m@index] != 1}s{/if}</a>, {$mgrabs[$m@index]} grab{if $mgrabs[$m@index] != 1}s{/if}
                                            </div>
                                            <div class="pull-right">
                                                {if $mnfo[$m@index] > 0}<span class="label"><a href="{$smarty.const.WWW_TOP}/nfo/{$mguid[$m@index]}" title="View NFO file" class="modal_nfo" rel="nfo">NFO</a></span>{/if}
                                                {if $mpass[$m@index] == 1}<span class="label">Passworded</span>{elseif $mpass[$m@index] == 2}<span class="label">Potential Password</span>{/if}
                                                <span class="label"><a href="{$smarty.const.WWW_TOP}/browse?g={$mgrp[$m@index]}" title="Browse releases in {$mgrp[$m@index]|replace:"alt.binaries":"a.b"}">Group</a></span>
                                                {if $mhaspreview[$m@index] == 1 && $userdata.canpreview == 1}<span class="label"><a href="{$smarty.const.WWW_TOP}/covers/preview/{$mguid[$m@index]}_thumb.jpg" name="name{$mguid[$m@index]}" title="Screenshot of {$mname[$m@index]|escape:"htmlall"}" class="modal_prev" rel="preview">Preview</a></span>{/if}
                                                {if $minnerfiles[$m@index] > 0}<span class="label"><a href="#" onclick="return false;" class="mediainfo" title="{$mguid[$m@index]}">Media</a></span>{/if}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="icons" style="width:80px;">
                                        <div class="icon icon_nzb"><a title="Download Nzb" href="{$smarty.const.WWW_TOP}/getnzb/{$mguid[$m@index]}/{$mname[$m@index]|escape:"htmlall"}"></a></div>
                                        {if $sabintegrated}<div class="icon icon_sab" title="Send to my Sabnzbd"></div>{/if}
                                        <div class="icon icon_cart_movie" title="Add to Cart" data-guid="{$mguid[$m@index]}" data-title="{$result.title}"></div>
                                    </td>
                                </tr>
                                {if $m@index == 1 && $m@total > 2}
                                    <tr><td colspan="5"><a class="mlmore" href="#">{$m@total-2} more...</a></td></tr>
                                {/if}
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>

    {if $results|@count > 10}
        <div class="nzb_multi_operations">
            {include file='multi-operations.tpl'}
        </div>
        </form>
    {/if}
{else}
    <div class="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Sorry!</strong> Either some amazon key is wrong, or there is nothing in this section.
    </div>
{/if}
