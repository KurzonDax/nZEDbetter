Please visit the official nZEDbetter Wiki at http://nzedbetter.org

There isn't a lot there right now, but I am logging most major changes as
they are introduced on the main page.

# Important:
This is psuedo-fork of the original nZEDb project.  I decided not to truly fork it because of the 
sheer number of changes I've made to the original code, scripts, and database schema.  

**THIS IS A WORK IN PROGRESS, AND IS IN NO WAY, SHAPE, OR FORM STABLE AT PRESENT.**  

Feel free to clone it and have a look at the changes made.  Currently, they nearly all involve the
binaries.php and releases.php files, as I'm working to improve the overall performance of 
the retrieving and inserting new items in to the database.  I'm also doing a ton of experimentation
with MySQL to establish baselines, and then profiling performance based on the changes I am
making.  The goal of the project as a whole is to optimize performance on generally available 
commodity hardware, and under virtual environments.  

## Latest Changes 
Some of the things I've added most recently:  
	* Moved the collection/binary/parts purging to a separate process from the Update Releases.  
	* Some major purging functions (like old releases, parts out of retention, etc.) now happen only on a user selectable schedule.  
	* Drastic changes to the movie identification process. Should see much less mismatched movies when browsing the database.  
	* A number of fixes to the part repair process, including ability to run the part repair process manually.  
	* New "Hashed Releases" category under "Other", along with a number of regexes to automatically move releases there.  
		+ Includes ability now to set a separate retention time for hashed releases  
	* Improved the NZB import process to ensure that once an NZB is imported from a directory, it won't be imported again if you need to stop and restart the import process.  
	* Ability to limit the number of inital posts retrieved for a group when using the date option for new groups.  
		+ This is handy for extremely prolific groups like alt.binaries.boneless  
	* Option to automatically initiate a new group by the "posts" value, IF there aren't any posts within the number of days specified for new groups.  
		+ Good for groups that you want to index, but don't update that often.
	* Numerous other bug fixes and improvements  

## Info  

The original nZEDb project can be found here: https://github.com/nZEDb/nZEDb

Some of the major changes I'm working on integrating:  
	* Reworking the update binaries process to not only improve performance, but also fix some, what I felt were, significant bugs in the process.  
	* Reworking the update releases stages.  Again, the main goal is improving the performance, but also fixing bugs along the way.  
	* Standardizing on the InnoDB storage engine, and experimenting with various configurations to optimize settings.  
	* All work is being tested using Percona Server, v5.6. While this is still an unreleased version, I feel there are enough improvements in it to warrant its use.  Additionally, it provides significantly better logging and instrumentation over MySQL.    
	* Improving the categorization process.  

Future plans:  
	* Reintegrating Sphinx full text indexing.  Newznab plus had this, though the support of it
	  seemed a bit questionable.  nZEDb removed it.  I'm going to take a stab at putting it back
	  in.  To be honest, it will depend on the cost-to-benefit ratio.  If it negatively impacts
	  stability and performance, then I may scrap the idea.  
	* Revising the web front end.  This will focus mainly on better search capabilities (with or
	  without Sphinx).  Unfortunately, I'm a much better at coding than I am at making web sites
	  look awesome, but we'll see what I can come up with.  

This project will remain open sourced, and open for user contributions.

