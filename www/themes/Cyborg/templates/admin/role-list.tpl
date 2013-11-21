
<table style="margin-top:10px;" class="data highlight">

	<tr>
		<th>name</th>
		<th>request limit</th>
		<th>download limit</th>
		<th>invites</th>
		<th>can preview</th>
		<th>default roles</th>
		<th>options</th>
	</tr>

	
	{foreach from=$userroles item=role}
	<tr class="{cycle values=",alt"}">
		<td><a href="{$smarty.const.WWW_TOP}/role-edit.php?id={$role.ID}">{$role.name}</a></td>
		<td>{$role.apirequests}</td>
		<td>{$role.downloadrequests}</td>
		<td>{$role.defaultinvites}</td>
		<td>{if $role.canpreview == 1}Yes{else}No{/if}</td>
		<td>{if $role.isdefault=="1"}Yes{else}No{/if}</td>
		<td><a href="{$smarty.const.WWW_TOP}/role-edit.php?id={$role.ID}">edit</a>&nbsp;{if $role.ID>"4"}<a class="confirm_action" href="{$smarty.const.WWW_TOP}/role-delete.php?id={$role.ID}">delete</a>{/if}</td>
	</tr>
	{/foreach}


</table>