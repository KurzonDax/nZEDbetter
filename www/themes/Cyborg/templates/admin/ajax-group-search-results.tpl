
{if $pagertotalitems>0}
<div id="group_list">
    <div class="row" style="margin-right: 0; margin-left: 0" >
        <div id="pagerBlock" class="pull-left">
            {$pager}
        </div>
        <div id="groupCount" class="pull-right">
            {$pagertotalitems} Groups Listed out of {$totalgroups} Total
        </div>
    </div>
    <form id="group_multi_operations_form" action="get">
        <table style="width:100%; clear: both;" class="table table-bordered table-highlight" id="group-list-table">
            <thead>
            <tr>
                <th><div class="icon"><input id="chkSelectAll" type="checkbox" class="group_check_all"></div></th>
                <th style="width: 265px;">Group<br />
                    <i id="name_ASC" class="icon-chevron-up sort-icons"></i>
                    <i id="name_DESC" class="icon-chevron-down sort-icons"></i>
                    <i id="groupHelp" class="icon-question-sign table-help-icon" data-toggle="popover" data-title="Newsgroups"
                       data-content="Newsgroups are where nZEDbetter pulls data from to create releases.
                           You may not specify a group more than once (i.e. all newsgroup names have to be unique). You can double-click on either the group
                           name, or the description, to edit each field. To add a new newsgroup to the database, click the <b>Adds Group</b> button."></i></th>
                <th>First Post<br />
                    <i id="firstPost_ASC" class="icon-chevron-up sort-icons"></i>
                    <i id="firstPost_DESC" class="icon-chevron-down sort-icons"></i></th>
                <th>Last Post<br />
                    <i id="lastPost_ASC" class="icon-chevron-up sort-icons"></i>
                    <i id="lastPost_DESC" class="icon-chevron-down sort-icons"></i></th>
                <th>Last Updated<br />
                    <i id="lastUpdated_ASC" class="icon-chevron-up sort-icons"></i>
                    <i id="lastUpdated_DESC" class="icon-chevron-down sort-icons"></i></th>
                <th style="min-width: 52px">Active<br />
                    <i id="active_ASC" class="icon-chevron-up sort-icons"></i>
                    <i id="active_DESC" class="icon-chevron-down sort-icons"></i></th>
                <th style="min-width: 52px">Backfill<br />
                    <i id="backfill_ASC" class="icon-chevron-up sort-icons"></i>
                    <i id="backfill_DESC" class="icon-chevron-down sort-icons"></i></th>
                <th>Releases<br />
                    <i id="releases_ASC" class="icon-chevron-up sort-icons"></i>
                    <i id="releases_DESC" class="icon-chevron-down sort-icons"></i></th>
                <th style="min-width: 70px;">Min Files<br />
                    <i id="minFiles_ASC" class="icon-chevron-up sort-icons"></i>
                    <i id="minFiles_DESC" class="icon-chevron-down sort-icons"></i>
                    <i class="icon-question-sign table-help-icon" id="minFilesHelp" data-toggle="popover" data-title="Minimum Files"
                       data-content="The Minimum Files value represents the minimum number of binaries required for a collection to be turned
                           into a release.  For example, in a newsgroup that is focused on movies, you might set Minimum Files to 2 (or higher) to
                           help prevent any spam or virus binaries from being inserted in to your database as a release.  However, for an eBook group,
                           you would probably want to set the value to 1 since most eBook binaries are small, and often only have a single file. A setting
                           of 0 will cause nZEDbetter to use the site-wide setting for the group. Double click on the value to edit it."></i></th>
                <th>Min Size<br />
                    <i id="minSize_ASC" class="icon-chevron-up sort-icons"></i>
                    <i id="minSize_DESC" class="icon-chevron-down sort-icons"></i>
                    <i class="icon-question-sign table-help-icon" id="minSizeHelp" data-toggle="popover" data-title="Minimum Size"
                       data-content="The Minimum Size value represents the minimum total size (in bytes) required for a collection to be turned
                           into a release.  For example, in a newsgroup that is focused on movies, you might set Minimum Size to a relatively larger value to
                           help prevent any spam or virus binaries from being inserted in to your database as a release. A setting
                           of 0 will cause nZEDbetter to use the site-wide setting for the group.  Double click on the value to edit it."></i></th>
                <th>Backfill Days<br />
                    <i id="backfillDays_ASC" class="icon-chevron-up sort-icons"></i>
                    <i id="backfillDays_DESC" class="icon-chevron-down sort-icons"></i>
                    <i class="icon-question-sign table-help-icon" id="backfillHelp" data-toggle="popover" data-title="Backfill Days"
                       data-content="The Backfill Days setting determines how far back nZEDbetter will attempt to retrieve header information for the group. Beware
                            that extremely large values for Backfill days will result in many more releases being generated, but at the expense of the time it takes
                            to complete the backfill process for a group.  In addition, extremely large values can have a detrimental impact on performance if you
                            do not have sufficient RAM and are indexing a large number of groups.  Double click on the value to edit it."></i></th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$grouplist item=group}
                {if $group.active == 6} {* Group is in the process of being deleted *}
                    <tr id="disabled-{$group.ID}" class="{cycle values=",alt"}">
                        <td style="width:26px;text-align:center;white-space:nowrap;">
                            <input id="chk-{$group.ID}" type="checkbox" class="group_check" value="{$result.guid}">
                        </td>
                        <td id="name-{$group.name|replace:".":"_"}">
                            <div class="disabled_notice" >Group currently being deleted.</div>
                            <div class="edit_name pointer group-name" id="{$group.ID}" >{$group.name|replace:"alt.binaries":"a.b"}</div>
                            <br />
                            <div class="tablehint edit_desc pointer" style="display: inline-block; margin-right: 20px;" id="{$group.ID}">{$group.description}</div>
                        </td>
                        {elseif $group.active == 7} {* Group is in the process of being reset *}
                    <tr id="disabled-{$group.ID}" class="{cycle values=",alt"}">
                        <td style="width:26px;text-align:center;white-space:nowrap;">
                            <input id="chk-{$group.ID}" type="checkbox" class="group_check" value="{$result.guid}">
                        </td>
                        <td id="name-{$group.name|replace:".":"_"}">
                            <div class="disabled_notice" >Group currently being reset.</div>
                            <div class="edit_name pointer group-name" id="{$group.ID}" >{$group.name|replace:"alt.binaries":"a.b"}</div>
                            <br />
                            <div class="tablehint edit_desc pointer" style="display: inline-block; margin-right: 20px;" id="{$group.ID}">{$group.description}</div>
                        </td>
                        {elseif $group.active == 8} {* Group is in the process of being purged *}
                    <tr id="disabled-{$group.ID}" class="{cycle values=",alt"}">
                        <td style="width:26px;text-align:center;white-space:nowrap;">
                            <input id="chk-{$group.ID}" type="checkbox" class="group_check" value="{$result.guid}">
                        </td>
                        <td id="name-{$group.name|replace:".":"_"}">
                            <div class="disabled_notice">Group currently being purged.</div>
                            <div class="edit_name pointer group-name" id="{$group.ID}" >{$group.name|replace:"alt.binaries":"a.b"}</div>
                            <br />
                            <div class="tablehint edit_desc pointer" style="display: inline-block; margin-right: 20px;" id="{$group.ID}">{$group.description}</div>
                        </td>
                        {else} {* Just a normal group *}
                    <tr id="grouprow-{$group.ID}" class="{cycle values=",alt"}">
                    <td style="width:26px;text-align:center;white-space:nowrap;">
                        <input id="chk-{$group.ID}" type="checkbox" class="group_check" value="{$result.guid}">
                    </td>
                    <td id="name-{$group.name|replace:".":"_"}">
                        <div class="edit_name pointer group-name" id="{$group.ID}" >{$group.name|replace:"alt.binaries":"a.b"}</div>
                        <br />
                        <div class="tablehint edit_desc pointer" style="display: inline-block; margin-right: 20px;" id="{$group.ID}">{$group.description}</div>
                    </td>
                {/if}
                <td class="less" id="dateTime-{$group.ID}-fr" data-date="{$group.first_record_postdate}"></td>
                <td class="less" id="dateTime-{$group.ID}-lr" data-date="{$group.last_record_postdate}"></td>
                <td class="less" id="dateTime-{$group.ID}-lu" data-date="{$group.last_updated}"></td>
                <td class="less" id="group-{$group.ID}">{if $group.active=="1"}<a id="btnDeactivate-{$group.ID}" class="noredtext btn btn-deactivate btn-xs">Deactivate</a>{else}<a id="btnActivate-{$group.ID}" class="noredtext btn btn-activate btn-xs">Activate</a>{/if}</td>
                <td class="less" id="backfill-{$group.ID}">{if $group.backfill=="1"}<a id="btnBackfillDeactivate-{$group.ID}" class="noredtext btn btn-deactivate btn-xs">Deactivate</a>{else}<a id="btnBackfillActivate-{$group.ID}" class="noredtext btn btn-activate btn-xs">Activate</a>{/if}</td>
                <td class="less"><a href="{$smarty.const.WWW_TOP}/../browse?g={$group.name}" title="Browse {$group.name}" >{$group.num_releases}</a></td>
                <td class="less edit_files pointer" id="{$group.ID}">{if $group.minfilestoformrelease==""}0{else}{$group.minfilestoformrelease}{/if}</td>
                <td class="less edit_size pointer" id="{$group.ID}" >{if $group.minsizetoformrelease==""}0.00 MB{else}{$group.minsizetoformrelease|fsize_format:"MB"}{/if}</td>
                <td class="less edit_backfill pointer" id="{$group.ID}" >{$group.backfill_target}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </form>

    <div style="position:relative;margin-top:5px;">
        <div style="float:left">
            {$pager}
        </div>
    </div>

    {else}
    <div id="group_list" class="alert-warning alert-big" style="line-height: 2em">
        <i class=" icon-frown icon-4x icon-alert-big" ></i><b> We're Sorry!</b> No groups matched the criteria you entered.  Please click the Advanced Search button
        to revise your search, or click <a href="group-list.php">here</a> to view the complete list of groups.
    </div>

{/if}
</div>