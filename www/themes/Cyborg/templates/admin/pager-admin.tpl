{assign var="pages" value=($pagertotalitems/$pageritemsperpage)|round}
{assign var="currentpage" value=($pageroffset+$pageritemsperpage)/$pageritemsperpage}
{assign var="upperhalfwaypoint" value=((($pages-$currentpage)/2)|round)+$currentpage}

{if $pages > 1}
    <!-- <div class="pagination" style="max-width='500px'; margin: 0px 0px -8px 0px;"> -->
    <div class="pull-left" style="margin: 0;">
    <ul class="pagination">
        <li {if ($currentpage-1) < 1}class="disabled"{/if}>{if ($currentpage-1) < 1}<a><i class="icon-double-angle-left"></i> Prev</a>{else}<a href="{$pagerquerybase}{$pageroffset-$pageritemsperpage}{$pagerquerysuffix}"><i class="icon-double-angle-left"></i> Prev</a>{/if}</li>
        {if $currentpage > 1}
            <li><a href="{$pagerquerybase}0{$pagerquerysuffix}">1</a></li>{/if}

        {if $currentpage > 3}<li class="disabled"><a>...</a></li>{/if}

        {if $currentpage > 2}<li><a href="{$pagerquerybase}{$pageroffset-$pageritemsperpage}{$pagerquerysuffix}">{$currentpage-1}</a></li>{/if}

        <li class="active"><a>{$currentpage}</a></li>

        {if ($currentpage+1) < $pages}<li><a href="{$pagerquerybase}{$pageroffset+$pageritemsperpage}{$pagerquerysuffix}">{$currentpage+1}</a></li>{/if}

        {if ($currentpage+1) < ($pages-1) && ($currentpage+2) < $upperhalfwaypoint}<li class="disabled"><a href="javascript:;">...</a></li>{/if}

        {if $upperhalfwaypoint != $pages && $upperhalfwaypoint != ($currentpage+1)}<li><a href="{$pagerquerybase}{$upperhalfwaypoint*$pageritemsperpage}{$pagerquerysuffix}">{$upperhalfwaypoint}</a></li>{/if}

        {if ($upperhalfwaypoint+1) < $pages}<li class="disabled"><a>...</a></li>{/if}

        {if $pages > $currentpage}<li><a href="{$pagerquerybase}{($pages*$pageritemsperpage)-$pageritemsperpage}{$pagerquerysuffix}">{$pages}</a></li>{/if}
        <li{if ($currentpage+1) > $pages} class="disabled"{/if}>{if ($currentpage+1) > $pages}<a>Next<i class="icon-double-angle-right"></i></a>{else}<a href="{$pagerquerybase}{$pageroffset+$pageritemsperpage}{$pagerquerysuffix}">Next <i class="icon-double-angle-right"></i></a>{/if}</li>

    </ul>

    </div>
{/if}

{*
<div class="pagination">
<ul>
<li><a href="javascript:;">Prev</a></li>
<li class="active">
<a href="javascript:;">1</a>
</li>
<li><a href="javascript:;">2</a></li>
<li><a href="javascript:;">3</a></li>
<li><a href="javascript:;">4</a></li>
<li><a href="javascript:;">Next</a></li>
</ul>
</div>
*}