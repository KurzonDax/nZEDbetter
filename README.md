Please visit the official nZEDbetter Wiki at http://nzedbetter.org

There isn't a lot there right now, but I am logging most major changes as
they are introduced on the main page.

# Important:
This is psuedo-fork of the original nZEDb project.  I decided not to truly fork it because of the 
sheer number of changes I've made to the original code, scripts, and database schema.  

**THIS IS A WORK IN PROGRESS, AND IS IN NO WAY, SHAPE, OR FORM STABLE AT PRESENT.**  

### 10/26 Update
Uhhg... So here't the deal:
Over the last three weeks, my day job has been unrelenting, causing me to put in close to 60 hours a week.  In addiiton, I picked up the flu somewhere along the way which put me out of commission for several days.  This has resulted in being way behind on where I wanted to be with the nZEDbetter project by now.  However, I do have the new installation wizard completed and the new database schema done.  This means that I can have a working build that can be installed by Monday, October 28th.  Unfortunately, some features aren't ready yet.  This includes the MusicBrainz and Sphinx integrations.  On the positive side, I'm building up a new development environment to begin testing against Ubuntu 13.10, Apache 2.4, and PHP 5.5.  Also, Percona has moved v5.6 to GA (meaning it's no longe beta), so if you had concerns about using an unreleased DB, worry no more.

To see screenshots of the new newsgroups section, look at https://github.com/KurzonDax/nZEDbetter/issues/29

My next major tasks are outlined below:
[ ] Finish MusicBrainz Integrtion (2-3 weeks to complete)
[ ] Test with Ubuntu 13.10, Apache 2.4, and PHP 5.5 (2 weeks to fully test)
[ ] Get this repo moved so it's an actual fork of @nZEDb (see help request below)
[ ] Develop Sphinx integration (unknown dev time, probably a month)
[ ] Update search options on web front-end (1-2 weeks)

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
- This is handy for extremely prolific groups like alt.binaries.boneless
* Option to automatically initiate a new group by the "posts" value, IF there aren't any posts within the number of days specified for new groups.  
- Good for groups that you want to index, but don't update that often.
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

