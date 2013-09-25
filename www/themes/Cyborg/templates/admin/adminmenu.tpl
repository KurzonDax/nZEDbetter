<h2>Admin Functions</h2>
<ul>
    <li><a title="Home" href="{$smarty.const.WWW_TOP}/..{$site->home_link}">Site Home</a></li>
    <li><a title="Admin Home" href="{$smarty.const.WWW_TOP}/">Admin Home</a></li>
    <li><div style="padding-left: 22px">Settings</div>
        <ul>
            <li><a title="Edit Site" href="{$smarty.const.WWW_TOP}/site-edit.php">Site Settings</a></li>
            <li style="border: none;"><a href="{$smarty.const.WWW_TOP}/tmux-edit.php">Tmux Settings</a>
        </ul>
    </li>
    <li><div style="padding-left: 22px">Users</div>
        <ul>
            <li><a href="{$smarty.const.WWW_TOP}/user-list.php">View</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/user-edit.php?action=add">Add</a> Users</li>
            <li style="border: none;"><a href="{$smarty.const.WWW_TOP}/role-list.php">View</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/role-edit.php?action=add">Add</a> Roles</li>
        </ul>
    </li>
    <li><a href="{$smarty.const.WWW_TOP}/content-add.php?action=add">Add</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/content-list.php">Edit</a> Content Page</li>
    <li><a href="{$smarty.const.WWW_TOP}/menu-list.php">View</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/menu-edit.php?action=add">Add</a> Menu Items</li>
    <li><a href="{$smarty.const.WWW_TOP}/category-list.php?action=add">Edit</a> Categories</li>
    <li><div style="padding-left: 22px">Groups</div>
        <ul>
            <li><a href="{$smarty.const.WWW_TOP}/group-list.php">View/Edit Group List</a></li>
            <li><a href="{$smarty.const.WWW_TOP}/group-edit.php">Add New Group</a></li>
            <li style="border: none;"><a href="{$smarty.const.WWW_TOP}/group-bulk.php">Bulk Add Groups</a></li>
        </ul>
    </li>
    <li><a href="{$smarty.const.WWW_TOP}/binaryblacklist-list.php">View</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/binaryblacklist-edit.php?action=add">Add</a> Blacklist</li>
    <li><a href="{$smarty.const.WWW_TOP}/release-list.php">View Releases</a></li>
    <li><a href="{$smarty.const.WWW_TOP}/rage-list.php">View</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/rage-edit.php?action=add">Add</a> TVRage List</li>
    <li><a href="{$smarty.const.WWW_TOP}/movie-list.php">View</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/movie-add.php">Add</a> Movie List</li>
    <li><a href="{$smarty.const.WWW_TOP}/anidb-list.php">View AniDB List</a></li>
    <li><a href="{$smarty.const.WWW_TOP}/music-list.php">View Music List</a>
        <ul><li style="border: none;"><a href="{$smarty.const.WWW_TOP}/musicgenre-list.php">View</a> Music Genres</li></ul>
    </li>
    <li><a href="{$smarty.const.WWW_TOP}/console-list.php">View Console List</a></li>
    <li><a href="{$smarty.const.WWW_TOP}/nzb-import.php">Import</a> <a style="padding:0;" href="{$smarty.const.WWW_TOP}/nzb-export.php">Export</a> Nzb's</li>
    <li><a href="{$smarty.const.WWW_TOP}/db-optimise.php" class="confirm_action">Optimise</a> Tables</li>
    <li><a href="{$smarty.const.WWW_TOP}/comments-list.php">View Comments</a></li>
    <li><a href="{$smarty.const.WWW_TOP}/site-stats.php">Site Stats</a></li>

</ul>
