# Cordova
<b>C</b>urated <b>O</b>nline <b>R</b>eference <b>D</b>atabase <b>O</b>f <b>V</b>ariation <b>A</b>nnotations

## Application
Cordova is an open source, web-based content management system for building and maintaining a database of genetic variations. It provides an interface for researchers to review and manually or computationally curate data prior to public release. Cordova offers a platform to share reliable genetic variation data for clinical diagnostics and the advancement of research.

## Recommended requirements
* Linux/Unix-based operating system (e.g. Ubuntu, CentOS, Mac, etc.)
* Apache web server
  * Must be enabled for PHP
* PHP 5.3.0 or greater
  * php-xml extension must be enabled (needed for PDF generation)
* MySQL 5.0.95 or greater
* Sendmail (needed for email service)

NOTE: We have developed Cordova using only the above specifications and have not had the chance to try other options. If you're experienced with setting up web servers and would like to try running Cordova with another database language/version or use a non-Apache web server, please feel free to give it a try. However, we're not sure how it will behave.

## Installation and configuration

### 1. Setup your web server
Depending on your operating system and preferences, there are a number of different ways to setup a web server. If you already have a web hosting service (e.g. GoDaddy, HostGator, DreamHost, etc.), have them help you out with this. If not, it's best to find someone with experience with setting up web servers. If you'd like to give it a shot yourself, there are some excellent guides available including the following:

* [Setting up a web server on Ubuntu](https://www.digitalocean.com/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu)
* [Setting up a web server on a Mac](http://www.maketecheasier.com/setup-web-server-in-mountain-lion/)

### 2. Download Cordova
Download the latest release of Cordova [here](https://github.com/clcg/cordova/releases). Uncompress the file, and put it in your web directory.

### 3. Configuration
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

In the following configuration steps, we denote the root Cordova directory as `[cordova]`. Feel free to rename this directory to whatever you would like.

#### Mandatory site configuration

##### 1. Getting started with example configuration files
All configuration files can be found in the `[cordova]/application/config/` directory. Cordova provides some example configuration files to get you started, but you must first make copies of them before editing them. The following files should be copied to the names specified below:

1. `config.php.example` should be copied to `config.php`
1. `database.php.example` should be copied to `database.php`
1. `variation_database.php.example` should be copied to `variation_database.php`

*Why copy the files? Why not rename them?* Renaming the files will work perfectly fine, too. However, in the event you accidentally screw something up (or your cat walks on your keyboard) and you need to start over, you can always re-copy one of the `.example` files.


##### 2. Configure base site URL and encryption
Your base site URL is essentially your website's homepage (e.g. *http://example.com/*, *http://cordova.example.com/*, *http://example.com/cordova/*). If you don't know how to register a domain, you can check out this [guide on domain registration](http://www.thesitewizard.com/archive/registerdomain.shtml). If you work at a university, you may be able to use a university subdomain. Check with your university's IT department to see if this is possible.

1. To set your **base URL** and **encryption**, open the `[cordova]/application/config/config.php` file.
1. Configure your **base URLs** by editing the `$base_url['development']` and `$base_url['production']` variables. For example:

    ````
    $base_url['development'] = 'http://develop.example.com/';
    $base_url['production'] = 'http://example.com/';
    ````

    If you don't have a development server, you can just leave it empty (i.e. set it to single quotes) as follows:

    ````
    $base_url['development'] = '';
    $base_url['production'] = 'http://example.com/';
    ````
1. Set your **encryption key** by editing the `$config['encryption_key']` variable. This key allows safe encryption of important data. We recommended that you generate a random encryption key by clicking [this link](http://jeffreybarke.net/tools/codeigniter-encryption-key-generator/). Copy the key, and replace the existing example key in your config file like the following example:

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

##### 4. Configure your site's specifications
1. To set your site's specifications, open the `[cordova]/application/config/variation_database.php` file.
1. Configure your **contact email address** by setting the `$config['contact_email']` variable. For this, provide an email address for users to contact you. For example:

    ````
    $config['contact_email'] = 'admin@example.com';
    ````
1. Set your site's **shorthand prefix** with the `$config['vd_prefix']` variable. For example, a good prefix for the *<b>D</b>eafness <b>V</b>ariation <b>D</b>atabase* would be *dvd*. This will be used mostly for naming downloadable files.

    ````
    $config['vd_prefix'] = 'dvd';
    ````
1. Set your site's **full name** with the `$config['strings']['site_full_name']` variable. This is simply the full name of your site (such as *Deafness Variation Database*) that will be displayed at the top of every page. For example:

    ````
    $config['strings']['site_full_name']  = 'Deafness Variation Database';
    ````
    
1. Set your site's **footer info** with the `$config['strings']['footer_info']` variable. This text will be dispayed at the bottom of each page on your site. For example:

    ````
    $config['strings']['footer_info']  = 'University of Iowa';
    ````
    You may also include any HTML.

##### 5. Configure environment
Setting Cordova's **environment** is useful when switching between development and production servers.

1. To set your site **environment**, open the `[cordova]/index.php` file. 
1. Edit the environment variable to be one of the following options. If in doubt, use **Option 1**.

    **Option 1**: `config['environment'] = 'production'` -- use this option when your site is ready to be released to the public.

    **Option 2**: `config['environment'] = 'development'` -- Use this option when your site isn't quite ready to be released to the public yet. If you only have a production server (and no development server), always use **Option 1**.
1. Save the file and close it.

##### 6. Configure admin email and password
At this point, you should have a functional website. However, you need to change the administrator password to something more secure.

1. Navigate to your homepage, and click the "Curators" link at the bottom of the page.
1. Login using the username `admin` and the password `password`.
1. In the "Admin"" menubar at the top, click the **Users** link.
1. In the **admin** user's row, click the **Edit** link.
1. Enter the administrator's email address in the **Email** field.
1. Enter a new password in the **Password** field.
1. Re-enter the password in the **Confirm Password** field.
1. Click the **Save User** button at the bottom of the page.

##### 7. Install the annotation pipeline
We've setup Cordova to seamlessly pair with *kafeen*, a local annotation pipeline. Technically, you can run Cordova just fine without *kafeen*, but we've placed this step in the **mandatory** section because we highly recommend it. If you'd just like to use Cordova for browsing and editing existing data, *kafeen* is not required. However, if you'd like to add a new variation (via the web interface), we highly recommend you install *kafeen*.

1. Download and install *kafeen* by visiting the [*kafeen* repository](https://github.com/clcg/kafeen) and following the installation guide there.
1. To enable Cordova to use *kafeen*, open the `[cordova]/application/config/variation_database.php` file.
1. Configure the location of *kafeen* by editing the `$config['annotation_path']` variable. For example:

    ````
    $config['annotation_path'] = '/opt/kafeen/';
    ````
    
1. If you installed Ruby via [RVM](https://rvm.io/) (this is a great way to install/upgrade Ruby), you need to specify the absolute path to Ruby by editing the `$config['ruby_path']` variable. If Ruby 1.9 or greater came pre-installed on your operating system or you're unsure of how Ruby was installed, you can leave the **ruby_path** empty for now and come back to it later if things don't work (you'll know it doesn't work if Cordova keeps telling you, "No data found" every time you try to add a variation). Otherwise, you can find the full path by typing `which ruby` into the command-line. Paste the response in the single quotes for **ruby_path** like the following.

    ````
    $config['ruby_path'] = '/usr/local/rvm/rubies/ruby-1.9.3-p484/bin/ruby';
    ````
    
1. Save the file and close it.

##### 8. Testing the mail server
Cordova is capable of sending emails. For this, you will need to have Sendmail installed and enabled. You can check if Sendmail is working properly by doing the following:

1. Click the "Contact Us" link in the left-side menubar on the homepage.
1. Fill out the contact form (as if you were inquiring about the site). You do not need to select "I'm interested in setting up my own variation database."
1. Click the "Submit" button.
1. An email should have been sent to the **contact email address** that you specified above. Check to see if this email sent successfully.

If this doesn't work, then Sendmail is not installed and/or enabled. Here are a couple example installation guides for Sendmail:

* [Setting up Sendmail on Ubuntu](http://www.linuxserverhowto.com/linux-mail-server-sendmail/install-sendmail-using-apt-get.html)
* [Setting up Sendmail on a Mac](http://www.informit.com/library/content.aspx?b=Mac_OS_X_Unleashed&seqNum=225)

Once installed, you can test it out again by following the above steps.
       
#### Optional (but noteworthy) site configuration

##### 1. Configure "About" page information
The "About" page that comes standard with Cordova contains a generalized description. You can modify this page however you would like. This file can be found at `[cordova]/application/views/pages/about.php`. For those who don't know HTML, we've provided a section on the page where you can easily insert whatever text you'd like. It looks as follows:

````
 <!-- Place your own welcome message between the 2 sets of asterisks (*) below -->
 <!-- ************************************************************************ -->
            
 <!-- ************************************************************************ -->
````
For those who are familiar with HTML, you can modify this entire page however you'd like.

##### 2. Configure color scheme
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

##### 3. Displaying/hiding/changing splash logos
There are two splash logos on the public-facing website, one at the top of the left-side menubar and one at the bottom of the left-side menubar. They are hidden by default, but you can change that by following the steps below.

###### Top logo ######
To unhide it:

1. Open the `[cordova]/assets/public/css/override.css` file.
1. Ignore the `display: none;` line by adding `/*` at the beginning of the line and `*/` at the end of line like the following:

    ````
/* Hide/unhide splash logo on top */
#logo a#logo-splash {
      /* display: none; */
}
    ````

1. Save the file and close it. You should now see the default logo in the top-left corner of the homepage.

To change it:

1. Take an image of your choice and crop/shrink it to **167 pixels (width) x 93 pixels (height)**.
1. Save it as a PNG file called `logo-top.png`.
1. Place this file in the `[cordova]/assets/public/img/` directory. There is already a file called `logo-top.png` in this directory (it's the default logo), so you can replace it with your own.

###### Bottom logo ######
To unhide it:

1. Open the `[cordova]/assets/public/css/override.css` file.
1. Ignore the `background-image: none;` line by adding `/*` at the beginning of the line and `*/` at the end of line like the following:

    ````
/* Hide/unhide logo on bottom */
#sidebar {
      /* background-image: none; */
}
    ````

1. Save the file and close it. You should now see the default logo in the lower-left corner of the homepage.

To change it:

1. Take an image of your choice and crop/shrink it to **100 pixels (width) x 150 pixels (height)**.
1. Save it as a PNG file called `logo-bottom.png`.
1. Place this file in the `[cordova]/assets/public/img/` directory. There is already a file called `logo-bottom.png` in this directory (it's the default logo), so you can replace it with your own.

## Developer guides
Cordova is written in [PHP](http://www.php.net/), built on the popular [CodeIgniter](http://ellislab.com/codeigniter) web application framework, and utilizes a [MySQL](http://en.wikipedia.org/wiki/MySQL) database. In addition, Cordova takes advantage of [Bootstrap](http://en.wikipedia.org/wiki/Bootstrap_(front-end_framework)), a powerful HTML/CSS/JavaScript templating system that makes developing beautiful websites much less painful.

For developers looking to expand this application but don't know where to start, you will find the following links to be useful for getting introduced and familiarized with these technologies as well as some best practices.

### Cordova API documentation
Each Cordova installation comes packaged with developer API documentation. Once you have Cordova up and running, you can point your browser to the `/docs` URL (e.g. `localhost/cordova/docs`, `example.com/docs`) to view the documentation.

### CodeIgniter 2
* [Tutorial](http://ellislab.com/codeigniter/user-guide/tutorial/index.html) -- get introduced to the CodeIgniter framework
* [Codeigniter Screencast #1 - The Perfect Model](http://www.youtube.com/watch?v=sckcHD0sYu4) -- a very good best practices video
* [Codeigniter Screencast 2 - A Master Layout](http://www.youtube.com/watch?v=OiiGh-iYPHg/) -- another very good best practices video
* [Ion Auth](http://benedmunds.com/ion_auth/) - the authentication library used by Cordova

### Bootstrap 2
* [Twitter Bootstrap Quickstart](http://www.youtube.com/watch?v=x560t2eOP6U) --  a nice, short introduction

## Licensing

Cordova is licensed under the [MIT license](https://github.com/clcg/kafeen/blob/master/LICENSE.txt). Cordova also uses third party software packages (see below) which each have their own respective licenses. You may freely utilize Cordova under the MIT license, but please be mindful of the third party licenses if you happen to modify their source code.

## Third party dependencies
Cordova depends on the following third party, open source software:

* **CodeIgniter 2** - PHP web application framework
  * Licensed under the [CodeIgniter license](http://ellislab.com/codeigniter/user-guide/license.html)
* **Bootstrap 2**
  * The code is licensed under the [Apache License v2.0](http://www.apache.org/licenses/LICENSE-2.0)
  * The documentation and Glyphicons are licensed under the [CC BY 3.0 license](http://creativecommons.org/licenses/by/3.0/)
* **Ion Auth** - authentication library for CodeIgniter
  * Licensed under the [Apache License v2.0](http://www.apache.org/licenses/LICENSE-2.0)
* **dompdf** (installs automatically; not distributed with Cordova source)
  * Licensed under the [GNU LGPLv2.1](https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html)
* **pChart** (installs automatically; not distributed with Cordova source)
  * Depending on how you use it, pChart is licensed under either the [GNU GPLv3 license or a commercial license](http://www.pchart.net/license)
  * Cordova uses pChart under the [GNU GPLv3 license](https://www.gnu.org/copyleft/gpl.html)
  * Please refer to the original pChart license on their website (link provided above) to see which license best suits your needs
  
## Troubleshooting

**Q: Only my homepage is showing. What do I do?**

**A**: You must do both of the following things:

1. In your server's HTTPD configuration (e.g. `httpd.conf`), you need to change `AllowOverride None` to `AllowOverride All`. You also need to have `FollowSymLinks` as one of the `Options`. Example:
        
    ```` 
<Directory />
      Options FollowSymLinks             # <-- HERE
      AllowOverride All                  # <-- HERE
</Directory>
<Directory "/var/www/html">
      Options Indexes FollowSymLinks     # <-- HERE
      AllowOverride All                  # <-- HERE
      Order allow,deny
      Allow from all
</Directory>
    ````
        
1. Enable `mod_rewrite` for Apache.

Here are a few nice guides for doing this:

* [Fixing this problem on Ubuntu](http://www.jarrodoberto.com/articles/2011/11/enabling-mod-rewrite-on-ubuntu)
* [Fixing this problem on a Mac](http://www.noppanit.com/apache-enable-mod_rewrite-on-macosx/)

---

**Q: Why does it say "No data found" whenever I try to submit a new variation?**

**A**: You need to specify the correct path to Ruby. *kafeen* is most likely installed properly but isn't ever run. This is because Cordova does not know the correct path to Ruby. Please refer to the configuration section above called "Install the annotation pipeline" for more information on setting the proper path to Ruby.

## Authors
[Sean Ephraim](www.linkedin.com/in/seanephraim/) | sean.ephraim@gmail.com

Nikhil Anand | mail@nikhil.io

Zach Ladley | zachary-ladlie@uiowa.edu
