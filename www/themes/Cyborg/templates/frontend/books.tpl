{if {$site->adbrowse} != ''}
    <div class="row">
        <div class="container" style="width:500px;">
            <fieldset class="adbanner div-center">
                <legend class="adbanner">Advertisement</legend>
                {$site->adbrowse}
            </fieldset></div></div>
    <br>
{/if}

<div class="accordion" id="searchtoggle">
    <div class="accordion-group">
        <div class="accordion-heading">
            <div class="row" style="text-align:right;margin-bottom:10px;">
                <div class="pull-left">
                    <a class="accordion-toggle btn btn-mini btn-info collapsed" data-toggle="collapse" data-parent="#searchtoggle" href="#searchfilter"
                       style="height: 25px; padding: 2px; padding-left: 4px; padding-right: 4px;">
                        Advanced Search&nbsp;<i class="icon-chevron-sign-down"></i></a>
                </div>
                <div class="pull-right" style="margin-right: 10px">
                    View:
                    <span><i class="icon-th-list"></i></span>&nbsp;&nbsp;
                    <a href="{$smarty.const.WWW_TOP}/browse?t={$category}"><i class="icon-align-justify"></i></a>
                </div>
            </div>
        </div>
        <div id="searchfilter" class="accordion-body collapse">
            <div class="accordion-inner advancedSearch">
                <form class="form-inline" name="browseby" action="books" style="margin:0;">
                    <div class="row">
                        <input class="form-control" style="width: 225px;" id="autocomplete-authors" type="text" name="author" value="{$author}" placeholder="Author">
                        <input class="form-control" style="width: 225px;" id="autocomplete-genres" type="text" name="genre" value="{$genre}" placeholder="Genre">
                        <input class="form-control" style="width: 240px;" id="autocomplete-publishers" type="text" name="publisher" value="{$publisher}" placeholder="Publisher">
                    </div>
                    <div class="row" style="margin-top: 5px">
                        <input class="form-control" style="width: 400px;" id="title" type="text" name="title" value="{$title}" placeholder="Title">
                        &nbsp;&nbsp;Minimum Rating: <select class="form-control" style="width: auto;" id="minRating" name="minRating">
                            <option value="-1">All</option>
                            {for $rating=5 to 1 step -1}
                                <option {if $rating==$minRating}selected="selected"{/if} value="{$rating}">{$rating}</option>
                            {/for}
                        </select>
                        <div class="pull-right">
                            <button class="btn btn-success" type="submit" value="Go" style="margin-right: 3px"><i class="icon-search"></i> Search</button>
                        </div>
                    </div>
                </form>
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


        <table class="table table-condensed table-striped data highlight icons" id="coverstable">
            <thead>
            <tr>
                <th style="vertical-align: top"><input type="checkbox" class="nzb_check_all"> Select all on page</th>
                <th>author<br/>
                    <a title="Sort Descending" href="{$orderbyauthor_desc}">
                        <i class="icon-chevron-down icon-black"></i>
                    </a>
                    <a title="Sort Ascending" href="{$orderbyauthor_asc}">
                        <i class="icon-chevron-up icon-black"></i>
                    </a>
                </th>
                <th>genre<br/>
                    <a title="Sort Descending" href="{$orderbygenre_desc}">
                        <i class="icon-chevron-down icon-black"></i>
                    </a>
                    <a title="Sort Ascending" href="{$orderbygenre_asc}">
                        <i class="icon-chevron-up icon-black"></i>
                    </a>
                </th>
                <th>posted<br/>
                    <a title="Sort Descending" href="{$orderbyposted_desc}">
                        <i class="icon-chevron-down icon-black"></i>
                    </a>
                    <a title="Sort Ascending" href="{$orderbyposted_asc}">
                        <i class="icon-chevron-up icon-black"></i>
                    </a>
                </th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$results item=result}
                <tr>
                    <td style="text-align:center">
                        <div class="movcover">
                            <a class="title thumbnail" title="View details" href="{$smarty.const.WWW_TOP}/details/{$result.guid}/{$result.searchname|escape:"htmlall"}">
                                <img class="shadow" src="{$smarty.const.WWW_TOP}{if $result.cover == 1}/covers/book/{$result.bookinfoID}.jpg{else}/themes/{$site->style}/images/book-no-cover.jpg{/if}" width="120" border="0" alt="{$result.title|escape:"htmlall"}" />
                            </a>
                            <div class="relextra" style="margin-top: 10px">
                                {if $result.nfoID > 0}<a href="{$smarty.const.WWW_TOP}/nfo/{$result.guid}" title="View NFO file" class="label modal_nfo label-primary" rel="nfo">NFO</a>{/if}
                                <a class="label label-primary" target="_blank" href="{$site->dereferrer_link}{$result.url}" name="amazon{$result.bookinfoID}" title="View amazon page">Amazon</a>
                                <a class="label label-primary" href="{$smarty.const.WWW_TOP}/browse?g={$result.group_name}" title="Browse releases in {$result.group_name|replace:"alt.binaries":"a.b"}">Group</a>
                            </div>
                        </div>
                    </td>
                    <td colspan="8" class="left" id="guid{$result.guid}">
                        <h2><a class="title" title="View details" href="{$smarty.const.WWW_TOP}/details/{$result.guid}/{$result.searchname|escape:"htmlall"}">{if $result.author != ""}{$result.author|escape:"htmlall"} - {/if}{$result.title|escape:"htmlall"}</a></h2>
                        {if $result.customerRating != "null" && $result.customerRating != ""}<b>Average Rating: </b>
                            {math equation="floor(x)" x=$result.customerRating assign='ratingInt'}
                            {math equation="x-y" x=$result.customerRating y=$ratingInt assign='ratingDec'}
                            {for $stars=1 to $ratingInt}<i class="icon-star" style="color: #2A9FD6"></i>{/for}{if $ratingDec > '.4'}<i class="icon-star-half" style="color: #2A9FD6"></i>{/if} ({$result.customerRating})<br />
                        {/if}
                        {if $result.genre != "null"}<b>Genre:</b> {$result.genre|escape:'htmlall'}<br />{/if}
                        {if $result.publisher != ""}<b>Publisher:</b> {$result.publisher}<br />{/if}
                        {if $result.publishdate != ""}<b>Released:</b> {$result.publishdate|date_format}<br />{/if}
                        {if $result.pages != ""}<b>Pages:</b> {$result.pages}<br />{/if}
                        {if $result.salesrank != ""}<b>Amazon Rank:</b> {$result.salesrank}<br />{/if}
                        {if $result.overview != "null"}<b>Overview:</b> {$result.overview|escape:'htmlall'}<br />{/if}
                        <br />
                        <a class="label label-primary" href="{$smarty.const.WWW_TOP}/books?platform={$result.platform}" title="View similar nzbs">Similar</a>
                        {if $isadmin || $ismod}
                            <a class="label label-warning" href="{$smarty.const.WWW_TOP}/admin/release-edit.php?id={$result.releaseID}&amp;from={$smarty.server.REQUEST_URI|escape:"url"}" title="Edit Release">Edit</a>
                            <a class="label confirm_action label-danger" href="{$smarty.const.WWW_TOP}/admin/release-delete.php?id={$result.releaseID}&amp;from={$smarty.server.REQUEST_URI|escape:"url"}" title="Delete Release">Del</a>
                        {/if}
                        <hr>
                        <div class="relextra" style="margin-bottom: 10px">
                            <b>{$result.searchname|escape:"htmlall"}</b>
                            <br />
                            <b style="margin-left: 10px">Info:</b> {$result.postdate|timeago},  {$result.size|fsize_format:"MB"},  <a title="View file list" href="{$smarty.const.WWW_TOP}/filelist/{$result.guid}">{$result.totalpart} files</a>,  <a title="View comments for {$result.searchname|escape:"htmlall"}" href="{$smarty.const.WWW_TOP}/details/{$result.guid}/{$result.searchname|escape:"htmlall"}#comments">{$result.comments} cmt{if $result.comments != 1}s{/if}</a>, {$result.grabs} grab{if $result.grabs != 1}s{/if}
                            <div class="icon"><input type="checkbox" class="nzb_check" value="{$result.guid}" /></div>
                            <div class="icon icon_nzb"><a title="Download Nzb" href="{$smarty.const.WWW_TOP}/getnzb/{$result.guid}/{$result.searchname|escape:"htmlall"}">&nbsp;</a></div>
                            <div class="icon icon_cart_movie" title="Add to Cart"></div>
                            {if $sabintegrated}<div class="icon icon_sab" title="Send to my Sabnzbd"></div>{/if}

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
        {/if}
    </form>

{else}
    <div class="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Sorry!</strong> Either some amazon key is wrong, or there is nothing in this section.
    </div>
{/if}
