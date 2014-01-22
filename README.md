# vadaman
**Va**riation **Da**tabase **Man**ager

## Application
This repo is intended to serve as a quick starting point for building a web application. The base application is built on [*CodeIgniter*](http://ellislab.com/codeigniter), a PHP web application framework. It also utilizes [*Bootstrap*](http://twitter.github.com/bootstrap/index.html) as a CSS/JS framework. It includes fundamental but often time-consuming features such as a navigation bar and a login system.

## Setup
### Apache
* Make sure your Apache web server is configured to run PHP
* Install the following extensions (and then restart Apache)
  * php-xml   <~~ Needed for PDF generation via dompdf

### Config files
* Change [*$config['base_url']*](http://stackoverflow.com/questions/6449386/base-url-function-not-working-in-codeigniter) in /config/config.php
* Change [*$config['encryption_key']*](http://stackoverflow.com/questions/6173769/encryption-key-in-codeigniter) in /config/config.php -- you can use one of the random CodeIgniter encryption keys generated [*here*](http://randomkeygen.com)
* Additional configuration for authentication can be found in /third_party/ion_auth/config/config.php

### Database
Run the .sql script provided by [*Ion Auth*](https://github.com/benedmunds/CodeIgniter-Ion-Auth).

### Email
Be sure to setup sendmail on your server. Additional email setup should be configured in /config/email.php. Refer to the [*CodeIgniter email docs*](http://ellislab.com/codeigniter/user-guide/libraries/email.html) for more information.

## Developers
For developers looking to expand this application but don't know where to start, you will find the following links very useful for getting introduced and familiarized with the frameworks and some of the techniques utilized.

### CodeIgniter
* [*Tutorial*](http://ellislab.com/codeigniter/user-guide/tutorial/index.html) -- create a simple blog-like news app 
* [*Codeigniter Screencast #1 - The Perfect Model*](http://www.youtube.com/watch?v=sckcHD0sYu4) -- a very good best practices video
* [*Codeigniter Screencast 2 - A Master Layout*](http://www.youtube.com/watch?v=OiiGh-iYPHg/) -- another very good best practices video

### Bootstrap
* [*Twitter Bootstrap Quickstart*](http://www.youtube.com/watch?v=x560t2eOP6U) --  a nice, short introduction

## License
Please refer to the CodeIgniter 2 and Twitter licenses.

## Authors
Sean Ephraim | sean-ephraim@uiowa.edu
Nikhil Anand
Zach Ladley

