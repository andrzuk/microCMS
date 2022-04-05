# microCMS
## A small and simple CMS made with no any framework.
Frontend is based on HTML, JavaScript (jQuery) and CSS. Backend is based on PHP and MySQL database. Communication between frontend and backend runs through API using AJAX requests. Installer is included. Front web page is based on free responsive template. Admin Panel also! :)
## Installation steps:
- upload project files to hosting to 'public_html' folder
- enable uploading images by setting attribute 777 for folder '/upload'
- write down your website URL into config file '/config/config.php' in section 'Domain URL'
- create empty MySQL database (and remember database name, user and password)
- write down these data into config file '/config/config.php' in section 'Database connection'
- create Email account on your hosting (for sending emails, especially for admin password recovery)
- write down your email account address into config file '/config/config.php' in section 'Mail account params'
- open URL of your domain address (http(s)://your-domain.com) in web browser
- click button 'Install Backend of Application' on front page or request URL '/install' (http(s)://your-domain.com/install)
- register yourself as ADMIN.
## Reset app:
- backup your database if needed - all data will be removed and set to default values
- upload files 'index.html', 'styles.css', 'page.js' and 'register.php' to folder '/install'
- request URL '/install' (http(s)://your-domain.com/install) in web browser
- register yourself as ADMIN.
## On-line: http://exe-system.pl

