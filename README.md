# DreamJournal
Originally, I was self hosting this for myself. Here you go internet, a treat. A Web based app to host on your VPN/Home Server for recording all your dreams. I used to use discord, but that platform has died off because of regolations. Anyways, this is super easy to setup: only three pages and a mySQL database.

#### The Database
I named my database journal, but it doesn't matter. Just make sure you set the name correctly in all files - stats.php, index.php, and achievements.php. Alongside that, just create a standard mysql database with the following:

* Table: dreams
* Text Field: content
* Date Field: dream_date

And of corpse, set the $username $password and $dbname. I know this is the old unsecure way to do this, but it's only a dream journal. Feel free to make a separate file for mySQL information - If you know how.

Like i said, easy setup. We have three features here:

+ Achievements Page: Make it a game! Awards for total dreams, and special merits for long term users.
+ Stats Page: Interesting stats about your dreams: Yearly and Monthly Trends, Streaks, and More.
