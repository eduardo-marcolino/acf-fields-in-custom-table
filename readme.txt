=== ACF: Fields in Custom Table ===
Contributors: eduardo.marcolino
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TW8LTG6J7XVP2&item_name=Donation+for+Wordpress+Plugin&currency_code=USD
Tags: acf,advanced custom fields,fields,meta,custom fields
Requires at least: 4.9.0
Tested up to: 5.5.3
Stable tag: 0.3
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Stores ACF custom fields in a custom table instead of WordPress core meta tables.

== Description ==

This ACF plugin makes it possible to store ACF data in structured database tables instead of WordPress core meta tables.

It uses ACF's `acf/update_field_group` hook to create/update the database and then uses `acf/save_post` hook to store the data.

It was heavily inspired by Austin Ginder's post [https://anchor.host/acf-custom-fields-stored-in-custom-table/](https://anchor.host/acf-custom-fields-stored-in-custom-table/).

You can contribute to this plugin by submit PR/Issue in [https://github.com/eduardo-marcolino/acf-fields-in-custom-table](https://github.com/eduardo-marcolino/acf-fields-in-custom-table).

= Supported Fields =

* Text
* Text Area
* Number
* Range
* Email
* URL
* Password
* Image
* File
* Wysiwyg Editor
* oEmbed
* Select
* Checkbox
* Radio Button
* Button Group
* True / False
* Date Picker
* Date Time Picker
* Time Picker
* Color Picker
* Link
* Post Object
* Page Link
* Relationship
* Taxonomy
* User

= Relational Fields =

This plugin supports the following relational field types: Post Object, Page Link, Relationship, Taxonomy and User.

It can store both single and multiple values based on the `multiple` option.

* If it's a single value field, then the column type will be `bigint(20) unsigned`
* If it's a multiple value field, then the column type will be longtext and the date will be stored in json format.

You can query relational fields with multiple values using using MySQL's function [JSON_CONTAINS](https://dev.mysql.com/doc/refman/5.7/en/json-search-functions.html#function_json-contains).
Here is an example:

Table:

```
+---------+-------------------+--------+
| post_id |       title       | stores |
+---------+-------------------+--------+
|       1 | Lord of the Flies | [1,2]  |
|       2 | The Island        | [2]    |
|       3 | 1984              | [3]    |
+---------+-------------------+--------+
```

Query:

```sql
SELECT * FROM wp_acf_books WHERE JSON_CONTAINS(stores, 2, '$')
```

The query above will return "Lord of the Flies" and "The Island".

= ACF Compatibility =

This plugin was testes with *ACF 5 FREE Version* .

== Frequently Asked Questions ==

= This plugin supports custom post types? =

Yes. It supports custom post types and built in types of post and page

= What happens if I use unsupported field? =

The value will be stored in the core meta tables instead of the custom table

Yes. It supports custom post types and built in types of post and page

== Screenshots ==

1. Enabling ACF: Fields in Custom Table

== Installation ==

Setting up ACF: Fields in Custom Table is very simple. Follow these easy steps

1.	Upload the plugin to your `/wp-content/plugins/` directory;
2.	Activate the plugin in your WordPress admin;
3.	Go to the Custom Fields > Field Groups menu, edit or create a field group and enable ACF: Fields in Custom Table option;

== Changelog ==

= 0.3 =
*	Added support for the following field types: Link, Post Object, Page Link, Relationship, Taxonomy and User

= 0.2 =
*	Added support for the following field types: Range, Image, File, oEmbed, Checkbox, Radio Button, Date Time Picker, Time Picker
* Using dbDelta function to modify table

= 0.1 =
*	First version of the plugin released

== Upgrade Notice ==

= 0.3 =
Added plugin support for 6 more field: Link, Post Object, Page Link, Relationship, Taxonomy and User along with major refactory to improve code quality.

= 0.2 =
Added support for the following field types Range, Image, File, oEmbed, Checkbox, Radio Button, Date Time Picker, Time Picker. The plugin now delegates all the database modifications to the dbDelta function.

= 0.1 =
*	Just released into the wild.
