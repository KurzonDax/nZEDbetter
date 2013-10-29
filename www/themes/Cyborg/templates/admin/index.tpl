
<h2>Introduction</h2>
<p>
	Welcome to nZEDbetter. In this area you will be able to configure many aspects of your site.<br>
</p>

	<ol style="list-style-type:decimal; line-height: 180%;">
	<li style="margin-bottom: 15px;">Configure your <a href="{$smarty.const.WWW_TOP}/site-edit.php">site options</a>. The defaults will probably work fine, but you will need
        to fill in API keys if you have them, adjust release lookups, etc. You'll also need to review the thread settings to make sure you aren't launching more threads than
        the maximum number of connections your service provider supports.
    </li>
    <li style="margin-bottom: 15px;">
        Next, take a look at the <a href="{$smarty.const.WWW_TOP}/tmux-edit.php">tmux script settings</a>.  By default, these are set conservatively.  Adjust them as appropriate.
    </li>
	<li style="margin-bottom: 15px;">There is a default list of usenet groups provided. To get started, you will need to <a href="{$smarty.const.WWW_TOP}/group-list.php">activate some groups</a>.
        <strong>Do not</strong> activate every group immediately though. See the recommend procedure below.
	You can also add your own groups from the Newsgroups page.</li>
	<li style="margin-bottom: 15px;">Next you will want to get the latest headers. <b>This should be done from the command line</b>, using the linux shell scripts found in /misc/update_scripts/nix_scripts.  Don't manually run
        individual scripts unless you have good reason to though.  Instead, use the tmux script located at /misc/update_scripts/nix_scripts/tmux/start.php.  This will handle everything for you.
    </li>
	<li style="margin-bottom: 15px;">If you intend to keep using nZEDbetter, consider signing up for your own api keys from <a href="http://www.themoviedb.org/account/signup">tmdb</a>, <a href="http://trakt.tv">trakt</a>, <a href="http://developer.rottentomatoes.com/">rotten tomatoes</a> and <a href="http://aws.amazon.com/">amazon</a>.</li>
	</ol>
<br />
<h2>Getting Started with Newsgroups</h2>
<p>You may be anxious to get started indexing, but it's important to follow a few recommendations to help ensure a good experience, and to not overwhelm your server.</p>
<p>The first thing you need to oconsider is what groups are important to you.  Unless you have a server with large amounts of RAM (greater than 32MB), you probably won't be able
to successfully index all 350+ groups.  If there are topics such as particular game consoles, certain styles of music, or adult material that you aren't interested in, feel free
to delete the groups associated with that particular subject.  Many of the group names are self explanatory.  In other cases, a description is provided.  You can easily edit the
descriptions by double clicking on them.</p>
<p>Once you've narrowed down the list, or decided on the ones you are interested in, here is the general procedure for getting going:</p>
<ol style="list-style-type:decimal; line-height: 180%;">
    <li style="margin-bottom: 15px;">
        Do <strong>not</strong> activate all of the groups at once. Instead, choose 20 or so that you would like to start with and activate those, but don't
        enable backfill immediately.  <strong style="color: crimson">If you haven't setup an indexer before</strong> it is strongly recommended that you only activate a couple of groups
        at first until you are certain there are no issues with your configuration.
    </li>
    <li style="margin-bottom: 15px;">
        Once you've activated some groups, you can begin the indexing process.  This is most easily handled by using the start.php script located in the
        /misc/update_scripts/nix_scripts/tmux/ directory.  After changing to that directory from a command line, just type php ./start.php and watch the
        magic happen.
    </li>
    <li style="margin-bottom: 15px;">
        It may take some time for your initial groups to completely update, depending on how prolific they are, and how far back you configured
        nZEDbetter to start new groups.  Generally, a good way to configure the system is to start new groups to go back 1 day, or 500,000 posts, which
        is the default.  You should also enable the option to automatically switch to posts if days are selected (this will make more sense when you look at the
        site configuration options).  Don't start activating additional groups until the first batch is completely updated.
    </li>
    <li style="margin-bottom: 15px;">
        Once your initial groups have completely updated, go ahead and enable backfill for just those groups.  Ideally, you should let them completely backfill
        before activating any more groups to update.  However, if you have a large amount of RAM, and a fast hard disk subsystem (i.e. RAID 10 array), you can
        go ahead and activate another batch of groups to be updated, assuming you have enough connections with your service provider to support updating and
        backfilling simultaneously.  You can adjust the number of threads used for each process on the site settings page.
    </li>
    <li style="margin-bottom: 15px;">
        Continue this process over the course of several days, or a week or more, until all of the groups you want to index are activated and backfilled.
        Remember, however, the less RAM you have installed, the less the total number of groups your can realistically index.  Unfortuantely, the initial updating and
        backfill process can take quite a while to complete, if done correctly.  However, you will be rewarded with a stable site that is filled with content.
    </li>
</ol>
