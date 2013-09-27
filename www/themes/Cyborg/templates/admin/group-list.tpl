<div id="group_list">
    <script src="{$smarty.const.WWW_TOP}/../themes/{$site->style}/scripts/jquery.jeditable.js"></script>

        <script>
            var www_top = "{$smarty.const.WWW_TOP}";
            var user_style = "{$site->style}";
            {literal}
            $(function() {

                $(".edit_desc").editable("ajax-group-inline-edit.php", {
                    indicator   : '<img src="'+www_top+'/../themes/'+user_style+'/images/indicator.gif">',
                    type        : "textarea",
                    submit      : "<i class='icon-ok btn-success btn-mini' style='font-size: 15px; padding-top: 2px; padding-bottom: 2px;'></i>",
                    cancel      : "<i class='icon-remove btn-danger btn-mini' style='font-size: 15px; padding-top: 2px; padding-bottom: 2px;'></i>",
                    style       : "width: 200px; display: inline-table; line-height: 29px; font-size: 12px; margin-bottom: 0; margin-top: 5px;",
                    name        : "desc",
                    id          : "id",
                    method      : "POST",
                    event       : "dblclick",
                    placeholder : "<div style='color:#CFCFCF'>Double click to add description</div>",
                    tooltip     : "Double click to edit description",
                    rows        : "0",
                    cols        : "0"
                });

                $(".edit_name").editable("ajax-group-inline-edit.php", {
                    indicator   : '<img src="'+www_top+'/../themes/'+user_style+'/images/indicator.gif">',
                    type        : "text",
                    submit      : "<i class='icon-ok btn-success btn-mini' style='font-size: 15px; padding-top: 2px; padding-bottom: 2px;'></i>",
                    cancel      : "<i class='icon-remove btn-danger btn-mini' style='font-size: 15px; padding-top: 2px; padding-bottom: 2px;'></i>",
                    style       : "width: 200px; display: inline-table; line-height: 29px; font-size: 12px;",
                    name        : "name",
                    id          : "id",
                    method      : "POST",
                    event       : "dblclick",
                    placeholder : "<div style='color:#CFCFCF'>Double click to add group name</div>",
                    tooltip     : "Double click to edit group name"
                });

                $(".edit_files").editable("ajax-group-inline-edit.php", {
                    indicator   : '<img src="'+www_top+'/../themes/'+user_style+'/images/indicator.gif">',
                    type        : "text",
                    submit      : "<i class='icon-ok btn-success btn-mini' style='font-size: 15px; padding-top: 2px; padding-bottom: 2px;'></i>",
                    cancel      : "<i class='icon-remove btn-danger btn-mini' style='font-size: 15px; padding-top: 2px; padding-bottom: 2px;'></i>",
                    style       : "width: 40px; display: inline-table; line-height: 29px; font-size: 12px; margin-bottom: 0; margin-top: 5px;",
                    name        : "files",
                    id          : "id",
                    method      : "POST",
                    event       : "dblclick",
                    tooltip     : "Double click to edit minimum files"
                });

                $(".edit_size").editable("ajax-group-inline-edit.php", {
                    indicator   : '<img src="'+www_top+'/../themes/'+user_style+'/images/indicator.gif">',
                    type        : "text",
                    submit      : "<i class='icon-ok btn-success btn-mini' style='font-size: 15px; padding-top: 2px; padding-bottom: 2px;'></i>",
                    cancel      : "<i class='icon-remove btn-danger btn-mini' style='font-size: 15px; padding-top: 2px; padding-bottom: 2px;'></i>",
                    style       : "width: 40px; display: inline-table; line-height: 29px; font-size: 12px; margin-bottom: 0; margin-top: 5px;",
                    name        : "size",
                    id          : "id",
                    method      : "POST",
                    event       : "dblclick",
                    tooltip     : "Double click to edit minimum size",
                    data        : function (e) { return (e.replace(/,| MB/g, '') * 1048576); }
                });

                $(".edit_backfill").editable("ajax-group-inline-edit.php", {
                    indicator   : '<img src="'+www_top+'/../themes/'+user_style+'/images/indicator.gif">',
                    type        : "text",
                    submit      : "<i class='icon-ok btn-success btn-mini' style='font-size: 15px; padding-top: 2px; padding-bottom: 2px;'></i>",
                    cancel      : "<i class='icon-remove btn-danger btn-mini' style='font-size: 15px; padding-top: 2px; padding-bottom: 2px;'></i>",
                    style       : "width: 40px; display: inline-table; line-height: 29px; font-size: 12px; margin-bottom: 0; margin-top: 5px;",
                    name        : "backfill",
                    id          : "id",
                    method      : "POST",
                    event       : "dblclick",
                    tooltip     : "Double click to edit target backfill days"
                });
            });
            {/literal}
        </script>
    <h1>{$page->title}</h1>

    <p>
        Below is a list of all usenet newsgroups available to be indexed. Click 'Activate' to start indexing a group. Backfill works independently of active.
    </p>

    {if $grouplist}

        <table style="width: 100%">
            <tr style="width: 100%">
                <td style="width: 30%; vertical-align:bottom;">
                    <div style="float:left; margin-bottom: 5px;">
                        {$pager}
                    </div>
                </td>

                <td style="width: 60%; vertical-align:bottom; text-align: center;">
                    <div style="margin-bottom: 5px">
                        <a href="{$smarty.const.WWW_TOP}/group-list-active.php" class="btn btn-success btn-small" style="margin-left: 4px"><i class="icon-ok"></i> Active Groups</a>
                        <a href="{$smarty.const.WWW_TOP}/group-list-inactive.php" class="btn btn-inverse btn-small" style="margin-left: 4px"><i class="icon-minus-sign"></i> Inactive Groups</a>
                        <a href="{$smarty.const.WWW_TOP}/group-list.php" class="btn btn-primary btn-small" style="margin-left: 4px"><i class="icon-star" ></i> All Groups</a>
                    </div>
                </td>
                <td style="width: 30%; min-width: 250px">
                    <form name="groupsearch" action="" style="margin-bottom:5px; float: right">
                        <input id="groupname" type="text" name="groupname" value="{$groupname}" size="15" placeholder="Search for groups..." style="width: 190px"/>
                        <button class="btn btn-primary btn-small" style="
                            margin-bottom: 0px;
                            padding-bottom: 2px;
                            height: 30px;
                        "><i class="icon-search"></i></button>

                    </form>
                </td>
            </tr>
        </table>


        <div id="message">msg</div>
        <form id="group_multi_operations_form" action="get">
            <table style="width:100%;" class="data highlight">

                <tr>
                    <th><div class="icon"><input id="chkSelectAll" type="checkbox" class="group_check_all"></div></th>
                    <th>group* <i class="icon-question-sign" id="minFilesHelp"></i></th>
                    <th>First Post</th>
                    <th>Last Post</th>
                    <th>last updated</th>
                    <th style="min-width: 52px">active</th>
                    <th style="min-width: 52px">backfill</th>
                    <th>releases</th>
                    <th>Min Files* <i class="icon-question-sign" id="minFilesHelp"></i></th>
                    <th>Min Size* <i class="icon-question-sign" id="minSizeHelp"></i></th>
                    <th>Backfill Days* <i class="icon-question-sign" id="backfillHelp"></i></th>
                    {* <th>options</th> *}
                </tr>

                {foreach from=$grouplist item=group}
                    <tr id="grouprow-{$group.ID}" class="{cycle values=",alt"}">
                        <td style="width:26px;text-align:center;white-space:nowrap;">
                            <input id="chk{$group.ID}" type="checkbox" class="group_check" value="{$result.guid}">
                        </td>
                        <td>
                            <div class="pull-left edit_name pointer" style="color: #B22222;" id="{$group.ID}" >{$group.name|replace:"alt.binaries":"a.b"}</div>
                            <div class="pull-right" style="margin-right: 4px; margin-top: 5px; display: inline-block;"><a class="noredtext btn btn-primary btn-mini" href="{$smarty.const.WWW_TOP}/group-edit.php?id={$group.ID}" title="Edit group properties"><i class="icon-pencil"></i></a></div>
                            <br /><div class="tablehint edit_desc pointer" style="display: inline-block; margin-right: 20px;" id="{$group.ID}">{$group.description}</div>
                        </td>
                        <td class="less">{$group.first_record_postdate|timeago}</td>
                        <td class="less">{$group.last_record_postdate|timeago}</td>
                        <td class="less">{$group.last_updated|timeago} ago</td>
                        <td class="less" id="group-{$group.ID}">{if $group.active=="1"}<a href="javascript:ajax_group_status({$group.ID}, 0)" class="noredtext btn btn-danger btn-mini">Deactivate</a>{else}<a href="javascript:ajax_group_status({$group.ID}, 1)" class="noredtext btn btn-success btn-mini">Activate</a>{/if}</td>
                        <td class="less" id="backfill-{$group.ID}">{if $group.backfill=="1"}<a href="javascript:ajax_backfill_status({$group.ID}, 0)" class="noredtext btn btn-danger btn-mini">Deactivate</a>{else}<a href="javascript:ajax_backfill_status({$group.ID}, 1)" class="noredtext btn btn-success btn-mini">Activate</a>{/if}</td>
                        <td class="less"><a href="{$smarty.const.WWW_TOP}/../browse?g={$group.name}">{$group.num_releases}</a></td>
                        <td class="less edit_files pointer" id="{$group.ID}">{if $group.minfilestoformrelease==""}0{else}{$group.minfilestoformrelease}{/if}</td>
                        <td class="less edit_size pointer" id="{$group.ID}" >{if $group.minsizetoformrelease==""}0.00 MB{else}{$group.minsizetoformrelease|fsize_format:"MB"}{/if}</td>
                        <td class="less edit_backfill pointer" id="{$group.ID}" >{$group.backfill_target}</td>
                        {* <td class="less" id="groupdel-{$group.ID}"><a title="Reset this group" href="javascript:ajax_group_reset({$group.ID})" class="group_reset">Reset</a> | <a href="javascript:ajax_group_delete({$group.ID})" class="group_delete">Delete</a> | <a href="javascript:ajax_group_purge({$group.ID})" class="group_purge" onclick="return confirm('Are you sure? This will delete all releases, binaries/parts in the selected group');" >Purge</a></td>*}
                    </tr>
                {/foreach}

            </table>
        </form>

        <div style="position:relative;margin-top:5px;">
            <tr style="width:100%">
                <td>
                    <div style="float:left">
                        {$pager}
                    </div>
                </td>

                <td>
                    <a title="Delete all collections, binaries and parts" href="javascript:ajax_all_reset()" class="btn btn-warning btn-small" style="float: right; margin-left: 4px" onclick="return confirm('Are you sure? This will reset all groups, deleting all collections/binaries/parts (does not delete releases).');"><i class="icon-asterisk"></i> Reset Groups</a>
                    <a title="Delete all releases, collections, binaries and parts" href="javascript:ajax_all_purge()" style="float: right; margin-left: 4px" class="btn btn-danger btn-small" onclick="return confirm('Are you sure? This will delete all releases, collections/binaries/parts.');"><i class="icon-remove"></i> Purge all</a>
                </td>
            </tr>
        </div>

    {else}
        <p>No groups available (eg. none have been added).</p>
    {/if}

</div>
