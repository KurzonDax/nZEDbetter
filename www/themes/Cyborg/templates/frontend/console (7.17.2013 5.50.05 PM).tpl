<div class="well well-small">
<center>
<form class="form-inline" name="browseby" action="console" style="margin:0;">
		
		<i class="fa-icon-font fa-midt"></i>
		<input class="input input-medium" id="title" type="text" name="title" value="{$title}" placeholder="Title" />
		
		<i class="fa-icon-desktop fa-midt"></i>
		<input class="input input-medium" id="platform" type="text" name="platform" value="{$platform}" placeholder="Platform" />
			
		<i class="fa-icon-inbox fa-midt"></i>
			<select class="input input-small" id="genre" name="genre">
				<option class="grouping" value=""></option>
				{foreach from=$genres item=gen}
					<option {if $gen.ID == $genre}selected="selected"{/if} value="{$gen.ID}">{$gen.title}</option>
				{/foreach}
			</select>
			
		<i class="fa-icon-flag fa-midt"></i>
			<select class="input input-small" id="category" name="t">
			<option class="grouping" value="1000"></option>
				{foreach from=$catlist item=ct}
				<option {if $ct.ID==$category}selected="selected"{/if} value="{$ct.ID}">{$ct.title}</option>
				{/foreach}
			</select>
		
		<input class="btn btn-success" type="submit" value="Go" />
</form>
</center>
</div>

{$site->adbrowse}	

{if $results|@count > 0}


<form id="nzb_multi_operations_form" action="get">
	<div class="well well-small">
		<div class="nzb_multi_operations">
			<table width="100%">
				<tr>
					<td width="30%">
						With Selected:
						<div class="btn-group">
							<input type="button" class="nzb_multi_operations_download btn btn-small btn-success" value="Download NZBs" />
							<input type="button" class="nzb_multi_operations_cart btn btn-small btn-info" value="Add to Cart" />
							{if $sabintegrated}<input type="button" class="nzb_multi_operations_sab btn btn-small btn-primary" value="Send to SAB" />{/if}
						</div>
					</td>
					<td width="50%">
						<center>
							{$pager}
						</center>
					</td>
					<td width="20%">
						<div class="pull-right">
							{if $isadmin || $ismod}
								Admin: 	
								<div class="btn-group">	
									<input type="button" class="nzb_multi_operations_edit btn btn-small btn-warning" value="Edit" />
									<input type="button" class="nzb_multi_operations_delete btn btn-small btn-danger" value="Delete" />
								</div>
								&nbsp;
							{/if}
							<a href="{$smarty.const.WWW_TOP}/browse?t={$category}"><i class="fa-icon-align-justify"></i></a>
							&nbsp;
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
	

<table style="width:100%;" class="data highlight icons table table-striped" id="coverstable">
	<tr>
		<th width="130">
			<input type="checkbox" class="nzb_check_all" />
		</th>
		<th width="140" >title<br/>
			<a title="Sort Descending" href="{$orderbytitle_desc}">
				<i class="fa-icon-caret-down"></i>
			</a>
			<a title="Sort Ascending" href="{$orderbytitle_asc}">
				<i class="fa-icon-caret-up"></i>
			</a>
		</th>
			
		<th>platform<br/>
			<a title="Sort Descending" href="{$orderbyplatform_desc}">
				<i class="fa-icon-caret-down"></i>
			</a>
			<a title="Sort Ascending" href="{$orderbyplatform_asc}">
				<i class="fa-icon-caret-up"></i>
			</a>
		</th>
		
		<th>genre<br/>
			<a title="Sort Descending" href="{$orderbygenre_desc}">
				<i class="fa-icon-caret-down"></i>
			</a>
			<a title="Sort Ascending" href="{$orderbygenre_asc}">
				<i class="fa-icon-caret-up"></i>
			</a>
		</th>
		
		<th>release date<br/>
			<a title="Sort Descending" href="{$orderbyreleasedate_desc}">
				<i class="fa-icon-caret-down"></i>
			</a>
			<a title="Sort Ascending" href="{$orderbyreleasedate_asc}">
				<i class="fa-icon-caret-up"></i>
			</a>
		</th>
		
		<th>posted<br/>
			<a title="Sort Descending" href="{$orderbyposted_desc}">
				<i class="fa-icon-caret-down"></i>
			</a>
			<a title="Sort Ascending" href="{$orderbyposted_asc}">
				<i class="fa-icon-caret-up"></i>
			</a>
		</th>
			
		<th>size<br/>
			<a title="Sort Descending" href="{$orderbysize_desc}">
				<i class="fa-icon-caret-down"></i>
			</a>
			<a title="Sort Ascending" href="{$orderbysize_asc}">
				<i class="fa-icon-caret-up"></i>
			</a>
		</th>
			
		<th>files<br/>
			<a title="Sort Descending" href="{$orderbyfiles_desc}">
				<i class="fa-icon-caret-down"></i>
			</a>
			<a title="Sort Ascending" href="{$orderbyfiles_asc}">
				<i class="fa-icon-caret-up"></i>
			</a>
		</th>
			
		<th>stats<br/>
			<a title="Sort Descending" href="{$orderbystats_desc}">
				<i class="fa-icon-caret-down"></i>
			</a>
			<a title="Sort Ascending" href="{$orderbystats_asc}">
				<i class="fa-icon-caret-up"></i>
			</a>
		</th>
			
	</tr>

	{foreach from=$results item=result}
		<tr class="{cycle values=",alt"}">
			<td class="mid">
				<div class="movcover">
					<center>
						<a class="title" title="View details" href="{$smarty.const.WWW_TOP}/details/{$result.guid}/{$result.searchname|escape:"seourl"}">
							<img class="shadow img img-polaroid" src="{$smarty.const.WWW_TOP}/covers/console/{if $result.cover == 1}{$result.consoleinfoID}.jpg{else}no-cover.jpg{/if}" 
							width="120" border="0" alt="{$result.title|escape:"htmlall"}" />
						</a>
					</center>
					<div class="movextra">
						<center>
						{if $result.nfoID > 0}<a href="{$smarty.const.WWW_TOP}/nfo/{$result.guid}" title="View Nfo" class="rndbtn modal_nfo badge" rel="nfo">Nfo</a>{/if}
						{if $result.url != ""}<a class="rndbtn badge badge-amaz" target="_blank" href="{$site->dereferrer_link}{$result.url}" name="amazon{$result.consoleinfoID}" title="View amazon page">Amazon</a>{/if}
						<a class="rndbtn badge" href="{$smarty.const.WWW_TOP}/browse?g={$result.group_name}" title="Browse releases in {$result.group_name|replace:"alt.binaries":"a.b"}">Grp</a>
						</center>
					</div>
				</div>
			</td>
			<td colspan="8" class="left" id="guid{$result.guid}">
				
				<ul class="inline">
					<li>
						<h4>
							<a class="title" title="View details" href="{$smarty.const.WWW_TOP}/details/{$result.guid}/{$result.searchname|escape:"seourl"}">{$result.title|escape:"htmlall"} - {$result.platform|escape:"htmlall"}</a>
						</h4>
					</li>
					<li style="vertical-align:text-bottom;">
						<div class="icon">
							<input type="checkbox" class="nzb_check" value="{$result.guid}" />
						</div>
					</li>
					<li style="vertical-align:text-bottom;">
						<div class="icon icon_nzb">
							<a title="Download Nzb" href="{$smarty.const.WWW_TOP}/getnzb/{$result.guid}/{$result.searchname|escape:"url"}">
								<img src="{$smarty.const.WWW_TOP}/templates/baffi/images/icons/nzbup.png">
							</a>
						</div>
					</li>
					<li style="vertical-align:text-bottom;">
						<div>
							<a href="#" class="icon icon_cart" title="Add to Cart">
								<img src="{$smarty.const.WWW_TOP}/templates/baffi/images/icons/cartup.png">
							</a>
						</div>
					</li>
					<li style="vertical-align:text-bottom;">
						{if $sabintegrated}
						<div>
							<a href="#" class="icon icon_sab" title="Send to my Sabnzbd">
								<img src="{$smarty.const.WWW_TOP}/templates/baffi/images/icons/sabup.png">
							</a>
						</div>
						{/if}
					</li>
				</ul>
				
				{if $result.genre != ""}<b>Genre:</b> {$result.genre}<br />{/if}
				{if $result.esrb != ""}<b>Rating:</b> {$result.esrb}<br />{/if}
				{if $result.publisher != ""}<b>Publisher:</b> {$result.publisher}<br />{/if}
				{if $result.releasedate != ""}<b>Released:</b> {$result.releasedate|date_format}<br />{/if}
				{if $result.review != ""}<b>Review:</b> {$result.review|escape:'htmlall'}<br />{/if}
				<br />
				<div class="movextra">
					<b>{$result.searchname|escape:"htmlall"}</b> <a class="rndbtn btn btn-mini btn-info" href="{$smarty.const.WWW_TOP}/console?platform={$result.platform}" title="View similar nzbs">Similar</a>
					{if $isadmin || $ismod}
						<a class="rndbtn btn btn-mini btn-warning" href="{$smarty.const.WWW_TOP}/admin/release-edit.php?id={$result.releaseID}&amp;from={$smarty.server.REQUEST_URI|escape:"url"}" title="Edit Release">Edit</a> <a class="rndbtn confirm_action btn btn-mini btn-danger" href="{$smarty.const.WWW_TOP}/admin/release-delete.php?id={$result.releaseID}&amp;from={$smarty.server.REQUEST_URI|escape:"url"}" title="Delete Release">Delete</a>
					{/if}
					<br />
					
					<ul class="inline">
						<li width="50px"><b>Info:</b></li>
						<li width="100px">Posted {$result.postdate|timeago}</li>
						<li width="80px">{$result.size|fsize_format:"MB"}</li>
						<li width="50px"><a title="View file list" href="{$smarty.const.WWW_TOP}/filelist/{$result.guid}">{$result.totalpart}</a> <i class="fa-icon-file"></i></li>
						<li width="50px"><a title="View comments for {$result.searchname|escape:"htmlall"}" href="{$smarty.const.WWW_TOP}/details/{$result.guid}/#comments">{$result.comments}</a> <i class="fa-icon-comments-alt"></i></li>
						<li width="50px">{$result.grabs} <i class="fa-icon-download-alt"></i></li>
					</ul>
					
				</div>
			</td>
		</tr>
	{/foreach}
	
</table>

<br/>

{$pager}

{if $results|@count > 10}
<div class="well well-small">
	<div class="nzb_multi_operations">
		<table width="100%">
			<tr>
				<td width="30%">
					With Selected:
					<div class="btn-group">
						<input type="button" class="nzb_multi_operations_download btn btn-small btn-success" value="Download NZBs" />
						<input type="button" class="nzb_multi_operations_cart btn btn-small btn-info" value="Add to Cart" />
						{if $sabintegrated}<input type="button" class="nzb_multi_operations_sab btn btn-small btn-primary" value="Send to SAB" />{/if}
					</div>
				</td>
				<td width="50%">
					<center>
						{$pager}
					</center>
				</td>
				<td width="20%">
					{if $section != ''}
						<div class="pull-right">
						{if $isadmin || $ismod}
							Admin: 	
							<div class="btn-group">	
								<input type="button" class="nzb_multi_operations_edit btn btn-small btn-warning" value="Edit" />
								<input type="button" class="nzb_multi_operations_delete btn btn-small btn-danger" value="Delete" />
							</div>
							&nbsp;
						{/if}
						<a href="{$smarty.const.WWW_TOP}/browse?t={$category}"><i class="fa-icon-th-list"></i></a>
						&nbsp;
						</div>
					{/if}
				</td>
			</tr>
		</table>
	</div>
</div>

{/if}

</form>

{else}
<div class="alert">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<strong>Sorry!</strong> Either something is wrong, or there is nothing in this section.
</div>
{/if}
