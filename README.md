Please visit the official nZEDbetter Wiki at http://nzedbetter.org
The original nZEDb is available here: https://github.com/nZEDb/nZEDb

---

### Version 0.6 Released
Version 0.6 is now in the master branch.  If you are an existing user, you should be able to use the misc/autopatcher.php script to update.  If you haven't used nZEDbetter before, you can visit [the nZEDbetter Wiki](http://nzedbetter.org/index.php?title=Installation#Do_You_Want_To_Do_This_the_Easy_Way_or_the_Hard_Way.3F) for an easy to use setup script that will install everything you need to get a base Ubuntu 12 or 13 system up and running.

Speaking of Ubuntu, I have been testing non-stop on 13.10 without any issues.  This means that nZEDbetter now supports 13.04 and 13.10, 64-bit.  I have also been using PHP 5.5 for all testing for about a month with no issues, so consider it blessed as well.

This latest version incorporates some bug fixes and a few enhancements.  For the full list, please visit the [changelog on the wiki](http://nzedbetter.org/index.php?title=ChangeLog).

Lastly, the MusicBrainz integration is coming along nicely.  So far, I'm seeing a fairly decent increase in proper identification compared with the Amazon look-ups.  I plan to have the first version with MB integration completed and released by December 2nd.


---

#### 11/4 Update - New Feature Added To Keep Parts DB Small

Three major fixes/additions:

1. Fixed autopatcher.php and patchmysql.php to work correctly now.  Future changes to the database schema will be posted in the nZEDbetter/db/patches directory.  Like with nZEDb, your current database version will be tracked within the site table, so the script will know which patch(es) need to be applied.  The autopatcher.php script needs to be run with sudo to have the proper permissions to update the directories (i.e. run sudo php ./autopatcher.php from the misc/testing/DB_scripts directory).  There are a couple of things to note though.  First, in order to use the updated autopatcher in the future, you will need to manually update by typing sudo git pull from the /var/www/nZEDbetter directory.  This will only work if you haven't changed any files though.  If you get any errors, try typing sudo git fetch --all && sudo git reset --hard origin/master from the /var/www/nZEDbetter directory.  Secondly, autopatcher will also automatically run patchmysql, so you don't need to do that separately. Lastly, with the new DB patch I committed today, the patch will probably take quite a while to complete if you have a large parts database.  This is because I realized over the weekend I mistakenly left a couple of indexes in place that serve no purpose other than to waste space.  I apologize for that.

2. I added a pretty major enhancement over the weekend.  nZEDbetter will now automatically purge or convert to releases collections that have had no activity within 6 hours of either the newsgroups first post or last post.  This is pretty significant enhancement that should help prevent the parts table from growing excessively large due to incomplete binaries.  The decision as to whether to purge a collection or convert it to a release is based on an estimated percentage of how complete the collection is.  The percentage is determined by the setting in site settings for what percentage of completion to keep releases.  In other words, if you have the system set to keep releases above 95% (the default), the purge script will only convert a collection to a release if 95% of the parts are available.  Since this check is performed once an hour by the purge thread script, you may see a fairly dramatic reduction in the size of the parts table due to collections that never complete.  Right now, the only thing that can be adjusted is the site percentage setting, however, I plan to make the whole process optional, and allow you to define the number of hours to allow collections to be inactive.  I'll have that done tonight or tomorrow.

3. I fixed a couple of minor things with the backfill_predb script.  It should now work just fine.  I cloned Johnnyboys predb repository, and hope to try and update it every so often.  I also made a change to the manual predb backfill files that will allow you to run them even if there are already entries in your predb table.  The backfill_predb script can be run any time regardless of whether there are entries in the predb table or not.  My version of the repo is available at https://github.com/KurzonDax/pre-info

I plan to officially begin the MusicBrainz integration module tomorrow.  I had to let my development server, which just got rebuilt last week, backfill for a while to give me a big bunch of music binaries to test against.  Hopefully, I'll have the integration complete in about 2-3 weeks.  Stay tuned for more details...


To see screenshots of the new newsgroups section, look at https://github.com/KurzonDax/nZEDbetter/issues/29

My next major tasks are outlined below:
- [ ] Finish MusicBrainz Integrtion (2-3 weeks to complete)
- [X] Test with Ubuntu 13.10, Apache 2.4, and PHP 5.5 - Completed, all are now supported
- [ ] Get this repo moved so it's an actual fork of @nZEDb (see help request below)
- [ ] Develop Sphinx integration (unknown dev time, probably a month)
- [ ] Update search options on web front-end (1-2 weeks)

---

#### HELP!!
I need some help.  I freely admit that I'm a github newb and would rather be coding than learning
how to use it properly.  That being said, I really would like to move this repo to be a true fork
of the original nZEDb project.  The catch is, I don't want to lose my history.  I've done some
research and it seems doable, but I didn't really find a consistent method.  Anyone have any ideas?
If so, please create an issue in the Issues section with your thoughts on the best way to go about it.
I will forever be in your debt.

---

#### Latest Changes
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

#### Info

nZEDbetter is based completely on the amazing work of @johnnyboy, @sinfuljosh, and the rest of the nZEDb team.  This application would not have been possible without me standing on the shoulders of those giants.

The original nZEDb project can be found here: https://github.com/nZEDb/nZEDb.  If you have some spare change to donate, send it their way as a thanks for all of their hard work.

This project will remain open sourced, and open for user contributions.

