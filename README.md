Please visit the official nZEDbetter Wiki at http://nzedbetter.org

---
#### 11/4 Update - New Feature Added To Keep Parts DB Small

Three major fixes/additions:

1. Fixed autopatcher.php and patchmysql.php to work correctly now.  Future changes to the database schema will be posted in the nZEDbetter/db/patches directory.  Like with nZEDb, your current database version will be tracked within the site table, so the script will know which patch(es) need to be applied.  The autopatcher.php script needs to be run with sudo to have the proper permissions to update the directories (i.e. run sudo php ./autopatcher.php from the misc/testing/DB_scripts directory).  There are a couple of things to note though.  First, in order to use the updated autopatcher in the future, you will need to manually update by typing sudo git pull from the /var/www/nZEDbetter directory.  This will only work if you haven't changed any files though.  If you get any errors, try typing sudo git fetch --all && sudo git reset --hard origin/master from the /var/www/nZEDbetter directory.  Secondly, autopatcher will also automatically run patchmysql, so you don't need to do that separately. Lastly, with the new DB patch I committed today, the patch will probably take quite a while to complete if you have a large parts database.  This is because I realized over the weekend I mistakenly left a couple of indexes in place that serve no purpose other than to waste space.  I apologize for that.

2. I added a pretty major enhancement over the weekend.  nZEDbetter will now automatically purge or convert to releases collections that have had no activity within 6 hours of either the newsgroups first post or last post.  This is pretty significant enhancement that should help prevent the parts table from growing excessively large due to incomplete binaries.  The decision as to whether to purge a collection or convert it to a release is based on an estimated percentage of how complete the collection is.  The percentage is determined by the setting in site settings for what percentage of completion to keep releases.  In other words, if you have the system set to keep releases above 95% (the default), the purge script will only convert a collection to a release if 95% of the parts are available.  Since this check is performed once an hour by the purge thread script, you may see a fairly dramatic reduction in the size of the parts table due to collections that never complete.  Right now, the only thing that can be adjusted is the site percentage setting, however, I plan to make the whole process optional, and allow you to define the number of hours to allow collections to be inactive.  I'll have that done tonight or tomorrow.

3. I fixed a couple of minor things with the backfill_predb script.  It should now work just fine.  I cloned Johnnyboys predb repository, and hope to try and update it every so often.  I also made a change to the manual predb backfill files that will allow you to run them even if there are already entries in your predb table.  The backfill_predb script can be run any time regardless of whether there are entries in the predb table or not.  My version of the repo is available at https://github.com/KurzonDax/pre-info

I plan to officially begin the MusicBrainz integration module tomorrow.  I had to let my development server, which just got rebuilt last week, backfill for a while to give me a big bunch of music binaries to test against.  Hopefully, I'll have the integration complete in about 2-3 weeks.  Stay tuned for more details...

#### 10/28 Update:
The changes to the Install pages are done.  We now have an actual wizard that walks you through the initial configuration.  Even more helpful though is a new install shell script that I put together over the weekend.  The purpose of it is to streamline the download, installation, and configuration of all required components necessary to run nZEDbetter.  This includes Percona, Apache, PHP, and necessary modules.  It also clones nZEDbetter, creates the Apache virtual host file, updates php.ini, and a few other odds and ends.

**In short, you can go from a base install of Ubuntu to a fully configured indexer in about 20 minutes.**

For more information on downloading the setup script, head over to http://nzedbetter.org/index.php?title=Installation and look at section 2.2, "Do You Want To Do This the Easy Way or the Hard Way?".

Remember, this is still 'alpha' software.  I am aware of a few bugs that exist, but it should be pretty much fully functional.  However, all new features haven't been implemented yet.

If you do run across any bugs or problems, don't hesitate to open a new issue here: https://github.com/KurzonDax/nZEDbetter/issues

#### 10/26 Update:
Uhhg... So here't the deal:
Over the last three weeks, my day job has been unrelenting, causing me to put in close to 60 hours a week.  In addiiton, I picked up the flu somewhere along the way which put me out of commission for several days.  This has resulted in being way behind on where I wanted to be with the nZEDbetter project by now.  However, I do have the new installation wizard completed and the new database schema done.  This means that I can have a working build that can be installed by Monday, October 28th.  Unfortunately, some features aren't ready yet.  This includes the MusicBrainz and Sphinx integrations.  On the positive side, I'm building up a new development environment to begin testing against Ubuntu 13.10, Apache 2.4, and PHP 5.5.  Also, Percona has moved v5.6 to GA (meaning it's no longe beta), so if you had concerns about using an unreleased DB, worry no more.

To see screenshots of the new newsgroups section, look at https://github.com/KurzonDax/nZEDbetter/issues/29

My next major tasks are outlined below:
- [ ] Finish MusicBrainz Integrtion (2-3 weeks to complete)
- [ ] Test with Ubuntu 13.10, Apache 2.4, and PHP 5.5 (2 weeks to fully test)
- [ ] Get this repo moved so it's an actual fork of @nZEDb (see help request below)
- [ ] Develop Sphinx integration (unknown dev time, probably a month)
- [ ] Update search options on web front-end (1-2 weeks)

---

### HELP!!
I need some help.  I freely admit that I'm a github newb and would rather be coding than learning
how to use it properly.  That being said, I really would like to move this repo to be a true fork
of the original nZEDb project.  The catch is, I don't want to lose my history.  I've done some
research and it seems doable, but I didn't really find a consistent method.  Anyone have any ideas?
If so, please create an issue in the Issues section with your thoughts on the best way to go about it.
I will forever be in your debt.

---

### Latest Changes
Some of the things I've added most recently:  
* Admin seciton has a new look.  It isn't complete yet, but the groups administration section is done.
* Some minor bug fixes with the front end templates
* Added optional ability to capture Amazon rating for books, console, and music.
* Auto-suggestions for Authors, Genres, and Publishers in eBook search (soon to be added to search fields in Console, Music, and maybe movies also)
* Ability to filter search results based on Amazon customer ratings
* Moved the collection/binary/parts purging to a separate process from the Update Releases.
* Some major purging functions (like old releases, parts out of retention, etc.) now happen only on a user selectable schedule.  
* Drastic changes to the movie identification process. Should see much less mismatched movies when browsing the database.  
* A number of fixes to the part repair process, including ability to run the part repair process manually.  
* New "Hashed Releases" category under "Other", along with a number of regexes to automatically move releases there.  
- Includes ability now to set a separate retention time for hashed releases
* Improved the NZB import process to ensure that once an NZB is imported from a directory, it won't be imported again if you need to stop and restart the import process.  
* Ability to limit the number of inital posts retrieved for a group when using the date option for new groups.  
    This is handy for extremely prolific groups like alt.binaries.boneless
* Option to automatically initiate a new group by the "posts" value, IF there aren't any posts within the number of days specified for new groups.  
    Good for groups that you want to index, but don't see updates that often.
* Numerous other bug fixes and improvements  

### Info

The original nZEDb project can be found here: https://github.com/nZEDb/nZEDb

Some of the major changes I'm working on integrating:  
* Reworking the update binaries process to not only improve performance, but also fix some, what I felt were, significant bugs in the process.  
* Reworking the update releases stages.  Again, the main goal is improving the performance, but also fixing bugs along the way.  
* Standardizing on the InnoDB storage engine, and experimenting with various configurations to optimize settings.  
* All work is being tested using Percona Server, v5.6. While this is still an unreleased version, I feel there are enough improvements in it to warrant its use.  Additionally, it provides significantly better logging and instrumentation over MySQL.    
* Improving the categorization process.  

Future plans:  
* Reintegrating Sphinx full text indexing.  Newznab plus had this, though the support of it seemed a bit questionable.  nZEDb removed it.  I'm going to take a stab at putting it back in.  To be honest, it will depend on the cost-to-benefit ratio.  If it negatively impacts stability and performance, then I may scrap the idea.
* Revising the web front end.  This will focus mainly on better search capabilities (with or without Sphinx).  Unfortunately, I'm a much better at coding than I am at making web sites look awesome, but we'll see what I can come up with.

This project will remain open sourced, and open for user contributions.

