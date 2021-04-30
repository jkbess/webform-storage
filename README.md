# Webform Storage

Webform Storage saves submissions submitted via HTML form to a SQLite database. It was designed to be a simple drop-in back-end solution for a static (or mostly-static) site needing minimal integration. How you handle the front end is entirely up to you but there are some examples to get things started.


## Requirements
PHP 7.0+. with the 'sqlite3' (_not_ 'pdo_sqlite') extension. If your web form will allow users to upload images you will also need the 'gd2' extension.


## Installation and setup
If you aren't installing with Composer, you can simply save this entire directory in your project. There is an alterate autoloader included for this purpose: in Webform.php, comment or remove the `require __DIR__ . '/vendor/autoload.php'` line and uncomment `require __DIR__ . '/src/autoload.php'`.

You will need to create a configuration file that provides the path to the SQLite database and list of its fields, as explained below in **Configuration**. If you haven't create the database yet you can use the *installer.php* included in the examples. Just create your configuration file and edit the first line of the installer file.


## Configuration
Use *config.php* as a template. The configuration is an associative array with the following properties:
* **`database_path`** - the absolute path to the SQLite database *starting at the root directory*.
* **`table_name`** - the name of the database table to which web form submissions will be saved and read.
* **`primary_key`** - the name of the table column with an autoincrementing key. *This is only used by the installer, so you can omit this if you are implementing this with an existing database.*
* **`fields_to_save`** - an associate array of the fields expected to be received by a web form submission. Do not include the `submission_date_column` or `attachments` columns (see below). Each item should be structured as `column_name` => [ *properties* ] and include values for:
  * **`type`** - SQLite data type of the column: `TEXT` or `INTEGER`. SQLite also allows other datatypes but web forms submissions are sent as strings it is unlikely you'll use others. Values received for `INTEGER` fields will be converted to numeric values before being saved.
  * **`required`** - boolean; whether null/empty filed values can be accepted.
  * **`alias`** - *optional* if you do not wish to use the actual column name in the web form itself for security or readability, enter an alternate.
* **`submission_date_column`** - *optional* If specified, Webform Storage will save the YYYY-MM-DD HH:MM:SS datetime at which the form was received as a string in this column. Set to `null` if not using.
* **`attachments`** - *optional* An associate array with information about how to save and process attached files. See **Attaching files** below for more. Set this property to `null` if users will not submit files with the web form.


## Usage
See the *request-handler.php* example for a basic implementation of Webform Storage. Web forms **must** be send via POST. GET request will be ignored. The script that receives the request musti nstantiate a `new Webform` from *Webform.php* which receives the configuration file as a parameter then call the `handleRequest` method on the Webform.

The application handles both web form submission and requests to retrieve saved data. Each request must include a `request_type` parameter. The application returns a JSON object with _boolean_ 'success' and _string_ 'message' properties. If the request is to retrieve information from the database it will also include a 'data' property with an array of those records, if successful.

Web form submissions must include a `request_type` of **save_form**. The application then tries to match the other properties of the request to the columns/fields (or their aliases) in the configuration array. If all required fields are included the form data is inserted into a new database row. See *webform.html* for an example implementation.

Requests to retrieve stored data must include a `request_type` of **get_entries**. **Out of the box this application does not do any security checks** on these request. If users of the web form are submitting **any** kind of private information, you must add your own security checks to prevent the database records from being publicly assessible. Note that the *request-handler.php* example includes an empty statement in which you can insert security code.

The data returned will include all table columns. By default it will include all saved form submissions, but you can limit or sort results by including these properties with your POST request:
* **`fields`** - a comma-separated list of column/field names (or aliases) to include. If not present or set to a false or null value, all columns will be included.
* **`sort_by`** - the name of the column/field (or its alias) by which you would like the results sorted.
* **`filters`** - an array of JSON objects which will limit the results. Each JSON must have the properties:
  * **`column_name`** - the column/field (or its alias) on which to filter.
  * **`filter_value`** - the value to compare against.
  * **`comparator`** - how to check the field value against the filter value. Permitted comparators are *'EQUALS'*, *'='*, *'LESS THAN'*, *'<'*, *'GREATER THAN'*, *'>'*, *'<='*, *'>='*, *'<>'*, *'!='*, *'NOT EQUAL'*, *'LIKE'*, and *'INCLUDES'*. Note that the last two are synonymous and perform partial/wild-card string matches.
See *get-records.html* for an example implementation of fetching and displaying submitted forms.


## Attaching Files
If users will be attaching files to their submitted web forms, Webform Storage can check the POSt request for files, save them to a specified location on your server, and store their URLs as a string in the database. (This application does not save files as blobs in the database itself.) 
You can set up this feature with the **`attachments`** property of the configuration, which should be an associative array with the following properties:
  * **`save_path`** - the absolute path to the folder in which the files should be saved
  * **`column_name`** - the name of the database column in which the URL(s) of the uploaded files will be stored
  * **`required`** - whether one or more files must be included to save the web form
  * **`allowed_file_types`** - an array of the allowed file extenstions, i.e. *['pdf', 'txt']*, etc. Any files not ending in one of these extensions will be ignored.
  * **`process_images`** - boolean; when set to `true` Webform Storage will compress and/or resize submitted JPG and PNG images before saving, according to the next two properties. 
  * **`jpg_quality`** - from 1 - 100, the compression level to use when saving JPGs. If set to a false value, a default of 90 will be used.
  * **`max_image_dimensions`** - an array with `width` and `height` values; the image will be reduced, maintaining aspect ratio, so it does not exceed either of these values.


## Note about multi-line values
SQLite doesn't recognize multi-line text values, so if you have any <textarea> elements you may want to parse these inputs for line breaks and handle as needed on the front end.