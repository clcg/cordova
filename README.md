# Cordova
<b>C</b>urated <b>O</b>nline <b>R</b>eference <b>D</b>atabase <b>O</b>f <b>V</b>ariation <b>A</b>nnotations

## Application
Cordova is an open source, web-based content management system for building and maintaining a database of genetic variations. It provides an interface for researchers to review and manually or computationally curate data prior to public release. Cordova offers a platform to share reliable genetic variation data for clinical diagnostics and the advancement of research.

## Recommended requirements
* Linux/Unix-based operating system (e.g. Ubuntu, CentOS, Mac, etc.)
* Apache web server
* PHP 5.3.0 or greater
  * php-xml extension
* MySQL 5.0.95 or greater

NOTE: We have developed Cordova using only the above specifications and unfortunately have not had the chance to try other options. If you're experienced with setting up web servers and would like to try running Cordova with another database language/version or use a non-Apache web server, please feel free to give it a try. However, we're not sure how it will behave.

## Installation and configuration

### Install the annotation pipeline
HOW TO INSTALL????

### Setup your web server
Depending on your operating system and preferences, there are a number of different ways to setup a web server. If you already have a web hosting service (e.g. GoDaddy, HostGator, DreamHost, etc.), have them help you out with this. If not, it's best to find someone with experience with setting up web servers. If you'd like to give it a shot yourself, there are some excellent guides available.

### Download Cordova
Download the latest release of Cordova [here](https://github.com/clcg/cordova/releases).

### Put Cordova in your web directory
You will need to use the command line for this.

1. Open the command line.
    * [How do I open it on Ubuntu?](http://askubuntu.com/questions/196212/how-do-you-open-a-command-line)
    * [How do I open it on Mac?](http://www.wikihow.com/Get-to-the-Command-Line-on-a-Mac)
1. Put Cordova in your web directory.
    1. Ubuntu: /var/
    1. blah 

### Configuration
To configure the site, you will need to edit some of the Cordova files. If you're unfamiliar with PHP or HTML, that's okay. The amount of editing is very minimal.

This guide will provide information for configuring your site using the configuration files. Some configuration options provide additional information within the configuration files, so please be sure to read all of the information provided before editing.

Each configuration option (or group of options) has been clearly labeled within the configuration files. For example, the base site URL would be labeled as

````
/*
|----------------------------------------------------
| Base Site URL
|----------------------------------------------------
````
Additional information about an option will be provided directly below its label.

The configuration section has been divided into two sections: **mandatory** and **optional** site configuration. Mandatory site configuration is the minimal configuration needed to get your variation database website working. Optional site configuration provides noteworthy options for personalization, but the default values should suffice. There are many other options not mentioned in this guide that can be found in the configuration files, but their default values should also suffice.

#### Mandatory site configuration

##### 1. Getting started with example configuration files
All configuration files can be found in the `[cordova]/application/config/` directory. Cordova provides some example configuration files to get you started, but you must first make copies of them before editing them. The following files should be copied to the names specified below:

1. `config.php.example` should be copied to `config.php`
1. `database.php.example` should be copied to `database.php`
1. `variation_database.php.example` should be copied to `variation_database.php`

##### 2. Configure base site URL and encryption
Your base site URL is essentially your website's homepage (e.g. *http://example.com/*, *http://cordova.example.com/*, *http://example.com/cordova/*). If you don't know how to register a domain, you can check out this [guide on domain registration](http://www.thesitewizard.com/archive/registerdomain.shtml). If you work at a university, you may be able to use a university subdomain. Check with your university's IT department to see if this is possible.

1. To set your base URL and encryption, open the `[cordova]/application/config/config.php` file.
1. Configure your base URLs by editing the `$base_url['development']` and `$base_url['production']` variables. For example:

    ````
    $base_url['development'] = 'http://develop.example.com/';
    $base_url['production'] = 'http://example.com/';
    ````

    If you don't have a development server, you can just leave it empty (i.e. set it to single quotes) as follows:

    ````
    $base_url['development'] = '';
    $base_url['production'] = 'http://example.com/';
    ````
1. Set your **encryption key** by editing the `$config['encryption_key']` variable. This key allows safe encryption of important data. We recommended that you generate a random encryption key by clicking [this link](http://jeffreybarke.net/tools/codeigniter-encryption-key-generator/). Copy the key, and replace the existing example key your config file like the following example:

    ````
    $config['encryption_key'] = 'Al6ZqeJt4HsoS2PWy0OrgETluEKlcaPX';
    ````

1. Save the file and close it.

##### 3. Configure database credentials
You will need to provide Cordova with the proper credentials to access your database. You will need to provide three things: the databases's name, username, and password.

1. To set your database credentials, open the `[cordova]/application/config/database.php` file.
1. To set your production environment, look for the `$db['production']['username']`, `$db['production']['password']`, and `$db['production']['database']` variables, and set them accordingly. For example:
    
    ````
    $db['production']['username'] = 'cordovauser';
    $db['production']['password'] = 'my_secret_password';
    $db['production']['database'] = 'cordova';
    ````
    
    If you have a development server, you can set the `$db['development']['username']`, `$db['development']['password']`, and `$db['development']['database']` variables as well. If not, you can leave these alone.
1. Save the file and close it.

##### 4. Configure your site's specifics
1. To set your site's specifics, open the `[cordova]/application/config/variation_database.php` file.
1. Configure your **contact email** by setting the `$config['contact_email']` variable. For this, provide an email address for users to contact you. For example:

    ````
    $config['contact_email'] = 'admin@example.com';
    ````
1. Set your site's **shorthand prefix** with the `$config['vd_prefix']` variable. For example, a good prefix for the ***D**eafness **V**ariation **D**atabase* would be *dvd*. This will be used mostly for for downloadable files.

    ````
    $config['vd_prefix'] = 'dvd';
    ````
1. Set your site's **full name** with the `$config['strings']['site_full_name']` variable. This is simply the full name of your site such as *Deafness Variation Database*. For example:

    ````
    $config['strings']['site_full_name']  = 'Deafness Variation Database';
    ````
1. Set your site's **footer info** with the `$config['strings']['footer_info']` variable. This text will be dispayed at the bottom of each page on your site. For example:

    ````
    $config['strings']['footer_info']  = 'University of Iowa';
    ````
    You may also include any HTML.

1. Set the **annotation tool paths** with the `$config['strings']['annotation_path']` and `$config['strings']['ruby_path']` variables.
    1. The **annotation_path** is the path to your annotation pipeline. A good place to store this is in the `/opt/` directory. For example, if you're using the *kafeen* annotation pipeline, your path might look like `/opt/kafeen/`. See below for full example.
    1. If you are using *kafeen* for annotation and you installed Ruby via RVM then you may need to specify the absolute path to Ruby, or problems may occur. If Ruby came pre-installed on your operating system or you're unsure of how Ruby was installed, you can leave the **ruby_path** empty for now and come back to it later if things don't work. Otherwise, you can find the full path by typing in `which ruby` from the command-line. Paste the response in the single quotes for **ruby_path**.
        
        ````
        $config['annotation_path'] = '/opt/kafeen/';
        $config['ruby_path'] = '/usr/local/rvm/rubies/ruby-1.9.3-p484/bin/ruby';
        ````

##### 0. Configure environment
Setting Cordova's *environment* can be useful when switching between development and production servers.

1. To set your site environment, open the `[cordova]/index.php` file. 
1. Edit the environment variable to be one of the following options. If in doubt, use **Option 1**.

    **Option 1**: `config['environment'] = 'production'` -- use this option when your site is ready to be released to the public.

    **Option 2**: `config['environment'] = 'development'` -- Use this option when your site isn't quite ready to be released to the public yet. If you only have a production server (and no development server), always use **Option 1**.
1. Save the file and close it.

##### 0. Configure admin password
At this point, you should have a functional website. All we need to do is BLAH BLAH BLAH

#### Optional site configuration

##### 0. Configure "About" page information
The "About" page that comes standard with Cordova contains a "one-size-fits-all" description. However, you can modify this page however you would like. This file can be found at `[cordova]/application/views/pages/about.php`. For those who don't know HTML, we've provided a section on the page where you can easily insert whatever text you'd like. It looks as follows:

````
 <!-- Place your own welcome message between the 2 sets of asterisks (*) below -->
 <!-- ************************************************************************ -->
            
 <!-- ************************************************************************ -->
````
For those who are familiar with HTML, you can modify this page however you'd like. However, we ask that you please leave the PHP code where it is.

##### 0. Configure color scheme
You can choose a different color scheme for your website by overriding the default cascading style sheets (CSS). 

1. Find the hex value (e.g. #002B41, #580000) for your desired color. You can refer to this [list of hex values](http://www.w3schools.com/html/html_colors.asp) if you'd like.
1. Open the `[cordova]/assets/public/css/override.css` file.
1. Replace the existing hex value with your own.
1. Uncomment the `background-color` line by removing `/*` and `*/`. The file should now look as follows:
    
    ````
    /* Override color theme */
    #sidebar,
    #sidebar-sorters fieldset,
    #sidebar-sorters fieldset legend,
    #sidebar-sorters-alphabet table {
      background-color: #580000;
    }
    ````
    
1. Save the file and close it.

### Apache
* Make sure your Apache web server is configured to run PHP
* Install the following extensions (and then restart Apache)
  * php-xml <~~ Needed for PDF generation via dompdf

### Config files
* Change [$config['base_url']](http://stackoverflow.com/questions/6449386/base-url-function-not-working-in-codeigniter) in /config/config.php
* Change [$config['encryption_key']](http://stackoverflow.com/questions/6173769/encryption-key-in-codeigniter) in /config/config.php -- you can use one of the random CodeIgniter encryption keys generated [*here*](http://randomkeygen.com)
* Additional configuration for authentication can be found in /third_party/ion_auth/config/config.php

### Database
REPLACE THIS TEXT

### Email
Be sure to setup sendmail on your server. Additional email setup should be configured in /config/email.php. Refer to the [CodeIgniter email docs](http://ellislab.com/codeigniter/user-guide/libraries/email.html) for more information.

## Developer Guides
Cordova is written in [PHP](http://www.php.net/), built on the popular [CodeIgniter](http://ellislab.com/codeigniter) web application framework, and utilizes a [MySQL](http://en.wikipedia.org/wiki/MySQL) database. In addition, Cordova takes advantage of [Twitter Bootstrap](http://en.wikipedia.org/wiki/Bootstrap_(front-end_framework)), a powerful HTML/CSS/JavaScript templating system that makes developing beautiful websites much less painful.

For developers looking to expand this application but don't know where to start, you will find the following links to be useful for getting introduced and familiarized with these technologies as well as some best practices.

### CodeIgniter 2
* [Tutorial](http://ellislab.com/codeigniter/user-guide/tutorial/index.html) -- get introduced to the CodeIgniter framework
* [Codeigniter Screencast #1 - The Perfect Model](http://www.youtube.com/watch?v=sckcHD0sYu4) -- a very good best practices video
* [Codeigniter Screencast 2 - A Master Layout](http://www.youtube.com/watch?v=OiiGh-iYPHg/) -- another very good best practices video
* [Ion Auth](http://benedmunds.com/ion_auth/) - the authentication library used by Cordova

### Twitter Bootstrap 2
* [Twitter Bootstrap Quickstart](http://www.youtube.com/watch?v=x560t2eOP6U) --  a nice, short introduction

## License
Please refer to the CodeIgniter 2 and Twitter licenses.

## Authors
Sean Ephraim | sean-ephraim@uiowa.edu

Nikhil Anand

Zach Ladley

