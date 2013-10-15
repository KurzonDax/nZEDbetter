
<script src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/plugins/jquery.jeditable.js"></script>
<script src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/groups-jq.js"></script>
<script>
    var www_top = "{$smarty.const.WWW_TOP}";
    var user_style = "{$site->style}";
</script>


<div class="row admin-toolbar">
    <div class="pull-left">
        <form id="frmMultiOps">
            <button title="Add new groups" class="btn btn-primary btn-small btn-singleOps" id="group-add" data-toggle="modal" data-target="#modalAddGroups" >
                <a class="" href="javascript:;"><i class="icon-plus"></i> Add Groups</a>
            </button>
            <div class="btn-group">
                <button id="btnMultiOps" type="button" class="btn btn-secondary btn-small btn-multiops dropdown-toggle" style="width: auto;" disabled="disabled" data-toggle="dropdown">
                    With Selected Group... <i class="icon-caret-down"></i>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li><a id="group-invertSelection" class="pointer">Invert Selection</a></li>
                    <li class="divider"></li>
                    <li><a id="groupMulti-allActive" class="pointer">Set All Active</a></li>
                    <li><a id="groupMulti-allInactive" class="pointer">Set All Inactive</a></li>
                    <li><a id="groupMulti-toggleActive" class="pointer">Toggle Active Status</a></li>
                    <li class="divider"></li>
                    <li><a id="groupMulti-allBackfillActive" class="pointer">Activate Backfill</a></li>
                    <li><a id="groupMulti-allBackfillInactive" class="pointer">Deactivate Backfill</a></li>
                    <li><a id="groupMulti-toggleBackfill" class="pointer">Toggle Backfill</a></li>
                    <li class="divider"></li>
                    <li><a id="group-Delete" class="pointer">Delete Group(s)...</a></li>
                    <li><a id="group-Reset" class="pointer">Reset Group(s)...<span style="float: right; font-size: 15px; color: darkred;" class="clearfix"><i class="icon-exclamation-sign"></i></span></a></li>
                    <li><a id="group-Purge" class="pointer">Purge Group(s)...<span style="float: right; font-size: 15px; color: darkred;"><i class="icon-warning-sign" class="clearfix"></a></i></span></li>
                </ul>
            </div>
            <button title="Show all groups" class="btn btn-tertiary btn-small btn-singleOps disabled" id="btnShowAllGroups" style="width: auto;">
                <a class="" href="javascript:;"><i class="icon-refresh"></i> Show All Groups</a>
            </button>
        </form>
    </div>
    <div class="pull-right">
        <form id="groupsearch" style="display: inline-block; vertical-align: middle">
            <div class="input-group">
                <input id="searchGroupName" type="text" class="form-control" value="{$groupname}" placeholder="Search for groups by name...">
                            <span class="input-group-btn">
                                <a id="btnGroupSearch" class="btn btn-primary"><i class="icon-search"></i></a>
                            </span>
            </div>
        </form>
        <a id="btnAdvancedSearch" class="btn btn-advSearch btn-multiops btn-secondary" data-toggle="modal" data-target="#modalAdvancedSearch" href="">
            Advanced Search&nbsp;<i class="icon-chevron-sign-down"></i></a>
    </div>

</div>
{if $grouplist}
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

<div class="modal fade" id="modalAddGroups">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3>Add Groups</h3>
            </div>
            <div class="modal-body">
                <div class="tabber" id="tabAddGroups">
                    <div class="tabbertab">
                        <h2><a name="tabNewGroup">Add New Group</a></h2>
                        <form id="frmAddGroup">
                            <table class="table-form">
                                <tr>
                                    <td><div class="pull-left">Name:</div><div class="pull-right">
                                            <i id="popName" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Newsgroup Name"
                                            data-content="Newsgroups are where nZEDbetter pulls data (headers) from to create releases. Each newsgroup is identified by it's name. You may not specify a group
                                            more than once (i.e. all newsgroup names have to be unique).  Contact your Usenet Service Provider for a complete list of the newsgroups they serve."></i></div>
                                    </td>
                                    <td>
                                        <input id="groupName" class="long" name="groupName" type="text"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td><div class="pull-left">Description:</div><div class="pull-right">
                                        <i id="popDescription" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Newsgroup Description"
                                            data-content="The description field is an area to provide a short depiction of the type of content the newsgroup is generally compose of.
                                            This field is optional, and limited to 255 characters."></i></div>
                                    </td>
                                    <td>
                                        <textarea id="description" name="groupDescription" style="height: 50px;"></textarea>
                                    </td>
                                </tr>

                                <tr>
                                    <td><div class="pull-left">Backfill Days:</div><div class="pull-right">
                                            <i id="popBackfill" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Backfill Days"
                                                data-content="The Backfill Days setting determines how far back nZEDbetter will attempt to retrieve header information for the group. Beware
                                                that extremely large values for Backfill days will result in many more releases being generated, at the expense of a much longer backfill process.
                                                In addition, extremely large values can have a detrimental impact on performance if you
                                                do not have sufficient RAM.  You may leave it at the default (or zero) to use the site-wide setting.
                                                Double click on the slider to manually enter the number of days."></i></div>
                                    </td>
                                    <td>
                                        <input class="input-medium" style="text-align: right;" id="backfillTarget" name="groupBackfill_target" type="number" min="0" max="1900" value="0" />
                                    </td>
                                </tr>

                                <tr>
                                    <td><div class="pull-left">Minimum Files to Form Release:</div><div class="pull-right">
                                            <i id="popMinFiles" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Minimum Files"
                                               data-content="The Minimum Files value represents the minimum number of binaries required for a collection to be turned
                                               into a release.  For example, in a newsgroup that is focused on movies, you might set Minimum Files to 2 (or higher) to
                                               help prevent any spam or virus binaries from being inserted in to your database as a release.  However, for an eBook group,
                                               you would probably want to set the value to 1 since most eBook binaries are small, and often only have a single file. A setting
                                               of zero will cause nZEDbetter to use the site-wide setting for the group."></i></div>
                                    </td>
                                    <td>
                                        <input type="number" id="minFiles" name="groupMinfilestoformrelease"  min="0" max="1000" value="0"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td><div class="pull-left">Minimum Size to Form Release:</div><div class="pull-right">
                                            <i id="popMinSize" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Minimum Size"
                                               data-content="The Minimum Size value represents the minimum total size (in bytes) required for a collection to be turned
                                               into a release.  For example, in a newsgroup that is focused on movies, you might set Minimum Size to a relatively larger value to
                                               help prevent any spam or virus binaries from being inserted in to your database as a release. A setting
                                               of zero will cause nZEDbetter to use the site-wide setting for the group."></i></div>
                                    </td>
                                    <td>
                                        <input type="number" id="groupMinSize" name="groupMinSize" min="0" max="1024" value="0" /><select id="minSizeUnitValue" style="margin-left: 8px;">
                                            <option value="1048576" selected>MB</option>
                                            <option value="1073741824">GB</option>
                                        </select>
                                        <input type="hidden" id="minSizeValue" name="groupMinSizeValue" value="0"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td><div class="pull-left">Set Group to Active:</div><div class="pull-right">
                                            <i id="popActive" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Group Active"
                                               data-content="The Group Active setting determines whether or not nZEDbetter will attempt to retrieve headers for this group.  Normally, this
                                               should be set to 'Yes'.  However, when you are first initializing your server, it is advised to not set all groups to active immediately, but
                                               rather in stages over a period of a few days.  Setting this option to 'No' will prevent new headers from being downloaded, but the backfill option
                                               (see below) will not be affected."></i></div>
                                    </td>
                                    <td>
                                        <div id="groupActive" class="btn-group" data-toggle="buttons">
                                            <label class="btn btn-primary" style="z-index: 1000;">
                                                <input type="radio" name="groupActive" id="groupActiveYes" value="1"> Yes
                                            </label>
                                            <label class="btn btn-primary active" style="z-index: 1000;">
                                                <input type="radio" name="groupActive" id="groupActiveNo" checked value="0"><i class='icon-ok'></i> No
                                            </label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td><div class="pull-left">Enable Group Backfill:</div><div class="pull-right">
                                            <i id="popBackfillActivate" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Group Backfill"
                                               data-content="The Group Backfill setting determines whether or not nZEDbetter will attempt to retrieve past headers for this group.  Normally, this
                                               should be set to 'Yes'.  However, when you are first initializing your server, it is advised to not enable backfill immediately.  Instead, backfill for
                                               groups should only be enabled once all groups have been activated and their headers are caught up to present.  Setting this option to 'No' will prevent
                                               old headers from being downloaded, but the Active option (see above) will not be affected."></i></div>
                                    </td>
                                    <td>
                                        <div id="groupBackfill" class="btn-group" data-toggle="buttons">
                                            <label class="btn btn-primary" style="z-index: 1000;">
                                                <input type="radio" name="groupBackfill" id="groupBackfillYes" value="1"> Yes
                                            </label>
                                            <label class="btn btn-primary active" style="z-index: 1000;">
                                                <input type="radio" name="groupBackfill" id="groupBackfillNo" checked value="0"><i class='icon-ok'></i> No
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </form>

                    </div> <!-- tabNewGroup -->
                    <div class="tabbertab">
                        <h2><a name="tabBulkAddGroups">Bulk Add Groups</a></h2>
                        <form id="frmBulkAddGroups">
                            <table class="table-form">
                                <tr>
                                    <td><div class="pull-left">Group List:</div><div class="pull-right">
                                            <i id="popBulkList" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Newsgroup List"
                                               data-content="<strong style='color: #000080;'>RegEx: </strong><br /> Enter any valid <a href='http://www.php.net/manual/en/reference.pcre.pattern.syntax.php' target='_blank'>PCRE regular expression</a>.
                                               You do not need to enter the leading or trailing forward slash.  Regex matches are performed with the case-insensitive flag automatically.<br /><br />
                                               <strong style='color: #000080;'>List: </strong><br />Enter either a comma separated list of newsgroups, or one newsgroup per line, listing all newsgroups you wish to import."></i></div>
                                    </td>
                                    <td>
                                        <textarea id="bulkList" name="bulkList"></textarea>
                                        <div id="bulkListType" class="btn-group" data-toggle="buttons" style="display: block; margin-top: 10px">
                                            <label class="btn btn-primary" style="z-index: 1000;">
                                                <input type="radio" name="bulkListType" id="bulkListRegex" value="1">Regex
                                            </label>
                                            <label class="btn btn-primary active" style="z-index: 1000;">
                                                <input type="radio" name="bulkListType" id="bulkListList" checked value="0"><i class='icon-ok'></i> List
                                            </label>
                                        </div>
                                        <input id="bulkUpdateExistingGroups" type="checkbox" style="margin-left: 15px; margin-top: 8px;">
                                        <label for="bulkUpdateExistingGroups" style="display: inline; vertical-align: text-bottom;"> Update existing groups</label>
                                        <input type="hidden" id="bulkgroupName">
                                    </td>
                                </tr>
                                <tr>
                                    <td><div class="pull-left">Description:</div><div class="pull-right">
                                            <i id="popBulkDescription" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Newsgroup Description"
                                               data-content="The description field is an area to provide a short depiction of the type of content the newsgroup is generally compose of.
                                            This field is optional, and limited to 255 characters."></i></div>
                                    </td>
                                    <td>
                                        <textarea id="bulkdescription" name="groupDescription" style="height: 50px;"></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td><div class="pull-left">Backfill Days:</div><div class="pull-right">
                                            <i id="popBulkBackfillDays" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Backfill Days"
                                               data-content="The Backfill Days setting determines how far back nZEDbetter will attempt to retrieve header information for the group. Beware
                                                that extremely large values for Backfill days will result in many more releases being generated, at the expense of a much longer backfill process.
                                                In addition, extremely large values can have a detrimental impact on performance if you
                                                do not have sufficient RAM.  You may leave it at the default (or zero) to use the site-wide setting.
                                                Double click on the slider to manually enter the number of days."></i></div>
                                    </td>
                                    <td>
                                        <input class="input-medium" style="text-align: right;" id="bulkbackfillTarget" name="Bulkbackfill_target" type="number" min="0" max="1900" value="0" />
                                    </td>
                                </tr>

                                <tr>
                                    <td><div class="pull-left">Minimum Files to Form Release:</div><div class="pull-right">
                                            <i id="popBulkMinFiles" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Minimum Files"
                                               data-content="The Minimum Files value represents the minimum number of binaries required for a collection to be turned
                                               into a release.  For example, in a newsgroup that is focused on movies, you might set Minimum Files to 2 (or higher) to
                                               help prevent any spam or virus binaries from being inserted in to your database as a release.  However, for an eBook group,
                                               you would probably want to set the value to 1 since most eBook binaries are small, and often only have a single file. A setting
                                               of zero will cause nZEDbetter to use the site-wide setting for the group."></i></div>
                                    </td>
                                    <td>
                                        <input type="number" id="bulkminFiles" name="Bulkminfilestoformrelease"  min="0" max="1000" value="0"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td><div class="pull-left">Minimum Size to Form Release:</div><div class="pull-right">
                                            <i id="popBulkMinSize" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Minimum Size"
                                               data-content="The Minimum Size value represents the minimum total size (in bytes) required for a collection to be turned
                                               into a release.  For example, in a newsgroup that is focused on movies, you might set Minimum Size to a relatively larger value to
                                               help prevent any spam or virus binaries from being inserted in to your database as a release. A setting
                                               of zero will cause nZEDbetter to use the site-wide setting for the group."></i></div>
                                    </td>
                                    <td>
                                        <input type="number" id="bulkminSize" name="Bulkminsizetoformrelease" min="0" max="1024" value="0" /><select id="bulkminSizeUnitValue" style="margin-left: 8px;">
                                            <option value="1048576" selected>MB</option>
                                            <option value="1073741824">GB</option>
                                        </select>
                                        <input type="hidden" id="bulkminSizeValue" name="groupMinSizeValue" value="0"/>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><div class="pull-left">Set Groups to Active:</div><div class="pull-right">
                                            <i id="popBulkActive" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Group Active"
                                               data-content="The Group Active setting determines whether or not nZEDbetter will attempt to retrieve headers for this group.  Normally, this
                                               should be set to 'Yes'.  However, when you are first initializing your server, it is advised to not set all groups to active immediately, but
                                               rather in stages over a period of a few days.  Setting this option to 'No' will prevent new headers from being downloaded, but the backfill option
                                               (see below) will not be affected."></i></div>
                                    </td>
                                    <td>
                                        <div id="bulkActive" class="btn-group" data-toggle="buttons">
                                            <label class="btn btn-primary" style="z-index: 1000;">
                                                <input type="radio" name="bulkgroupActive" id="bulkActiveYes" value="1"> Yes
                                            </label>
                                            <label class="btn btn-primary active" style="z-index: 1000;">
                                                <input type="radio" name="bulkgroupActive" id="bulkActiveNo" checked value="0"><i class='icon-ok'></i> No
                                            </label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td><div class="pull-left">Enable Backfill for Groups:</div><div class="pull-right">
                                            <i id="popBulkBackfillActivate" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Group Backfill"
                                               data-content="The Group Backfill setting determines whether or not nZEDbetter will attempt to retrieve past headers for this group.  Normally, this
                                               should be set to 'Yes'.  However, when you are first initializing your server, it is advised to not enable backfill immediately.  Instead, backfill for
                                               groups should only be enabled once all groups have been activated and their headers are caught up to present.  Setting this option to 'No' will prevent
                                               old headers from being downloaded, but the Active option (see above) will not be affected."></i></div>
                                    </td>
                                    <td>
                                        <div id="bulkBackfill" class="btn-group" data-toggle="buttons">
                                            <label class="btn btn-primary" style="z-index: 1000;">
                                                <input type="radio" name="bulkgroupBackfill" id="bulkBackfillYes" value="1"> Yes
                                            </label>
                                            <label class="btn btn-primary active" style="z-index: 1000;">
                                                <input type="radio" name="bulkgroupBackfill" id="bulkBackfillNo" checked value="0"><i class='icon-ok'></i> No
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                        </form>

                    </div> <!-- tabBulkAddGroups -->
                </div> <!-- /#tabber Final Tag -->
            </div> <!-- modal-body Final Tag -->
            <div class="modal-footer">
                <a href="javascript:;" class="btn btn-tertiary" data-dismiss="modal">Close</a>
                <button id="btnAddGroupsSave" class="btn btn-primary" >Save changes</button>
            </div>
        </div> <!-- modal content -->
    </div> <!-- modal dialog -->
</div> <!-- modal main tag -->
<div class="modal fade" id="modalDeleteGroups">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3>Delete Groups</h3>
            </div>
            <div class="modal-body">
                <p class="warning-heading">Please confirm that you wish to delete the following groups.<br />
                    <span style="font-size: 13px; color: #333;">
                        You will not be able to undo this operation. Note that this will only delete the group entry from the database.  Optionally, you can
                        delete collections, binaries, parts, and/or releases in the database associated with the groups.
                    </span>
                </p>
                <form ID="formDeleteGroups">
                    <input type="checkbox" id="chkFormDeleteCollections" style="margin-bottom: 15px;"/><label class="danger" for="chkFormDeleteCollections"> Delete collections, binaries, and parts for each group</label><br />
                    <input type="checkbox" id="chkFormDeleteReleases" style="margin-bottom: 15px;"/><label class="danger" for="chkFormDeleteReleases"> Delete releases for each group</label>
                </form>
                <p id="modalDeleteGroupsList"></p>
            </div> <!-- modal-body Final Tag -->
            <div class="modal-footer">
                <a href="javascript:;" class="btn btn-tertiary" data-dismiss="modal">Close</a>
                <button id="btnConfirmDeleteGroups" class="btn btn-primary" data-dismiss="modal">Confirm Delete</button>
            </div>
        </div> <!-- modal content -->
    </div> <!-- modal dialog -->
</div> <!-- modal main tag -->
<div class="modal fade" id="modalResetGroups">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3>Reset Groups</h3>
            </div>
            <div class="modal-body">
                <p class="warning-heading">Please confirm that you wish to reset the following groups.<br />
                    <span style="font-size: 13px; color: #333;">
                        You will not be able to undo this operation.  Once you confirm this operation, the First Post, Last Post, and Last Updated fields for each group
                        will be reset to null.  Optionally, all collections, binaries, and parts related to these groups will be deleted.  You should only proceed
                        if you truly wish to start over with the specified groups.<br /><strong>NOTE:</strong> Releases that already exist will remain in the database.<br />
                    </span><br />
                </p>
                <form ID="formResetGroups">
                    <input type="checkbox" id="chkDeleteCollections" style="margin-bottom: 15px;"/><label class="danger" for="chkDeleteCollections"> Delete collections, binaries, and parts for each group</label>
                </form>
                <p id="modalResetGroupsList"></p>
            </div> <!-- modal-body Final Tag -->
            <div class="modal-footer">
                <a href="javascript:;" class="btn btn-tertiary" data-dismiss="modal">Close</a>
                <button id="btnConfirmResetGroups" class="btn btn-primary" data-dismiss="modal">Confirm Reset</button>
            </div>
        </div> <!-- modal content -->
    </div> <!-- modal dialog -->
</div> <!-- modal main tag -->
<div class="modal fade" id="modalPurgeGroups">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3>Purge Groups</h3>
            </div>
            <div class="modal-body">
                <p class="warning-heading">Please confirm that you wish to purge the following groups.<br />
                    <span style="font-size: 13px; color: #333;">
                        You will not be able to undo this operation.  Once you confirm this operation, ALL data releated to the group, including collections,
                        binaries, parts, <strong style="color: darkred">AND releases</strong> will be deleted from the database. In addition, the First Post, Last Post, and Last Updated
                        fields will be reset to null. You should only proceed if you truly wish to start over with the specified groups.
                    </span>
                </p>
                <p id="modalPurgeGroupsList"></p>
            </div> <!-- modal-body Final Tag -->
            <div class="modal-footer">
                <a href="javascript:;" class="btn btn-tertiary" data-dismiss="modal">Close</a>
                <button id="btnConfirmPurgeGroups" class="btn btn-primary" data-dismiss="modal">Confirm Purge</button>
            </div>
        </div> <!-- modal content -->
    </div> <!-- modal dialog -->
</div> <!-- modal main tag -->
<div class="modal fade" id="modalAddGroupsSuccess">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3>Adding Groups</h3>
            </div>
            <div id="addGroupsWaiting" class="modal-body">
                <p class="warning-heading" style="border-bottom: none;"><i class="icon-spinner icon-spin icon-large"></i> Attempting to add groups...<br />
                </p>
            </div>
            <div id="addGroupsFinished" class="modal-body hidden">
                <p class="warning-heading">The following groups have been successfully added or updated.<br />
                    <span id="notation" class="hidden" style="font-size: 13px; color: #333;">
                        You will need to refresh the page to view these groups as a part of the group list.
                    </span>
                </p>
                <table style="width:100%; clear: both;" class="table table-bordered table-highlight">
                    <thead>
                    <tr>
                        <th style="width: 20%">Group ID</th>
                        <th style="width: 60%">Name</th>
                        <th style="width: 20%">Status</th>
                    </tr>
                    </thead>
                    <tbody id="modalAddGroupsTable">

                    </tbody>
                </table>
            </div> <!-- modal-body Final Tag -->
            <div id="modalAddGroupsSuccessFooter" class="modal-footer hidden">
                <a id="btnAddGroupsRefresh" href="" class="btn btn-secondary">Refresh Page</a>
                <button id="btnAddGroupsOk" class="btn btn-primary" data-dismiss="modal">Ok</button>
            </div>
        </div> <!-- modal content -->
    </div> <!-- modal dialog -->
</div> <!-- modal main tag -->
<div class="modal fade" id="modalAdvancedSearch">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3>Advanced Search</h3>
            </div>
            <div id="frmAdvancedSearchBody" class="modal-body" style="overflow-y: visible;">
                <form id="frmAdvancedSearch" class="form-horizontal advancedSearchForm" role="form">
                    <fieldset>
                        <!-- Prepended checkbox -->
<!-- Group Name -->     <div class="form-group advancedSearch">
                            <label for="txtAdvancedSearch-Name" class="col-lg-3 control-label" >Group Name:</label>
                            <div class="col-lg-9">
                                <div class="input-group advancedSearch">
                                    <span class="input-group-addon outer">
                                        <div class="chk-squared">
                                            <input type="checkbox" value="None" id="chkAdvancedSearch-Name" name="check" class="hidden"/>
                                            <label for="chkAdvancedSearch-Name"></label>
                                        </div>
                                    </span>
                                    <span class="input-group-addon inner">
                                        <select id="selAdvancedSearch-Name" name="advSearchControl" class="selectpicker advancedSearch" data-width="90px" disabled>
                                            <option>EQUALS</option>
                                            <option selected>LIKE</option>
                                            <option>NOT LIKE</option>
                                            <option>IS NULL</option>
                                        </select>
                                    </span>
                                    <input id="txtAdvancedSearch-Name" type="text" name="advSearchControl" class="form-control" placeholder="Enter either partial or full name" disabled>
                                </div><!-- /input-group -->
                            </div><!--col-lg-9-->
                        </div><!--form-group-->
<!-- Description -->    <div class="form-group advancedSearch">
                            <label for="txtAdvancedSearch-Description" class="col-lg-3 control-label">Description:</label>
                            <div class="col-lg-9">
                                <div class="input-group advancedSearch">
                                    <span class="input-group-addon outer">
                                        <div class="chk-squared">
                                            <input type="checkbox" value="None" id="chkAdvancedSearch-Description" name="check" class="hidden"/>
                                            <label for="chkAdvancedSearch-Description"></label>
                                        </div>
                                    </span>
                                    <span class="input-group-addon inner">
                                        <select id="selAdvancedSearch-Description" name="advSearchControl" class="selectpicker advancedSearch" data-width="90px" disabled>
                                            <option>EQUALS</option>
                                            <option selected>LIKE</option>
                                            <option>NOT LIKE</option>
                                            <option>IS NULL</option>
                                        </select>
                                    </span>
                                    <input id="txtAdvancedSearch-Description" type="text" name="advSearchControl" class="form-control" placeholder="Enter partial or full description" disabled>
                                </div><!-- /input-group -->
                            </div><!--col-lg-9-->
                        </div><!--form-group-->
<!-- First Post -->     <div class="form-group advancedSearch">
                            <label for="datAdvancedSearch-firstPost" class="col-lg-3 control-label">First Post:
                            <i id="popFPDateHelp" class="icon-question-sign help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Relative Dates"
                                                data-content="<strong>Relative Dates:</strong><br />Relative dates must always be preceeded with a dash (hyphen), followed by
                                                the quantity to go back, and finally the unit of measure (d=days, w=weeks, m=months, y=years).<br />Examples:<br />
                                                -10d means go back 10 days from today<br />
                                                -3m would equate to go back 3 months"></i></label>
                            <div class="col-lg-9">
                                <div class="input-group advancedSearch">
                                    <span class="input-group-addon outer">
                                        <div class="chk-squared">
                                            <input type="checkbox" value="None" id="chkAdvancedSearch-firstPost" name="check" class="hidden"/>
                                            <label for="chkAdvancedSearch-firstPost"></label>
                                        </div>
                                    </span>
                                    <span class="input-group-addon inner">
                                        <select id="selAdvancedSearch-firstPost" name="advSearchControl" class="selectpicker advancedSearch" data-width="90px" disabled>
                                            <option selected>EQUALS</option>
                                            <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&gt;=</span>">&gt;=</option>
                                            <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&lt;=</span>">&lt;=</option>
                                            <option>NOT</option>
                                            <option>IS NULL</option>
                                        </select>
                                    </span>
                                    <input id="datAdvancedSearch-firstPost" type="text" name="advSearchControl" class="form-control" disabled placeholder="YYYY-MM-DD or Relative Date" >
                                    <span class="input-group-addon outer">
                                        <button id="calAdvancedSearch-firstPost" type="button" name="advSearchControl" class="icon-calendar trigger btn-calendar" disabled></button>
                                    </span>
                                </div><!-- /input-group -->
                            </div><!--col-lg-9-->
                        </div><!--form-group-->
<!-- Last Post -->      <div class="form-group advancedSearch">
                            <label for="datAdvancedSearch-lastPost" class="col-lg-3 control-label">Last Post:
                            <i id="popLPDateHelp" class="icon-question-sign help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Relative Dates"
                                                data-content="<strong>Relative Dates:</strong><br />Relative dates must always be preceeded with a dash (hyphen), followed by
                                                the quantity to go back, and finally the unit of measure (d = days, w = weeks, m = months, y = years).<br />Examples:<br />
                                                -10d means go back 10 days from today<br />
                                                -3m would equate to go back 3 months"></i></label>
                            <div class="col-lg-9">
                                <div class="input-group advancedSearch">
                                    <span class="input-group-addon outer">
                                        <div class="chk-squared">
                                            <input type="checkbox" value="None" id="chkAdvancedSearch-lastPost" name="check" class="hidden"/>
                                            <label for="chkAdvancedSearch-lastPost"></label>
                                        </div>
                                    </span>
                                    <span class="input-group-addon inner">
                                        <select id="selAdvancedSearch-lastPost" name="advSearchControl" class="selectpicker advancedSearch" data-width="90px" disabled>
                                            <option selected>EQUALS</option>
                                            <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&gt;=</span>">&gt;=</option>
                                            <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&lt;=</span>">&lt;=</option>
                                            <option>NOT</option>
                                            <option>IS NULL</option>
                                        </select>
                                    </span>
                                    <input id="datAdvancedSearch-lastPost" type="text" name="advSearchControl" class="form-control" disabled placeholder="YYYY-MM-DD or Relative Date" >
                                    <span class="input-group-addon outer">
                                        <button id="calAdvancedSearch-lastPost" type="button" name="advSearchControl" class="icon-calendar trigger btn-calendar" disabled></button>
                                    </span>
                                </div><!-- /input-group -->
                            </div><!--col-lg-9-->
                        </div><!--form-group-->
<!-- Last Updated -->   <div class="form-group advancedSearch">
                            <label for="datAdvancedSearch-lastUpdated" class="col-lg-3 control-label" >Last Updated:
                            <i id="popLUDateHelp" class="icon-question-sign help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Relative Dates"
                                                data-content="<strong>Relative Dates:</strong><br />Relative dates must always be preceeded with a dash (hyphen), followed by
                                                the quantity to go back, and finally the unit of measure (d = days, w = weeks, m = months, y = years).<br />Examples:<br />
                                                -10d means go back 10 days from today<br />
                                                -3m would equate to go back 3 months"></i></label>
                            <div class="col-lg-9">
                                <div class="input-group advancedSearch">
                                    <span class="input-group-addon outer">
                                        <div class="chk-squared">
                                            <input type="checkbox" value="None" id="chkAdvancedSearch-lastUpdated" name="check" class="hidden"/>
                                            <label for="chkAdvancedSearch-lastUpdated"></label>
                                        </div>
                                    </span>
                                    <span class="input-group-addon inner">
                                        <select id="selAdvancedSearch-lastUpdated" name="advSearchControl" class="selectpicker advancedSearch" data-width="90px" disabled>
                                            <option selected>EQUALS</option>
                                            <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&gt;=</span>">&gt;=</option>
                                            <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&lt;=</span>">&lt;=</option>
                                            <option>NOT</option>
                                            <option>IS NULL</option>
                                        </select>
                                    </span>
                                    <input id="datAdvancedSearch-lastUpdated" type="text" name="advSearchControl" class="form-control" disabled placeholder="YYYY-MM-DD or Relative Date" >
                                    <span class="input-group-addon outer">
                                        <button id="calAdvancedSearch-lastUpdated" type="button" name="advSearchControl" class="icon-calendar trigger btn-calendar" disabled></button>
                                    </span>
                                </div><!-- /input-group -->
                            </div><!--col-lg-9-->
                        </div><!--form-group-->
<!-- Group Active -->   <div class="form-group advancedSearch">
                            <label for="txtAdvancedSearch-lastUpdated" class="col-lg-3 control-label" style="padding-top: 4px;">Group Active:</label>
                            <div class="col-lg-9">
                                <div id="btnAdvancedSearch-Active" class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-primary" style="z-index: 1000;">
                                        <input type="radio" name="advancedSearchActive" id="advancedSearchActiveYes" value="1"> Yes
                                    </label>
                                    <label class="btn btn-primary" style="z-index: 1000;">
                                        <input type="radio" name="advancedSearchActive" id="advancedSearchActiveNo" value="0"> No
                                    </label>
                                    <label class="btn btn-primary active" style="z-index: 1000;">
                                        <input type="radio" name="advancedSearchActive" id="advancedSearchActiveEither" checked value="-1"><i class='icon-ok'></i> Either
                                    </label>
                                </div>
                            </div><!--col-lg-9-->
                        </div><!--form-group-->
<!-- Group Backfill --> <div class="form-group advancedSearch">
                            <label for="txtAdvancedSearch-lastUpdated" class="col-lg-3 control-label" style="padding-top: 4px;">Group Backfill:</label>
                            <div class="col-lg-9">
                                <div id="btnAdvancedSearch-Backfill" class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-primary" style="z-index: 1000;">
                                        <input type="radio" name="advancedSearchBackfill" id="advancedSearchBackfillYes" value="1"> Yes
                                    </label>
                                    <label class="btn btn-primary" style="z-index: 1000;">
                                        <input type="radio" name="advancedSearchBackfill" id="advancedSearchBackfillNo" value="0"> No
                                    </label>
                                    <label class="btn btn-primary active" style="z-index: 1000;">
                                        <input type="radio" name="advancedSearchBackfill" id="advancedSearchBackfillEither" checked value="-1"><i class='icon-ok'></i> Either
                                    </label>
                                </div>
                            </div><!--col-lg-9-->
                        </div><!--form-group-->
<!-- Releases -->       <div class="form-group advancedSearch">
                            <label for="numAdvancedSearch-Releases" class="col-lg-3 control-label">Releases:</label>
                            <div class="col-lg-9">
                                <div class="input-group advancedSearch">
                                        <span class="input-group-addon outer">
                                            <div class="chk-squared">
                                                <input type="checkbox" value="None" id="chkAdvancedSearch-Releases" name="check" class="hidden"/>
                                                <label for="chkAdvancedSearch-Releases"></label>
                                            </div>
                                        </span>
                                        <span class="input-group-addon inner">
                                            <select id="selAdvancedSearch-Releases" name="advSearchControl" class="selectpicker advancedSearch" data-width="90px" disabled>
                                                <option selected>EQUALS</option>
                                                <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&gt;=</span>">&gt;=</option>
                                                <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&lt;=</span>">&lt;=</option>
                                                <option>NOT</option>
                                                <option>IS NULL</option>
                                            </select>
                                        </span>
                                    <input id="numAdvancedSearch-Releases" type="number" name="advSearchControl" class="form-control" placeholder="Enter number of releases" style="text-align: left;" disabled>
                                </div><!-- /input-group -->
                            </div><!--col-lg-9-->
                        </div><!--form-group-->
<!-- Min Files -->      <div class="form-group advancedSearch">
                            <label for="numAdvancedSearch-minFiles" class="col-lg-3 control-label" >Minimum Files:</label>
                            <div class="col-lg-9">
                                <div class="input-group advancedSearch">
                                        <span class="input-group-addon outer">
                                            <div class="chk-squared">
                                                <input type="checkbox" value="None" id="chkAdvancedSearch-minFiles" name="check" class="hidden"/>
                                                <label for="chkAdvancedSearch-minFiles"></label>
                                            </div>
                                        </span>
                                        <span class="input-group-addon inner">
                                            <select id="selAdvancedSearch-minFiles" name="advSearchControl" class="selectpicker advancedSearch" data-width="90px" disabled>
                                                <option selected>EQUALS</option>
                                                <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&gt;=</span>">&gt;=</option>
                                                <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&lt;=</span>">&lt;=</option>
                                                <option>NOT</option>
                                                <option>IS NULL</option>
                                            </select>
                                        </span>
                                    <input id="numAdvancedSearch-minFiles" type="number" name="advSearchControl" class="form-control" placeholder="Enter minimum number of files" style="text-align: left;" disabled>
                                </div><!-- /input-group -->
                            </div><!--col-lg-9-->
                        </div><!--form-group-->
<!-- Min Size -->       <div class="form-group advancedSearch">
                            <label for="numAdvancedSearch-minSize" class="col-lg-3 control-label" style="padding-top: 0px;">Minimum File Size:</label>
                            <div class="col-lg-9">
                                <div class="input-group advancedSearch">
                                    <span class="input-group-addon outer">
                                        <div class="chk-squared">
                                            <input type="checkbox" value="None" id="chkAdvancedSearch-minSize" name="check" class="hidden"/>
                                            <label for="chkAdvancedSearch-minSize"></label>
                                        </div>
                                    </span>
                                    <span class="input-group-addon inner">
                                        <select id="selAdvancedSearch-minSize" name="advSearchControl" class="selectpicker advancedSearch" data-width="90px" disabled>
                                            <option selected>EQUALS</option>
                                            <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&gt;=</span>">&gt;=</option>
                                            <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&lt;=</span>">&lt;=</option>
                                            <option>NOT</option>
                                            <option>IS NULL</option>
                                        </select>
                                    </span>
                                    <input id="specialAdvancedSearch-minSize" type="number" name="advSearchControl" class="form-control" placeholder="Enter file size" style="text-align: left;" disabled>
                                    <span class="input-group-addon outer" style="border-top-right-radius: 5px; border-bottom-right-radius: 5px; margin-left: 0;">
                                        <select id="selAdvancedSearch-minSizeUnitValue"  name="advSearchControl" class="selectpicker advancedSearch" data-width="90px" disabled>
                                            <option value="1048576" selected>MB</option>
                                            <option value="1073741824">GB</option>
                                        </select>
                                    </span>
                                    <input type="hidden" id="numAdvancedSearch-minSize">
                                </div><!-- /input-group -->
                            </div><!--col-lg-9-->
                        </div><!--form-group-->
<!-- Backfill Days -->  <div class="form-group advancedSearch">
                            <label for="numAdvancedSearch-BackfillDays" class="col-lg-3 control-label" >Backfill Days:</label>
                            <div class="col-lg-9">
                                <div class="input-group advancedSearch">
                                    <span class="input-group-addon outer">
                                        <div class="chk-squared">
                                            <input type="checkbox" value="None" id="chkAdvancedSearch-BackfillDays" name="check" class="hidden"/>
                                            <label for="chkAdvancedSearch-BackfillDays"></label>
                                        </div>
                                    </span>
                                    <span class="input-group-addon inner">
                                        <select id="selAdvancedSearch-BackfillDays" name="advSearchControl" class="selectpicker advancedSearch" data-width="90px" disabled>
                                            <option selected>EQUALS</option>
                                            <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&gt;=</span>">&gt;=</option>
                                            <option data-content="<span style='font-size: 18px; line-height: 0em; font-weight: 500; font-family: Consolas,sans-serif;'>&lt;=</span>">&lt;=</option>
                                            <option>NOT</option>
                                            <option>IS NULL</option>
                                        </select>
                                    </span>
                                    <input id="numAdvancedSearch-BackfillDays" type="number" name="advSearchControl" class="form-control" placeholder="Enter number of days to backfill" style="text-align: left;" disabled>
                                </div><!-- /input-group -->
                            </div><!--col-lg-9-->
                        </div> <!--form-group-->
                    <div id="hidden_fields" class="hidden">
                        <input type="hidden" id="order_by" value="name_ASC">
                        <input type="hidden" id="current_offset" value="0">
                    </div>
                    </fieldset>
                </form>
            </div> <!-- modal-body Final Tag -->
            <div class="modal-footer">
                <button id="btnResetAdvancedSearch" class="btn btn-secondary pull-left" disabled>Reset Form</button>
                <button class="btn btn-tertiary" data-dismiss="modal">Cancel</button>
                <button id="btnAdvancedSearchOk" class="btn btn-primary"><i class="icon-search"></i> Search</button>
            </div>
        </div> <!-- modal content -->
    </div> <!-- modal dialog -->
</div> <!-- modal main tag -->
