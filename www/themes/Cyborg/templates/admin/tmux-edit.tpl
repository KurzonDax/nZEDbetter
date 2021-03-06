<br/>
<div class="hint" style="font-size: medium; margin-bottom: 20px;">You must click the <a href="#SaveSettings" style="margin: 0; text-decoration: underline; color: darkred; font-size: medium;">save settings</a> button under the tabs to preserve any changes you make.</div>

<form action="{$SCRIPT_NAME}?action=submit" method="post">

{if $error != ''}
	<div class="error">{$error}</div>
{/if}
<div class="tabber" id="tmuxtabs">
<div class="tabbertab">
<h2><a name="Start">Start</a></h2>
<fieldset>
    <legend>Tmux - How It Works</legend>
    <table class="input">
        <tr>
            <td><label for="explain">Information:</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td>
                <div class="explanation">Tmux is a screen multiplexer and at least version 1.6 is required. It is used here to allow multiple windows per session and multiple panes per window.<br />
                    Each script is run in its own shell environment. It is not looped, but allowed to run once and then exit. This notifies tmux that the pane is dead and can then be respawned with another iteration of the script in a new shell environment.
                    This allows for scripts that crash to be restarted without user intervention.<br /><br />
                    You can run multiple tmux sessions, but they all must have an associated tmux.conf file and all sessions must use the same tmux.conf file.<br /><br />
                    <h3><b>NOTICE:</b></h3> If "Save Tmux Settings" is the last thing you did on this page or if it is the active element and if you have this page set to autorefresh or you refresh instead of following a link to this page, you will set the db with the settings currently on this page, not reload from db. This could cause tmux scripts to start while optimize or patch the database is running.</div>
            </td>
        </tr>
    </table>
</fieldset>
</div>
<div class="tabbertab">
    <h2><a name="Monitor">Monitor</a></h2>

        <fieldset>
            <legend>Monitor</legend>
            <table class="input">
                <tr>
                    <td><label for="explain">Information:</label></td>
                    <td>
                        <div class="explanation">
                            Monitor is the name of the script that monitors all of the tmux panes and windows. It stops/stops scripts based on user settings. It queries the database to provide stats from your nZEDb database.<br /><br />
                            There are 2 columns of numbers, 'In Process' and 'In Database'. The 'In Process' is all releases that need to be postprocessed. The 'In Database' is the number of releases matching that category.
                            <ul>
                                <li>The 'In Process' column has 2 sets of numbers, the total for each category that needs to be postprocessed and inside the parenthesis is the difference from when the script started to what it is now.</li>

                                <li>The 'In Database' column also has 2 sets of numbers, the total releases for each category and inside the parenthesis is the percentage that category is to the total number of releases.</li>
                            </ul>
                            <b>Special Row Definitions</b><br/>
                            <table style="margin-left: 15px;">
                                <tr>
                                    <td><b>Misc Row</b></td>
                                    <td>The Misc row means something different in both columns.<br/>
                                        The 'In Process' column is all releases that have not had 'Additional' run on them. This includes all categories, not just the Misc Category.<br/>
                                        The 'In Database' number is the amount of releases that have not been categorized in any other category.
                                    </td>
                                </tr>
                                <tr>
                                    <td><b>PreDB Row</b></td>
                                    <td>The 'In Process' predb is the total predb and inside the parenthesis is number changed since the script started.<br/>
                                        The 'In Database' is the total matched predb's you have and the number inside the parenthesis is the percentage of total releases that you have matched to a predb release.</td>
                                </tr>
                                <tr>
                                    <td><b>NZB's Row</b></td>
                                    <td>The 'In Process' NZBs are total nzbs.  Inside the parenthesis is the number of unique nzbs.<br/>
                                        The 'In Database' number represents NZBs that have all parts available and will be processed on next run.</td>
                                </tr>
                                <tr>
                                    <td><b>Request ID Row</b></td>
                                    <td>The 'In Process' requestID is the number of releases waiting to be processed.  Inside the parenthesis is the number changed since the script started.<br/>
                                        The 'In Database' number is the total matches of releases to requestIDs.  Inside the parenthesis is percentage of total releases that have been matched to a requestID.</td>
                                </tr>
                            </table>
                            <br/>
                            <b>Note:</b><br/>
                            The counts for parts, binaries and predb totals are estimates and can vary wildly between queries. It is too slow to query the db for real counts, when using InnoDB. All of the other counts are actual counts.

                        </div>
                    </td>
                </tr>
                <tr>
                    <td><label for="RUNNING">Tmux Scripts Running:</label></td>
                    <td>
                        {html_radios id="RUNNING" name='RUNNING' values=$truefalse_names output=$truefalse_names selected=$ftmux->RUNNING separator='<br />'}
                        <div class="hint">This is the shutdown, true/on, it runs, false/off and all scripts are terminated. This will start/stop all panes without terminating the monitor pane.</div>
                    </td>
                </tr>

                <tr>
                    <td style="width:160px;"><label for="MONITOR_DELAY">Monitor Loop Timer:</label></td>
                    <td>
                        <input id="MONITOR_DELAY" name="MONITOR_DELAY" class="tiny" type="text" value="{$ftmux->MONITOR_DELAY}" />
                        <div class="hint">The time between query refreshes of monitor information, in seconds. This has no effect on any other pane, except in regards to the kill switches. The other panes are checked every 10 seconds. The lower the number, the more often it queries the database for numbers.<br />
                            <b>As the database gets larger in size, the longer this set of queries takes to process.</b> It is recommended that you set the sleep timer to at least 300 seconds (5 minutes), if any number in postprocess or total releases exceeds 1 million.</div>
                    </td>
                </tr>

                <tr>
                    <td><label for="TMUX_SESSION">Tmux Session:</label></td>
                    <td>
                        <input id="TMUX_SESSION" name="TMUX_SESSION" class="long" type="text" value="{$ftmux->TMUX_SESSION}" />
                        <div class="hint">Enter the session name to be used by tmux, no spaces allowed in the name, this can't be changed after scripts start. If you are running multiple servers, you could put your hostname here</div>
                    </td>
                </tr>

                <tr>
                    <td><label for="MONITOR_PATH">Monitor a Ramdisk:</label></td>
                    <td>
                        <input id="MONITOR_PATH" style="margin-bottom: 3px;" name="MONITOR_PATH" class="long" type="text" value="{$ftmux->MONITOR_PATH}" /><br />
                        <input id="MONITOR_PATH_A" style="margin-bottom: 3px;" name="MONITOR_PATH_A" class="long" type="text" value="{$ftmux->MONITOR_PATH_A}" /><br />
                        <input id="MONITOR_PATH_B" name="MONITOR_PATH_B" class="long" type="text" value="{$ftmux->MONITOR_PATH_B}" />
                        <div class="hint">Enter a path here to have Monitor monitor its usage and free space. Must be a valid path.<br />To use this example, add to fstab and edit path, gid and uid, then mount as user not root:<br />tmpfs /var/www/nZEDb/nzbfiles/tmpunrar tmpfs user,uid=1000,gid=33,nodev,nodiratime,nosuid,size=1G,mode=777 0 0<br />
                            gid == group id == /etc/groups, uid == user id == /etc/passwd</div>
                    </td>
                </tr>


            </table>
        </fieldset>

</div>
<div class="tabbertab">
    <h2><a name="Update">Update Binaries</a></h2>
    <fieldset>
        <legend>Sequential Processing</legend>
        <table class="input">
            <tr>
                <td><label for="explain">Information:</label></td>
                <td>
                    <div class="explanation">Sequential processing causes the update_binaries, backfill, andn update_releases scripts to run one after the other, rather than
                        running simultaneously.  This is generally only recommended for relatively low-powered servers (i.e. less than four processor cores, and less than 8MB RAM.  Running
                        sequentially dramtically lowers the performance requirements of the database engine.</div>
                </td>
            </tr>
            <tr>
                <td><label for="SEQUENTIAL">Run Sequential:</label></td>
                <td>
                    {html_radios id="SEQUENTIAL" name='SEQUENTIAL' values=$truefalse_names output=$truefalse_names selected=$ftmux->SEQUENTIAL separator='<br />'}
                    <div class="hint">Choose to run update_binaries, backfill and update releases_sequentially. Changing requires restart. true/false</div>
                </td>
            </tr>

            <tr>
                <td style="width:160px;"><label for="SEQ_TIMER">Sequential Sleep Timer:</label></td>
                <td>
                    <input id="SEQ_TIMER" name="SEQ_TIMER" class="tiny" type="text" value="{$ftmux->SEQ_TIMER}" />
                    <div class="hint">The time to sleep from the time the loop ends until it is restarted, in seconds.</div>
                </td>
            </tr>
        </table>
    </fieldset>
    <fieldset>
        <legend>Update Binaries</legend>
        <table class="input">
            <tr>
                <td><label for="BINARIES">Update Binaries:</label></td>
                <td>
                    {html_radios id="BINARIES" name='BINARIES' values=$truefalse_names output=$truefalse_names selected=$ftmux->BINARIES separator='<br />'}
                    <div class="hint">Choose to run update_binaries true/false. Update binaries retrieves articles from the Usenet Service Provider.
                        Articles are retrieved starting with the most recent post that has been obtained for each group until present. </div>
                </td>
            </tr>

            <tr>
                <td style="width:160px;"><label for="BINS_TIMER">Update Binaries Sleep Timer:</label></td>
                <td>
                    <input id="BINS_TIMER" name="BINS_TIMER" class="tiny" type="text" value="{$ftmux->BINS_TIMER}" />
                    <div class="hint">The time to sleep from the time the loop ends until it is restarted, in seconds.</div>
                </td>
            </tr>
        </table>
    </fieldset>
    <fieldset>
        <legend>Backfill</legend>
        <table class="input">
            <tr>
                <td><label for="BACKFILL">Backfill:</label></td>
                <td>
                    {html_options class="siteeditstyle" id="BACKFILL" name='BACKFILL' values=$backfill_ids output=$backfill_names selected=$ftmux->BACKFILL}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{html_options class="siteeditstyle" id="BACKFILL_ORDER" name='BACKFILL_ORDER' values=$backfill_group_ids output=$backfill_group selected=$ftmux->BACKFILL_ORDER}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{html_options class="siteeditstyle" id="BACKFILL_DAYS" name='BACKFILL_DAYS' values=$backfill_days_ids output=$backfill_days selected=$ftmux->BACKFILL_DAYS}
                    <div class="hint">Choose to run backfill type. Backfill gets from your first_record back.<br />
                        Disabled - Disables backfill from running.<br />
                        Safe - Backfills 1 group by backfill days (set in admin-view groups), using the number of threads set in admin. This downloads Backfill Quantity times the Backfill Threads, each loop.<br \>
                        example: you have Backfill Threads = 10, Backfill Quantity = 20k, Max Messages = 5k: you will run 10 threads, queue of 40 and download 200k headers.<br />
                        Interval - Backfills the number of groups (set in tmux), by backfill days (set in admin-view groups), completely.<br />
                        All - Backfills the number of groups (set in tmux), by Backfill Quantity (set in tmux), up to backfill days (set in admin-view groups)<br />
                        These settings are all per loop and does not use backfill date. Approximately every 80 minutes, every activated backfill group will be backfilled (5k headers). This is to allow incomplete collections to be completed and/or the 2 hour delay reset if the collection is still active. This extra step is not necessary and is not used when using Sequential.<br />
                        Newest - Sorts the group selection with the least backfill days backfilled, first.<br />
                        Oldest - Sorts the group selection with the most backfill days backfilled, first.<br />
                        Alphabetical - Sorts the group selection from a to z.<br />
                        Alphabetical Reverse - Sorts the group selection from z to a.<br /a>
                        Most Posts - Sorts the group selection by the highest number of posts, first.<br /a>
                        Fewest Posts - Sorts the group selection by the lowest number of posts, first.<br />
                        Backfill days - Days per Group from admin->view group or the Safe Backfill Date from admin->edit site.</div>
                </td>
            </tr>
            <tr>
                <td style="width:160px;"><label for="BACKFILL_QTY">Backfill Quantity:</label></td>
                <td>
                    <input id="BACKFILL_QTY" name="BACKFILL_QTY" class="medium" type="text" value="{$ftmux->BACKFILL_QTY}" />
                    <div class="hint">When not running backfill intervals, you select the number of headers per group per thread to download.</div>
                </td>
            </tr>

            <tr>
                <td style="width:160px;"><label for="BACKFILL_GROUPS">Backfill Groups:</label></td>
                <td>
                    <input id="BACKFILL_GROUPS" name="BACKFILL_GROUPS" class="tiny" type="text" value="{$ftmux->BACKFILL_GROUPS}" />
                    <div class="hint">When running backfill the groups are sorted so that the newest groups are backfilled first. Select the number of groups to backfill per loop.</div>
                </td>
            </tr>

            <tr>
                <td style="width:160px;"><label for="BACK_TIMER">Backfill Sleep Timer:</label></td>
                <td>
                    <input id="BACK_TIMER" name="BACK_TIMER" class="tiny" type="text" value="{$ftmux->BACK_TIMER}" />
                    <div class="hint">The time to sleep from the time the loop ends until it is restarted, in seconds.</div>
                </td>
            </tr>

            <tr>
                <td style="width:160px;"><label for="PROGRESSIVE">Variable Sleep Timer:</label></td>
                <td>
                    {html_radios id="PROGRESSIVE" name='PROGRESSIVE' values=$truefalse_names output=$truefalse_names selected=$ftmux->PROGRESSIVE separator='<br />'}
                    <div class="hint">This will vary the backfill sleep depending on how many collections you have.<br />ie 50k collections would make sleep timer 100 seconds and 20k releases would make sleep timer 40 seconds.</div>
                </td>
            </tr>
        </table>
    </fieldset>
    <fieldset>
        <legend>Import NZBS</legend>
        <table class="input">
            <tr>
                <td><label for="explain">Information:</label></td>
                <td>
                    <div class="explanation">This will import all nzbs in the given path. If in your path you have nzbs in the root folder and subfolders(regardless of nzbs inside), threaded scripts will ignore all nzbs in the root path. Then each subfolder is threaded.</div>
                </td>
            </tr>
            <tr>
                <td><label for="IMPORT">Import NZBS:</label></td>
                <td>
                    {html_options class="siteeditstyle" id="IMPORT" name='IMPORT' values=$import_ids output=$import_names selected=$ftmux->IMPORT}
                    <div class="hint">Choose to run import nzb script true/false. This can point to a single folder with multiple subfolders on just the one folder. If you run this threaded, it will run 1 folder per thread.</div>
                </td>
            </tr>

            <tr>
                <td><label for="NZBS">Nzbs:</label></td>
                <td>
                    <input id="NZBS" class="long" name="NZBS" type="text" value="{$ftmux->NZBS}" />
                    <div class="hint">Set the path to the nzb dump you downloaded from torrents, this is the path to bulk files folder of nzbs. This is by default, recursive and threaded. You set the threads in edit site, Advanced Settings.</div>
                </td>
            </tr>

            <tr>
                <td><label for="IMPORT_BULK">Use Bulk Importer:</label></td>
                <td>
                    {html_radios id="IMPORT_BULK" name='IMPORT_BULK' values=$truefalse_names output=$truefalse_names selected=$ftmux->IMPORT_BULK separator='<br />'}
                    <div class="hint">Choose to run the bulk import nzb script true/false. This uses /dev/shm and can interfere with apparmor. This runs about 10% faster than stock importer. true/false</div>
                </td>
            </tr>

            <tr>
                <td style="width:160px;"><label for="IMPORT_TIMER">Import NZBS Sleep Timer:</label></td>
                <td>
                    <input id="IMPORT_TIMER" name="IMPORT_TIMER" class="tiny" type="text" value="{$ftmux->IMPORT_TIMER}" />
                    <div class="hint">The time to sleep from the time the loop ends until it is restarted, in seconds.</div>
                </td>
            </tr>

        </table>
    </fieldset>
</div>

<div class="tabbertab">
<h2><a name="Postprocessing">Postprocessing</a></h2>
<fieldset>
    <legend>Update Releases</legend>
    <table class="input">
        <tr>
            <td><label for="RELEASES">Update Releases:</label></td>
            <td>
                {html_radios id="RELEASES" name='RELEASES' values=$truefalse_names output=$truefalse_names selected=$ftmux->RELEASES separator='<br />'}
                <div class="hint">Create releases, this is really only necessary to turn off when you only want to post process.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="REL_TIMER">Update Releases Sleep Timer:</label></td>
            <td>
                <input id="REL_TIMER" name="REL_TIMER" class="tiny" type="text" value="{$ftmux->REL_TIMER}" />
                <div class="hint">The time to sleep from the time the loop ends until it is restarted, in seconds.</div>
            </td>
        </tr>
    </table>
</fieldset>

<fieldset>
    <legend>Postprocessing</legend>
    <table class="input">
        <tr>
            <td><label for="POST">Postprocess Additional:</label></td>
            <td>
                {html_options class="siteeditstyle" id="POST" name='POST' values=$post_ids output=$post_names selected=$ftmux->POST}
                <div class="hint">Choose to do deep rar inspection, preview and sample creation and/or nfo processing. true/false</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="POST_TIMER">Postprocess Additional Sleep Timer:</label></td>
            <td>
                <input id="POST_TIMER" name="POST_TIMER" class="tiny" type="text" value="{$ftmux->POST_TIMER}" />
                <div class="hint">The time to sleep from the time the loop ends until it is restarted, in seconds.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="POST_KILL_TIMER">Postprocess Kill Timer:</label></td>
            <td>
                <input id="POST_KILL_TIMER" name="POST_KILL_TIMER" class="tiny" type="text" value="{$ftmux->POST_KILL_TIMER}" />
                <div class="hint">The time postprocess is allowed to run with no updates to the screen. Activity is detected when the history for the pane changes. The clock is restarted everytime activity is detected.</div>
            </td>
        </tr>

        <tr>
            <td><label for="POST_AMAZON">Postprocess Amazon:</label></td>
            <td>
                {html_radios id="POST_AMAZON" name='POST_AMAZON' values=$truefalse_names output=$truefalse_names selected=$ftmux->POST_AMAZON separator='<br />'}
                <div class="hint">Choose to do books, music and games lookups true/false</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="POST_TIMER_AMAZON">Postprocess Amazon Sleep Timer:</label></td>
            <td>
                <input id="POST_TIMER_AMAZON" name="POST_TIMER_AMAZON" class="tiny" type="text" value="{$ftmux->POST_TIMER_AMAZON}" />
                <div class="hint">The time to sleep from the time the loop ends until it is restarted, in seconds.</div>
            </td>
        </tr>

        <tr>
            <td><label for="POST_NON">Postprocess Non-Amazon:</label></td>
            <td>
                {html_radios id="POST_NON" name='POST_NON' values=$truefalse_names output=$truefalse_names selected=$ftmux->POST_NON separator='<br />'}
                <div class="hint">Choose to do movies, anime and tv lookups. true/false</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="POST_TIMER">Postprocess Non-Amazon Sleep Timer:</label></td>
            <td>
                <input id="POST_TIMER_NON" name="POST_TIMER_NON" class="tiny" type="text" value="{$ftmux->POST_TIMER_NON}" />
                <div class="hint">The time to sleep from the time the loop ends until it is restarted, in seconds.</div>
            </td>
        </tr>
    </table>
</fieldset>

<fieldset>
    <legend>Purge Processed Collections</legend>
    <table class="input">
        <tr>
            <td><label for="PURGE_THREAD">Purge processed collections:</label></td>
            <td>
                {html_radios id="RUN_PURGE_THREAD" name='RUN_PURGE_THREAD' values=$truefalse_names output=$truefalse_names selected=$ftmux->RUN_PURGE_THREAD separator='<br />'}
                <div class="hint">Choose to run the collection purge thread.  This thread automatically purges collections that have been processed
                    and had releases created from them.  A collection (and associated binaries/parts) will only be purged once the NZB has been
                    successfully created, and the release has been inserted in to the database.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="PURGE_SLEEP">Purge Thread Sleep Timer:</label></td>
            <td>
                <input id="PURGE_SLEEP" name="PURGE_SLEEP" class="tiny" type="text" value="{$ftmux->PURGE_SLEEP}" />
                <div class="hint">The time to sleep from the time the loop ends until it is restarted, in seconds.</div>
            </td>
        </tr>
        <tr>
            <td style="width:160px;"><label for="PURGE_MAX_COLS">Max number of collections to purge each run:</label></td>
            <td>
                <input id="PURGE_MAX_COLS" name="PURGE_MAX_COLS" class="medium" type="text" value="{$ftmux->PURGE_MAX_COLS}" /><br />
                <div class="hint">This setting determines how many collections will be purged each time the script runs.  It is <b>highly
                        recommended</b> to leave this at the default of 500, unless you have a lot of RAM (more than 32GB) and
                    you have MySQL configured appropriately to use lots of RAM.  Even then, it is not recommended to go
                    above 1000, unless you are not running update_binaries, backfill, or update_releases.
                </div>
            </td>
        </tr>
        <tr>
            <td style="width:160px;"><label for="FURIOUS_PURGE">Enable furious purging:</label></td>
            <td>
                {html_radios id="FURIOUS_PURGE" name='FURIOUS_PURGE' values=$truefalse_names output=$truefalse_names selected=$ftmux->FURIOUS_PURGE separator='<br />'}
                <div class="hint">This option allows the purge script to continue running, without pause, until all collections
                    that are ready to be purged have been deleted.  Set this to TRUE while doing your initial update_binaries for all of your groups.
                    Leave it set to true until all groups have been backfilled. After that, it's up to you.  It doesn't
                    hurt anything to leave it enabled.
                </div>
            </td>
        </tr>
        <tr>
            <td style="width:160px;"><label for="FULL_PURGE_FREQ">Frequency, in hours, to run full purge:</label></td>
            <td>
                <input id="FULL_PURGE_FREQ" name="FULL_PURGE_FREQ" class="tiny" type="text" value="{$ftmux->FULL_PURGE_FREQ}" />
                <div class="hint">Set this to the number of HOURS between full purge cycles.  The full purge cycle cleans <br />
                    up the collections, binaries, and parts databases by deleting items past retention.  It also removes <br />
                    releases that have gone past their retention.  Recommend to run this once every 24 hours.  It is not really<br />
                    necessary to run it more often than that.
                </div>
            </td>
        </tr>
        <tr>
            <td style="width:160px;"><label for="NO_PURGE_MISC_BEFORE_FIX">Require namecheck before purging Hashed or Misc-Other releases:</label></td>
            <td>
                {html_radios id="NO_PURGE_MISC_BEFORE_FIX" name='NO_PURGE_MISC_BEFORE_FIX' values=$truefalse_names output=$truefalse_names selected=$ftmux->NO_PURGE_MISC_BEFORE_FIX separator='<br />'}
                <div class="hint">
                    This option will prevent the purging of Misc->Other and Misc->Hashed releases until the name fixer script has had
                    a chance to process the release (and move it to a new category with a fixed name).  If you set this to true, you must
                    have postprocessing set to Additional or All above.  You must also have Fix Release Names (under optional processes) set
                    to true.  Lastly, the retention hours for Misc->Other and Hashed releases must be greater than zero on the Site Settings
                    Advanced tab.
                </div>
            </td>
        </tr>
        <tr>
            <td style="width:160px;"><label for="DEAD_COLLECTION_CHECK_HOURS">Stale Collection Window in Hours:</label></td>
            <td>
                <input id="DEAD_COLLECTION_CHECK_HOURS" name="DEAD_COLLECTION_CHECK_HOURS" class="tiny" type="text" value="{$ftmux->DEAD_COLLECTION_CHECK_HOURS}" />
                <div class="hint">This setting determines how long a collection may go without being updated before considering it stale.<br />
                    The window is based on the posted date and time of the last binary to be updated within the collection compared to date/time<br />
                    of either the first or last post we have in the database for the newsgroup.  If the completeness of the collection is estimated<br />
                    to be less than the required percent complete for the site (set in site settings), the collection, binaries, and parts will be<br />
                    purged.  Otherwise, it will be queued to convert to a release.  The default is 6 hours.  A good range is usually 6-24 hours.<br />
                    Set to 0 (zero) to disable the function completely.  This check requires that the purge thread be enabled.  Collectiones will<br />
                    checked once an hour.
                </div>
            </td>
        </tr>
    </table>
</fieldset>
</div>
<div class="tabbertab">
<h2><a name="Optional">Optional Processes</a></h2>
<fieldset>
    <legend>Fix Release Names</legend>
    <table class="input">
        <tr>
            <td><label for="FIX_NAMES">Fix Release Names:</label></td>
            <td>
                {html_radios id="FIX_NAMES" name='FIX_NAMES' values=$truefalse_names output=$truefalse_names selected=$ftmux->FIX_NAMES separator='<br />'}
                <div class="hint">Choose to try to fix Releases Names using NFOs true/false</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="FIX_TIMER">Fix Release Names Sleep Timer:</label></td>
            <td>
                <input id="FIX_TIMER" name="FIX_TIMER" class="tiny" type="text" value="{$ftmux->FIX_TIMER}" />
                <div class="hint">The time to sleep from the time the loop ends until it is restarted, in seconds.</div>
            </td>
        </tr>
    </table>
</fieldset>

<fieldset>
    <legend>Remove Crap Releases</legend>
    <table class="input">
        <tr>
            <td><label for="FIX_CRAP">Remove Crap Releases:</label></td>
            <td>
                {html_options class="siteeditstyle" id="FIX_CRAP" name='FIX_CRAP' values=$fix_crap_ids output=$fix_crap_names selected=$ftmux->FIX_CRAP}
                <div class="hint">Choose to run Remove Crap Releases. You can choose all or select a single option.</div>
            </td>
        </tr>

        <tr>
            <td><label for="FIX_CRAP">Hours to Scan:</label></td>
            <td>
                {html_options class="siteeditstyle" id="REMOVE_CRAP_HOURS" name='REMOVE_CRAP_HOURS' values=$crap_hours_values output=$crap_hours_values selected=$ftmux->REMOVE_CRAP_HOURS}
                <div class="hint">Number of hours to scan back for crap releases.  This is based on the date/time the release was added to the database. Select 'full' to
                check all releases on each scan.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="CRAP_TIMER">Remove Crap Releases Sleep Timer:</label></td>
            <td>
                <input id="CRAP_TIMER" name="CRAP_TIMER" class="tiny" type="text" value="{$ftmux->CRAP_TIMER}" />
                <div class="hint">The time to sleep from the time the loop ends until it is restarted, in seconds.</div>
            </td>
        </tr>
    </table>
</fieldset>

<fieldset>
    <legend>Misc Sorter</legend>
    <table class="input">
        <tr>
            <td><label for="explain">Information:</label></td>
            <td>
                <div class="explanation">Misc Sorter only works on the misc category(7000). It will throw an error if you have no releases in 7000 that need to be postprocessed. This works by collecting keywords from the nfo, if enough keywords are present, then an assumption is made as to which category it belongs.</div>
            </td>
        </tr>
        <tr>
            <td><label for="SORTER">Misc Sorter:</label></td>
            <td>
                {html_radios id="SORTER" name='SORTER' values=$truefalse_names output=$truefalse_names selected=$ftmux->SORTER separator='<br />'}
                <div class="hint">Choose to run Misc Sorter true/false</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="SORTER_TIMER">Misc Sorter Sleep Timer:</label></td>
            <td>
                <input id="SORTER_TIMER" name="SORTER_TIMER" class="tiny" type="text" value="{$ftmux->SORTER_TIMER}" />
                <div class="hint">The time to sleep from the time the loop ends until it is restarted, in seconds.</div>
            </td>
        </tr>

    </table>
</fieldset>

<fieldset>
    <legend>Decrypt Hashes</legend>
    <table class="input">
        <tr>
            <td><label for="explain">Information:</label></td>
            <td>
                <div class="explanation">Decrypt hashes only works on a.b.inner-sanctum and only works form releases posted buy doggo. If you do not index that group, there is no need to enable this.
                    Included in the same pane is Update Predb. This scrapes several predb sites and then tries to match against releases.</div>
            </td>
        </tr>
        <tr>
            <td><label for="DEHASH">Decrypt Hash Based Release Names:</label></td>
            <td>
                {html_options class="siteeditstyle" id="DEHASH" name='DEHASH' values=$dehash_ids output=$dehash_names selected=$ftmux->DEHASH}
                <div class="hint">Choose to run Decrypt Hashes true/false</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="DEHASH_TIMER">Decryt Hashes Sleep Timer:</label></td>
            <td>
                <input id="DEHASH_TIMER" name="DEHASH_TIMER" class="tiny" type="text" value="{$ftmux->DEHASH_TIMER}" />
                <div class="hint">The time to sleep from the time the loop ends until it is restarted, in seconds.</div>
            </td>
        </tr>

    </table>
</fieldset>

<fieldset>
    <legend>Update TV/Theater</legend>
    <table class="input">
        <tr>
            <td><label for="UPDATE_TV">Update TV and Theater Schedules:</label></td>
            <td>
                {html_radios id="UPDATE_TV" name='UPDATE_TV' values=$truefalse_names output=$truefalse_names selected=$ftmux->UPDATE_TV separator='<br />'}
                <div class="hint">Choose to run Update TV and Theater Schedules true/false</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="TV_TIMER">Update TV and Theater Start Timer:</label></td>
            <td>
                <input id="TV_TIMER" name="TV_TIMER" class="tiny" type="text" value="{$ftmux->TV_TIMER}" />
                <div class="hint">This is a start timer. The default is 12 hours. This means that if enabled, is will start/run every 12 hours, no matter how long it runs for.</div>
            </td>
        </tr>
    </table>
</fieldset>
</div>
<div class="tabbertab">
    <h2><a name="Advanced">Advanced</a></h2>
<fieldset>
    <legend>Miscellaneous</legend>
    <table class="input">
        <tr>
            <td style="width:160px;"><label for="NICENESS">Niceness:</label></td>
            <td>
                <input id="NICENESS" name="NICENESS" class="tiny" type="text" value="{$ftmux->NICENESS}" />
                <div class="hint">This sets the 'nice'ness of each script, default is 19, the lowest, the highest is -20 anything between -1 and -20 require root/sudo to run</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="COLLECTIONS_KILL">Maximum Collections:</label></td>
            <td>
                <input id="COLLECTIONS_KILL" name="COLLECTIONS_KILL" class="tiny" type="text" value="{$ftmux->COLLECTIONS_KILL}" />
                <div class="hint">Set this to any number above 0 and when it is exceeded, backfill and update binaries will be terminated. 0 disables.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="POSTPROCESS_KILL">Maximum Postprocess:</label></td>
            <td>
                <input id="POSTPROCESS_KILL" name="POSTPROCESS_KILL" class="tiny" type="text" value="{$ftmux->POSTPROCESS_KILL}" />
                <div class="hint">Set this to any number above 0 and when it is exceeded, import, backfill and update binaries will be terminated. 0 disables.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="WRITE_LOGS">Logging:</label></td>
            <td>
                {html_radios id="WRITE_LOGS" name='WRITE_LOGS' values=$truefalse_names output=$truefalse_names selected=$ftmux->WRITE_LOGS separator='<br />'}
                <div class="hint">Set this to write each panes output to a per pane per day log file. This adds GMT date to the filename.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="POWERLINE">Powerline Status Bar:</label></td>
            <td>
                {html_radios id="POWERLINE" name='POWERLINE' values=$truefalse_names output=$truefalse_names selected=$ftmux->POWERLINE separator='<br />'}
                <div class="hint">Choose to use the Powerline tmux status bar. To make this pretty, you need to install a patched font. This can be found on <a href="https://github.com/jonnyboy/powerline-fonts">my fork</a> or <a href="https://github.com/Lokaltog/powerline-fonts">the original git</a><br \>You will need to copy the default theme located at powerline/powerline/themes/default.sh to powerline/powerline/themes/tmux.sh and edit that file for what is displayed, colors, etc.</div>
            </td>
        </tr>
    </table>
</fieldset>
</div>
<div class="tabbertab">
    <h2><a name="ServerMons">Server Monitors</a></h2>
<fieldset>
    <legend>Server Monitors</legend>
    <table class="input">
        <tr>
            <td style="width:160px;"><label for="HTOP">htop:</label></td>
            <td>
                {html_radios id="HTOP" name='HTOP' values=$truefalse_names output=$truefalse_names selected=$ftmux->HTOP separator='<br />'}
                <div class="hint">htop - an interactive process viewer for Linux. The pane for this can not be created after tmux starts.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="NMON">nmon:</label></td>
            <td>
                {html_radios id="NMON" name='NMON' values=$truefalse_names output=$truefalse_names selected=$ftmux->NMON separator='<br />'}
                <div class="hint">nmon is short for Nigel's performance Monitor for Linux. The pane for this can not be created after tmux starts.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="VNSTAT">vnstat:</label></td>
            <td>
                {html_radios id="VNSTAT" name='VNSTAT' values=$truefalse_names output=$truefalse_names selected=$ftmux->VNSTAT separator='<br />'}
                <input id="VNSTAT_ARGS" name="VNSTAT_ARGS" class="text" type="text" value="{$ftmux->VNSTAT_ARGS}" />
                <div class="hint">vnStat is a console-based network traffic monitor for Linux and BSD that keeps a log of network traffic for the selected interface(s). Any additional arguments should be placed in the text box. The pane for this can not be created after tmux starts.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="TCPTRACK">tcptrack:</label></td>
            <td>
                {html_radios id="TCPTRACK" name='TCPTRACK' values=$truefalse_names output=$truefalse_names selected=$ftmux->TCPTRACK separator='<br />'}
                <input id="TCPTRACK_ARGS" name="TCPTRACK_ARGS" class="text" type="text" value="{$ftmux->TCPTRACK_ARGS}" />
                <div class="hint">tcptrack displays the status of TCP connections that it sees on a given network interface. tcptrack monitors their state and displays information such as state, source/destination addresses and bandwidth usage in a sorted, updated list very much like the top(1) command. <br />Any additional arguments should be placed in the text box. <br />You may need to run "sudo setcap cap_net_raw+ep /usr/bin/tcptrack", to be able to run as user. The pane for this can not be created after tmux starts.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="BWMNG">bwm-ng:</label></td>
            <td>
                {html_radios id="BWMNG" name='BWMNG' values=$truefalse_names output=$truefalse_names selected=$ftmux->BWMNG separator='<br />'}
                <div class="hint">bwm-ng can be used to monitor the current bandwidth of all or some specific network interfaces or disks (or partitions). The pane for this can not be created after tmux starts.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="MYTOP">mytop:</label></td>
            <td>
                {html_radios id="MYTOP" name='MYTOP' values=$truefalse_names output=$truefalse_names selected=$ftmux->MYTOP separator='<br />'}
                <div class="hint">mytop - display MySQL server performance info like `top'. <br />You will need to create ~/.mytop, an example can be found in 'perldoc mytop'. The pane for this can not be created after tmux starts.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="CONSOLE">Console:</label></td>
            <td>
                {html_radios id="CONSOLE" name='CONSOLE' values=$truefalse_names output=$truefalse_names selected=$ftmux->CONSOLE separator='<br />'}
                <div class="hint">Open an empty bash shell. The pane for this can not be created after tmux starts.</div>
            </td>
        </tr>

        <tr>
            <td style="width:160px;"><label for="COLORS">256 Colors:</label></td>
            <td>
                <input id="COLORS_START" name="COLORS_START" class="short" type="text" value="{$ftmux->COLORS_START}" />
                <input id="COLORS_END" name="COLORS_END" class="short" type="text" value="{$ftmux->COLORS_END}" /><br />
                <input id="COLORS_EXC" name="COLORS_EXC" class="longer" type="text" value="{$ftmux->COLORS_EXC}" />
                <div class="hint">The color displayed is tmux scripts is randomized from this list.<br />
                    The first box is the start number, the second box is the end number and the last box are the exceptions. An array is created from these numbers.<br />
                    If you connect using putty, then under Window/Translation set Remote character set to UTF-8 and check "Copy and paste line drawing characters". To use 256 colors, you must set Connection/Data Terminal-type string to "xterm-256color" and in Window/Colours check the top three boxes, otherwise only 16 colors are displayed. If you are using FreeBSD, you will need to add export TERM=xterm-256color to your .bashrc file to show 256 colors.</div>
            </td>
        </tr>
    </table>
</fieldset>
</div>
</div>
{* <input type="submit" class="tabpage" value="Save Tmux Settings" /> *}
<button id="SaveSettings" type="submit" class="btn btn-primary btn-default" style="float: right; margin-top: 15px"><i class="icon-save"></i> Save Tmux Settings</button>
</form>

