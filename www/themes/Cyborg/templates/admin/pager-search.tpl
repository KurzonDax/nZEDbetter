{math equation="ceil(x/y)" x=$pagertotalitems y=$pageritemsperpage assign='pages'}
{assign var="currentpage" value=($pageroffset+$pageritemsperpage)/$pageritemsperpage}
{math equation="floor(((x-y)/2)+y)" x=$pages y=$currentpage assign='upperhalfwaypoint'} {* 10 *}
{assign var="counter" value=1}

{if $pages > 1}
    <div class="pull-left" style="margin: 0;">
        <form>
        <ul class="pagination">
            <li {if ($currentpage-1) < 1}class="disabled"{/if}>{if ($currentpage-1) < 1}<a><i class="icon-double-angle-left"></i> Prev</a>{else}<a name="pager-prev" class="pointer" data-offset="{$pageroffset-$pageritemsperpage}"><i class="icon-double-angle-left"></i> Prev</a>{/if}</li>
            {* if $currentpage > 1}
                <li><a name="pager-link" class="pointer" data-offset="0">1</a></li>{/if *}
            {if $pages < 5}
                {while $counter <= $pages}
                    {if $counter == $currentpage}
                        <li class="active"><a>{$currentpage}</a></li>
                    {else}
                        <li><a name="pager-link" class="pointer" data-offset="{$pageritemsperpage*($counter - 1)}">{$counter}</a></li>
                    {/if}
                    {$counter = $counter + 1}
                {/while}
            {/if}
            {if $pages >= 5}
                {if $currentpage > 1}
                    <li><a name="pager-link" class="pointer" data-offset="0">First</a></li>
                {else}
                    <li class="active"><a>First</a></li>
                {/if}
                <li{if $currentpage != 1 && $currentpage != $pages} class="active"{/if}><a class="active" style="padding: 4px 10px 4px;">Go to page: <select name="pagerselect" style="margin-bottom: 0; height: 21px; border: 1px solid #999;">
                        {while $counter <= $pages}
                            <option {if $counter == $currentpage}selected{/if} data-offset="{$pageritemsperpage*($counter - 1)}" value="{$pageritemsperpage*($counter - 1)}">{$counter}</option>
                            {$counter = $counter + 1}
                        {/while}
                </select></a></li>
                {if $currentpage < $pages}
                    <li><a name="pager-link" class="pointer" data-offset="{$pageritemsperpage*($pages - 1)}">Last</a></li>
                {else}
                    <li class="active"><a>Last</a></li>
                {/if}

            {/if}
            <li{if ($currentpage+1) > $pages} class="disabled"{/if}>{if ($currentpage+1) > $pages}<a>Next<i class="icon-double-angle-right"></i></a>{else}<a name="pager-next" class="pointer" data-offset="{$pageroffset+$pageritemsperpage}">Next <i class="icon-double-angle-right"></i></a>{/if}</li>

        </ul>
        </form>
    </div>
{else}
    <ul class="pagination">
        <li class="active"><a>1</a></li>
    </ul>
{/if}