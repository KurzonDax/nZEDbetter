<div id="group_list">
    <script src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/plugins/jquery.jeditable.js"></script>
    <script src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/groups-jq.js"></script>
    <script>
        var www_top = "{$smarty.const.WWW_TOP}";
        var user_style = "{$site->style}";
    </script>

    {if $grouplist}
        <div class="row admin-toolbar">
            <div class="pull-left">
                <form id="frmMultiOps">
                    <button title="Add new groups" class="btn btn-primary btn-small btn-singleOps" id="group-add" data-toggle="modal" data-target="#modalAddGroups" ><a class="" href="javascript:;"><i class="icon-plus"></i> Add Groups</a></button>
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
                            <li><a id="group-Reset" class="pointer">Reset Group(s)...<span style="float: right; font-size: 15px; color: darkred;"><i class="icon-exclamation-sign"></i></span></a></li>
                            <li><a id="group-Purge" class="pointer">Purge Group(s)...<span style="float: right; font-size: 15px; color: darkred;"><i class="icon-warning-sign"></a></i></span></li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="pull-right">
                <form id="groupsearch" style="display: inline-block; vertical-align: middle">
                    <div class="input-group">
                        <input id="searchGroupName" type="text" class="form-control" value="{$groupname}" placeholder="Search for groups by name..." style="width: 225px;height: 30px;padding-bottom: 5px;font-size: 13px;">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="button"><i class="icon-search"></i></button>
                            </span>
                    </div>
                    {* <input id="searchGroupName" class="no-bottom" type="text" name="groupname" value="{$groupname}" size="15" placeholder="Search for groups by name..." style="width: 190px"/>
                    <button class="btn btn-primary btn-small" style="
                        margin-bottom: 5px;
                        padding-bottom: 2px;
                        height: 30px;
                    "><i class="icon-search"></i></button> *}
                </form>
                <a class="accordion-toggle btn btn-advSearch btn-multiops btn-secondary collapsed" data-toggle="collapse" data-parent="#searchtoggle" href="#searchfilter">
                       Advanced Search&nbsp;<i class="icon-chevron-sign-down"></i></a>
            </div>
            <div id="searchfilter" class="collapse group-filter row">
                <form class="form-inline" name="browseby" style="margin:0;">
                    <div class="normal-row row">
                        <input class="form-control" style="width: 225px;" id="autocomplete-authors" type="text" name="author" value="{$author}" placeholder="Author">
                        <input class="form-control" style="width: 225px;" id="autocomplete-genres" type="text" name="genre" value="{$genre}" placeholder="Genre">
                        <input class="form-control" style="width: 240px;" id="autocomplete-publishers" type="text" name="publisher" value="{$publisher}" placeholder="Publisher">
                    </div>
                    <div class="normal-row row" style="margin-top: 5px">
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

        <div id="pagerBlock" class="pull-left">
            {$pager}
        </div>
        <form id="group_multi_operations_form" action="get">
            <table style="width:100%; clear: both;" class="table table-bordered table-highlight" id="group-list-table">
                <thead>
                <tr>
                    <th><div class="icon"><input id="chkSelectAll" type="checkbox" class="group_check_all"></div></th>
                    <th style="width: 265px;">Group<br />
                        <i id="group_asc" class="icon-chevron-down sort-icons"></i><i id="group_desc" class="icon-chevron-up sort-icons"></i>
                        <i id="groupHelp" class="icon-question-sign table-help-icon" data-toggle="popover" data-title="Newsgroups"
                           data-content="Newsgroups are where nZEDbetter pulls data from to create releases.
                           You may not specify a group more than once (i.e. all newsgroup names have to be unique). You can double-click on either the group
                           name, or the description, to edit each field. To add a new newsgroup to the database, click the <b>Adds Group</b> button."></i></th>
                    <th>First Post<br />
                        <i id="first-post_asc" class="icon-chevron-down sort-icons"></i><i id="first-post_desc" class="icon-chevron-up sort-icons"></i></th>
                    <th>Last Post<br />
                        <i id="last-post_asc" class="icon-chevron-down sort-icons"></i><i id="last-post_desc" class="icon-chevron-up sort-icons"></i></th>
                    <th>Last Updated<br />
                        <i id="last-update_asc" class="icon-chevron-down sort-icons"></i><i id="last-update_desc" class="icon-chevron-up sort-icons"></i></th>
                    <th style="min-width: 52px">Active<br />
                        <i id="active_asc" class="icon-chevron-down sort-icons"></i><i id="active_desc" class="icon-chevron-up sort-icons"></i></th>
                    <th style="min-width: 52px">Backfill<br />
                        <i id="backfill_asc" class="icon-chevron-down sort-icons"></i><i id="backfill_desc" class="icon-chevron-up sort-icons"></i></th>
                    <th>Releases<br />
                        <i id="releases_asc" class="icon-chevron-down sort-icons"></i><i id="releases_desc" class="icon-chevron-up sort-icons"></i></th>
                    <th style="min-width: 70px;">Min Files<br />
                        <i id="min-files_asc" class="icon-chevron-down sort-icons"></i><i id="min-files_desc" class="icon-chevron-up sort-icons"></i>
                        <i class="icon-question-sign table-help-icon" id="minFilesHelp" data-toggle="popover" data-title="Minimum Files"
                           data-content="The Minimum Files value represents the minimum number of binaries required for a collection to be turned
                           into a release.  For example, in a newsgroup that is focused on movies, you might set Minimum Files to 2 (or higher) to
                           help prevent any spam or virus binaries from being inserted in to your database as a release.  However, for an eBook group,
                           you would probably want to set the value to 1 since most eBook binaries are small, and often only have a single file. A setting
                           of 0 will cause nZEDbetter to use the site-wide setting for the group. Double click on the value to edit it."></i></th>
                    <th>Min Size<br />
                        <i id="min-size_asc" class="icon-chevron-down sort-icons"></i><i id="min-size_desc" class="icon-chevron-up sort-icons"></i>
                        <i class="icon-question-sign table-help-icon" id="minSizeHelp" data-toggle="popover" data-title="Minimum Size"
                           data-content="The Minimum Size value represents the minimum total size (in bytes) required for a collection to be turned
                           into a release.  For example, in a newsgroup that is focused on movies, you might set Minimum Size to a relatively larger value to
                           help prevent any spam or virus binaries from being inserted in to your database as a release. A setting
                           of 0 will cause nZEDbetter to use the site-wide setting for the group.  Double click on the value to edit it."></i></th>
                    <th>Backfill Days<br />
                        <i id="backfill-days_asc" class="icon-chevron-down sort-icons"></i><i id="backfill-days_desc" class="icon-chevron-up sort-icons"></i>
                        <i class="icon-question-sign table-help-icon" id="backfillHelp" data-toggle="popover" data-title="Backfill Days"
                           data-content="The Backfill Days setting determines how far back nZEDbetter will attempt to retrieve header information for the group. Beware
                            that extremely large values for Backfill days will result in many more releases being generated, but at the expense of the time it takes
                            to complete the backfill process for a group.  In addition, extremely large values can have a detrimental impact on performance if you
                            do not have sufficient RAM and are indexing a large number of groups.  Double click on the value to edit it."></i></th>
                    {* <th>options</th> *}
                </tr>
                </thead>
                <tbody>
                {foreach from=$grouplist item=group}
                    <tr id="grouprow-{$group.ID}" class="{cycle values=",alt"}">
                        <td style="width:26px;text-align:center;white-space:nowrap;">
                            <input id="chk-{$group.ID}" type="checkbox" class="group_check" value="{$result.guid}">
                        </td>
                        <td id="name-{$group.name|replace:".":"_"}">
                            <div class="edit_name pointer group-name" id="{$group.ID}" >{$group.name|replace:"alt.binaries":"a.b"}</div>

                            <br /><div class="tablehint edit_desc pointer" style="display: inline-block; margin-right: 20px;" id="{$group.ID}">{$group.description}</div>
                        </td>
                        <td class="less">{$group.first_record_postdate|timeago}</td>
                        <td class="less">{$group.last_record_postdate|timeago}</td>
                        <td class="less">{$group.last_updated|timeago} ago</td>
                        <td class="less" id="group-{$group.ID}">{if $group.active=="1"}<a id="btnDeactivate-{$group.ID}" class="noredtext btn btn-deactivate btn-xs">Deactivate</a>{else}<a id="btnActivate-{$group.ID}" class="noredtext btn btn-activate btn-xs">Activate</a>{/if}</td>
                        <td class="less" id="backfill-{$group.ID}">{if $group.backfill=="1"}<a id="btnBackfillDeactivate-{$group.ID}" class="noredtext btn btn-deactivate btn-xs">Deactivate</a>{else}<a id="btnBackfillActivate-{$group.ID}" class="noredtext btn btn-activate btn-xs">Activate</a>{/if}</td>
                        <td class="less"><a href="{$smarty.const.WWW_TOP}/../browse?g={$group.name}" title="Browse {$group.name}" >{$group.num_releases}</a></td>
                        <td class="less edit_files pointer" id="{$group.ID}">{if $group.minfilestoformrelease==""}0{else}{$group.minfilestoformrelease}{/if}</td>
                        <td class="less edit_size pointer" id="{$group.ID}" >{if $group.minsizetoformrelease==""}0.00 MB{else}{$group.minsizetoformrelease|fsize_format:"MB"}{/if}</td>
                        <td class="less edit_backfill pointer" id="{$group.ID}" >{$group.backfill_target}</td>
                        {* <td class="less" id="groupdel-{$group.ID}"><a title="Reset this group" href="javascript:ajax_group_reset({$group.ID})" class="group_reset">Reset</a> | <a href="javascript:ajax_group_delete({$group.ID})" class="group_delete">Delete</a> | <a href="javascript:ajax_group_purge({$group.ID})" class="group_purge" onclick="return confirm('Are you sure? This will delete all releases, binaries/parts in the selected group');" >Purge</a></td>*}
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
        <p>No groups available (eg. none have been added).</p>
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
                                        <input id="name" class="long" name="groupName" type="text"/>
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
                                        <input class="input-medium" style="text-align: right;" id="backfill_target" name="groupBackfill_target" type="number" min="0" max="1900" value="0" />
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
                                        <input type="number" id="minfilestoformrelease" name="groupMinfilestoformrelease"  min="0" max="1000" value="0"/>
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
                                        <input type="number" id="minsizetoformrelease" name="groupMinsizetoformrelease" min="0" max="1024" value="0" /><select id="minSizeUnitValue" style="margin-left: 8px;">
                                            <option selected="selected" value="1">Bytes</option>
                                            <option value="1024">KB</option>
                                            <option value="1048576">MB</option>
                                            <option value="107374182400">GB</option>
                                        </select>
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
                                                <input type="radio" name="groupActive" id="groupActiveNo" checked="true" value="0"><i class='icon-ok'></i> No
                                            </label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td><div class="pull-left">Enable Group Backfill:</div><div class="pull-right">
                                            <i id="popBackfill" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Group Backfill"
                                               data-content="The Group Backfill setting determines whether or not nZEDbetter will attempt to retrieve past headers for this group.  Normally, this
                                               should be set to 'Yes'.  However, when you are first initializing your server, it is advised to not enable backfill immediately.  Instead, backfill for
                                               groups should only be enabled once all groups have been activated and their headers are caught up to present.  Setting this option to 'No' will prevent
                                               old headers from being downloaded, but the Active option (see above) will not be affected."></i></div>
                                    </td>
                                    <td>
                                        <div id="groupBackfill" class="btn-group" data-toggle="buttons">
                                            <label class="btn btn-primary" style="z-index: 1000;">
                                                <input type="radio" name="options" id="groupBackfillYes" value="1"> Yes
                                            </label>
                                            <label class="btn btn-primary active" style="z-index: 1000;">
                                                <input type="radio" name="options" id="groupBackfillNo" checked="true" value="0"><i class='icon-ok'></i> No
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
                                               data-content="<strong>RegEx: </strong> Enter any valid <a href='http://www.php.net/manual/en/reference.pcre.pattern.syntax.php' target='_blank'>PCRE regular expression</a>.
                                               You do not need to enter the leading or trailing forward slash.  Regex matches are performed with the case-insensitive flag automatically.<br />
                                               <strong>List: </strong>Enter either a comma separated list of newsgroups, or one newsgroup per line, listing all newsgroups you wish to import."></i></div>
                                    </td>
                                    <td>
                                        <textarea id="bulkList" name="bulkList"></textarea>
                                        <div id="bulkListType" class="btn-group" data-toggle="buttons" style="display: block; margin-top: 10px">
                                            <label class="btn btn-primary" style="z-index: 1000;">
                                                <input type="radio" name="options" id="bulkListRegex" value="1">Regex
                                            </label>
                                            <label class="btn btn-primary active" style="z-index: 1000;">
                                                <input type="radio" name="options" id="bulkListList" checked="true" value="0"><i class='icon-ok'></i> List
                                            </label>
                                        </div>

                                    </td>
                                </tr>

                                <tr>
                                    <td><div class="pull-left">Backfill Days:</div><div class="pull-right">
                                            <i id="popBulkBackfill" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Backfill Days"
                                               data-content="The Backfill Days setting determines how far back nZEDbetter will attempt to retrieve header information for the group. Beware
                                                that extremely large values for Backfill days will result in many more releases being generated, at the expense of a much longer backfill process.
                                                In addition, extremely large values can have a detrimental impact on performance if you
                                                do not have sufficient RAM.  You may leave it at the default (or zero) to use the site-wide setting.
                                                Double click on the slider to manually enter the number of days."></i></div>
                                    </td>
                                    <td>
                                        <input class="input-medium" style="text-align: right;" id="Bulkbackfill_target" name="Bulkbackfill_target" type="number" min="0" max="1900" value="0" />
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
                                        <input type="number" id="Bulkminfilestoformrelease" name="Bulkminfilestoformrelease"  min="0" max="1000" value="0"/>
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
                                        <input type="number" id="Bulkminsizetoformrelease" name="Bulkminsizetoformrelease" min="0" max="1024" value="0" /><select id="BulkminSizeUnitValue" style="margin-left: 8px;">
                                            <option selected="selected" value="1">Bytes</option>
                                            <option value="1024">KB</option>
                                            <option value="1048576">MB</option>
                                            <option value="107374182400">GB</option>
                                        </select>
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
                                                <input type="radio" name="bulkActive" id="bulkActiveYes" value="1"> Yes
                                            </label>
                                            <label class="btn btn-primary active" style="z-index: 1000;">
                                                <input type="radio" name="bulkActive" id="bulkActiveNo" checked="true" value="0"><i class='icon-ok'></i> No
                                            </label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td><div class="pull-left">Enable Backfill for Groups:</div><div class="pull-right">
                                            <i id="popBackfill" class="icon-question-sign table-help-icon-modal" style="color: #2B4E72; font-size: medium;" data-toggle="popover" data-title="Group Backfill"
                                               data-content="The Group Backfill setting determines whether or not nZEDbetter will attempt to retrieve past headers for this group.  Normally, this
                                               should be set to 'Yes'.  However, when you are first initializing your server, it is advised to not enable backfill immediately.  Instead, backfill for
                                               groups should only be enabled once all groups have been activated and their headers are caught up to present.  Setting this option to 'No' will prevent
                                               old headers from being downloaded, but the Active option (see above) will not be affected."></i></div>
                                    </td>
                                    <td>
                                        <div id="bulkBackfill" class="btn-group" data-toggle="buttons">
                                            <label class="btn btn-primary" style="z-index: 1000;">
                                                <input type="radio" name="options" id="bulkBackfillYes" value="1"> Yes
                                            </label>
                                            <label class="btn btn-primary active" style="z-index: 1000;">
                                                <input type="radio" name="options" id="bulkBackfillNo" checked="true" value="0"><i class='icon-ok'></i> No
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
                <button id="btnAddGroupsSave" class="btn btn-primary" data-dismiss="modal">Save changes</button>
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
                    <span style="font-size: 13px; color: #333; line-height: 2.5em">
                        You will not be able to undo this operation. Note that this will only delete the group entry from the database.  This will not
                        affect collections, binaries, parts, or releases that already exist in the database.
                    </span>
                </p>
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
                        You will not be able to undo this operation.  Once you confirm this operation, all collections, binaries, and parts related to these
                        groups will be deleted.  In addition, the First Post, Last Post, and Last Updated fields will be reset to null. You should only proceed
                        if you truly wish to start over with the specified groups.<br /><strong>NOTE:</strong> Releases that already exist will remain in the database.
                    </span>
                </p>
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
