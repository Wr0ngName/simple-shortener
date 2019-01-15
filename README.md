# Simple Shortener

A simple URL Shortener...


## Introduction

Originally, I've found the sources for this project via GitHub (https://github.com/azlux/Simple-URL-Shortener). However, I was not satisfied by how the security was handled, nor the userID generated, and a hole bunch of details.

These aspects have driven me to start a fork of this, and make it differently. Also, most of the functionnalties have been kept, some have been added, but a big part of this inner skeleton has been redeveloped.


## Prerequisites

In order to run this, you need :
 - A web hosting solution (Apache, Nginx,..);
 - A database and the related PHP connector (PDO) for MySQL or Sqlite3;
 - PHP 7 (I guess v5.6 is enough, not tested);
 - A webrowser supporting JavaScript;

 
 ## Functionalities
 
 - Simple "per user" management;
 - Bookmarklet for a quick storing;
 - Google ReCAPTCHA v2 ready;
 - 35 characters comments, to store details about the link;
 - Unique ID generator from https://github.com/kvz/locutus/blob/master/src/php/misc/uniqid.js ;
 - Prepared queries to avoid injections;
 - Works from everywhere (subdirectory, root location, ..);
 - Visits counter;


## Deployment

- Unzip the content where you want to implement the Simple Shornener;
- Access the folder where you extracted the files with your web browser. You should have been redirected to the 'install.php' file, if not, aim for it;
- Fill the fields as follow:
  - The type of database to use;
  - The name for your instance to deploy (title for your pages);
  - If you want to deploy Google ReCAPTCHA v2, enter your site's key and secret (find it here: https://www.google.com/recaptcha/admin)
- Once done, submit!
  - If you have choosen the Sqlite3 database for storage, you are almost done!
  - If you prefer MySQL, after submitting, you are now in from of a next form, to fill your database server informations (host, user, password,db name).
- When you see the end, a message reminds you to delete the installation script, for security reasons. Once done,you can now access and use your URL shortener! (If you don't remove the file, you will start the install wizard again and again...)

NOTE : If you have troubles, here are a few tips:
 - At start, the script will write its configuration in the core/ directory, it needs to be writable for the installation to succeed. Don't forget to switch back once done!
 - If you are using Nginx, you can remove the .htaccess files used by Apache. In order to configure your server, use:

```NGINX
if (!-e $request_filename) {
    	rewrite ^/([^/]*)$ /index.php?site=$1 last;
}
```
Remove direct access to the core files with :
```NGINX
location ~* (bdd\.php|config\.php|database\.sqlite3|\.htaccess)$ {
        deny all;
}
```


## Configuration / Tunning

Most of the configuration is done with the installer. However, you can feel the need to tune and modify your installation!
Here are the different files:
  - index.php: the main part of the solution. Handles adding, redirecting and other actions.
  - list.php: allows every user to get a list of the links already submitted, with the ability to purge the old ones.
  - install.php: installation wizard. To access for the first time, and to delete once completed.
  - core/: folder containing all the sensible data (database, config files);
    - bdd.php: contains the settings to connect the database.
    - config.php: stores ReCAPTCHA settings, and instance's title.
    - database.sqlite3: if you have choosen the Sqliite3 storage, the database file will be created here.
  - static/: folder grouping the scripts, stylesheets and pictures of the shortener.
    - common.css: main cascade stylesheet, used to setup most of the environment.
    - list.css: stylesheet specific to the list.php webpage (table, bulk actions,..).
    - sunset.png: background used on the pages;
    - delete-icon.png: inline deletion button (list.php)
  - .htaccess: Denies direct access to files inside the core directory (Apache only! see above for Nginx);


## Future

Feel free to ask for more functionalties, or to develop them and request a change! ;-)
