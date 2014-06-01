twitter-scraper
===============

Realtime Scraper for Twitter based on the Twitter API

# SETUP

## Config
Rename config.sample.php to config.php or create a copy by this name.
Go to developer.twitter.com and create an API Key, the Callback URL you are asked for is the url to the location on your server where you will upload this script.
Enter your database credentials and the API Key credentials.

## Initialisation
After uploading all files to your server access 0-create_database.php on your webserver.
This will create the required databases on your server. As well as aquiring the required Security Tokens to communicate to the Twitter API later on.
If everything works out this should guide you through the initialisation process.

## Running the Script
After the initialisation you have two ways of executing the scraping process.

### Cron Execution

The best way to deal with this, especially if you want to execute this script for a week or longer is setting up a cron-job. There are lots of documents on the web on how to setup a cron-job. Depending on your hosting service you might even have a frontend.
This projects holds a folder named "cron" it contains two HTML documents.
You need to create a cron-job for each HTML document. 
"cron_tweets.html" is gathering the actual tweets from the API and "cron_users.html" is looking up data on the users gathered through the tweet-data.

### Browserbased Execution



## Using the Data

You should be carefully gathering the data, the amount of data can grow really fast. So depending on your hosting situation you should have an eye on the size of your database.
While the script is running you can use "info/index.php" to check how much data has been gathered. Depending on the amount of data gathered the page might take a while to load.

## Exporting the Data

Don't even attempt to export the data to any kind of webbased tool like PhpMyAdmin. 
Get a proper sql desktop tool to download the data for further processing.