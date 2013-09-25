<div id="group_list"> 

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
    <table style="width:100%;" class="data highlight">

        <tr>
            <th>group</th>
            <th>First Post</th>
			<th>Last Post</th>
            <th>last updated</th>
            <th style="min-width: 52px">active</th>
            <th style="min-width: 52px">backfill</th>
            <th>releases</th>
			<th>Min Files</th>
			<th>Min Size</th>
            <th>Backfill Days</th>
			<th>options</th>
        </tr>
        
        {foreach from=$grouplist item=group}
        <tr id="grouprow-{$group.ID}" class="{cycle values=",alt"}">
            <td>
				<a href="{$smarty.const.WWW_TOP}/group-edit.php?id={$group.ID}">{$group.name|replace:"alt.binaries":"a.b"}</a>
				<div class="tablehint">{$group.description}</div>
			</td>
            <td class="less">{$group.first_record_postdate|timeago}</td>
			<td class="less">{$group.last_record_postdate|timeago}</td>
            <td class="less">{$group.last_updated|timeago} ago</td>
            <td class="less" id="group-{$group.ID}">{if $group.active=="1"}<a href="javascript:ajax_group_status({$group.ID}, 0)" class="noredtext btn btn-danger btn-mini">Deactivate</a>{else}<a href="javascript:ajax_group_status({$group.ID}, 1)" class="noredtext btn btn-success btn-mini">Activate</a>{/if}</td>
            <td class="less" id="backfill-{$group.ID}">{if $group.backfill=="1"}<a href="javascript:ajax_backfill_status({$group.ID}, 0)" class="noredtext btn btn-danger btn-mini">Deactivate</a>{else}<a href="javascript:ajax_backfill_status({$group.ID}, 1)" class="noredtext btn btn-success btn-mini">Activate</a>{/if}</td>
            <td class="less"><a href="{$smarty.const.WWW_TOP}/../browse?g={$group.name}">{$group.num_releases}</a></td>
			<td class="less">{if $group.minfilestoformrelease==""}n/a{else}{$group.minfilestoformrelease}{/if}</td>
			<td class="less">{if $group.minsizetoformrelease==""}n/a{else}{$group.minsizetoformrelease|fsize_format:"MB"}{/if}</td>
            <td class="less">{$group.backfill_target}</td>
            <td class="less" id="groupdel-{$group.ID}"><a title="Reset this group" href="javascript:ajax_group_reset({$group.ID})" class="group_reset">Reset</a> | <a href="javascript:ajax_group_delete({$group.ID})" class="group_delete">Delete</a> | <a href="javascript:ajax_group_purge({$group.ID})" class="group_purge" onclick="return confirm('Are you sure? This will delete all releases, binaries/parts in the selected group');" >Purge</a></td>
        </tr>
        {/foreach}

    </table>
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
