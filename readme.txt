=== data-triplify ===
Contributors: Douglas Paranhos & Eduardo Andrade
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: 1.0.0
Tags: data, posts, triplify

A plugin that triplifies your wordpress data.

== Description ==

A plugin to triplify your data in several formats, such as JSON-LD, RDF, XML. The user just defines the correspondences between columns in the data table and the linked data properties for a predetermined type, 

e.g.

for post_type post, the column "post_title" should be represented as "dc:title".

Done all these correspondences, the user just acess the RESTful API, which he chose the URL himself and get the data of the post_type and the chosen format, both passed as arguments in the URL.

== Installation ==

WP Data Cube Importer is very easy to install like any other wordpress plugin. No need to edit or modify anything here.

1.    Unzip the file 'wp-data-triplify.zip'.
2.    Upload the ' triplify' directory to '/wp-content/plugins/' directory using ftp client or upload and install wp-data-cube.zip through plugin install wizard in wp admin panel .
3.    Activate the plugin through the 'Plugins' menu in WordPress.


== Frequently Asked Questions ==

1. How to use the plugin?

After plugin activation you can see the ' WP Data Triplify ' menu in admin backend.
(The yellow part)
1)Choose the URL to the RESTful API.
2)Define the basis-URI of each post of the post-type to be browsed in next step.
3)Browse the post_type you wish to triplify
4)Then you should see an ordered list of columns that belong to this post_type, if it exists.
5)Fill the correspondences that are relevants to you. Ignore the others. Press the button to save the options.
6)Acess the URL chosen for the RESTful API and get the data.

2. Defining the Correspondences between the columns and the linked data properties manually is too boring, can I just upload a file containing everything I need to get the data?

Yes! That's the green part.
1)Click 'upload file'.
2)Choose the file.*
3)Click import.
4)Acess the URL chosen for the RESTful API and get the data.

*IMPORTANT: The file must have the following format.
1-Must be a .csv file.
2-Must have ;(semicolon) as delimiter.
3-The first line of the file must have the post_types you wish to triplify, semicolon separated.
4-The second line, must have the basis-URI for each post_type. It MUST have the same number of semicolons of first line.
5-All other lines must have: the column, it's correspondence and if this will show a URI or not. Semicolon separated.

3.I want a correspondence with a prefix that doesn't exists. WHat should I do?
You can upload other prefixes than the ones that already exists in the plugin. (Pink part)
1)Enter the prefix in the left input.
2)Enter the prefix's URI in the right input.
3)Click Save.
And that's all.


== Screenshots ==

????

== Changelog ==

= 1.0.0 =	
* Initial release version. Tested and found works well without any issues.
