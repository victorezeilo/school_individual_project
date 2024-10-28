# Project Pulse Read me file.

## Online Deployment

1. **Online Availability**: If you encounter any difficulties with your local system configuration or if the local files do not work as expected, Project Pulse is also available for online deployment. You can access the online version via the following link: [Project Pulse Online](https://app013.smartapps4free.com).

2. **System Configuration**: Sometimes, local setups can be complex or might not perfectly mirror your deployment environment. In such cases, using the online version can be a convenient alternative to ensure that you can access and use Project Pulse without any issues.


3. **Log in**: Use the provided login credentials to access Project Pulse.

    username: viez22skola@gmail.com
    password: 123456


## Setting Up Project Pulse

Project Pulse requires specific server and software configurations to run smoothly. Follow these steps to set up the system:

### Prerequisites

- **Webserver**: 
- PHP 8.2
- MySQL 5.7 

Ensure you have a web server running PHP 8.2 or higher and MySQL 5.7 installed. If not, you'll need to install and configure these components on your server.


### Database Setup

1. **Create a Database**: Create a new MySQL database for Project Pulse. You can do this using a tool like phpMyAdmin or by running SQL commands in your MySQL shell.

2. **Restore from Dump File**: Import the database structure and initial data using the provided SQL dump file. You can do this by running the following command:

   - Going to the SQL Workbench, click on the administrator, on the navigator.
   - click on `Data Import/Restore`.
   - click on `Import from Self-Contained File`.
   - select the `dumpfile.sql` (it is located at: db/dumpfile.sql).
   - At the `Default Target Schema:`, select the database that you have just created and you will use for the project pulse.


### Configuration


3. **Update .param Files**: The `.param` files must contain project-specific configuration details (located at: config/app/.params). 

make sure to add your details below in the .param files:

    "MySQL_DB_NAME","???" <-- database name
    "MySQL_DB_USER","???" <-- user(typically root)
    "MySQL_DB_PASS","???" <-- database password
    "MySQL_DB_HOST","???" <-- host

    "ADMIN_PHONE","???"
    "ADMIN_EMAIL",""
    "WEBMASTER_PHONE",""
    "WEBMASTER_EMAIL",""
    "SUPPORT_PHONE",""
    "SUPPORT_EMAIL",""

**Note:** You need to update the `.param` files according to your specific project's location. The exact parameters within the .param files will depend on your project.

4. **Update config.inc.php**: In the `Project_Pulse/include` folder, you will find a `config.inc.php` file. Update the paths to the .param files in this configuration file to point to the correct location. These paths ensure that Project Pulse can access the necessary project-specific information.


### Software Installation


5. **Install XAMPP (if not already installed)**: If you don't have XAMPP installed, download and install it from the [official XAMPP website](https://www.apachefriends.org/index.html).

6. **Start Apache and MySQL**: After installing XAMPP, start the Apache web server and the MySQL database server using the XAMPP control panel. This is necessary to serve Project Pulse via a local web server.

7. **Xampp folder**: Place the all the submitted document (`db, Project_Pulse, config`) at the htdocs folder (`xampp/htdocs`).


### Running Project Pulse


8. **Access Project Pulse**: Open a web browser and navigate to the URL where you've placed the Project Pulse files. If you're running it locally, it's typically `http://localhost/Project_Pulse`.


### Local Development and Shared Hosting


9. **Local Development**: To run Project Pulse on your local machine, make sure you have PHP and MySQL versions 8.2 and 5.7 installed as previously mentioned. Additionally, any required local configurations can be addressed as needed. 

10. **Shared Hosting Standards**: The system should be compatible with the current shared hosting standards in general. This means that any machine set up to meet these standards should be able to run the package without any issues. Ensure that your hosting environment aligns with these standards for smooth operation.


**If there is any issue, please feel free to reach out to me.**
