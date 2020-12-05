# ACF: Fields in Custom Table

This ACF plugin makes it possible to store ACF data in structured database tables instead of WordPress core meta tables.

It uses ACF's `acf/update_field_group` hook to create/update the database and then uses `acf/save_post` hook to store the data.

It was heavily inspired by Austin Ginder's post [https://anchor.host/acf-custom-fields-stored-in-custom-table/](https://anchor.host/acf-custom-fields-stored-in-custom-table/).


## Supported Fields

- Text
- Text Area
- Number
- Range
- Email
- URL
- Password
- Image
- File
- Wysiwyg Editor
- oEmbed
- Select
- Checkbox
- Radio Button
- Button Group
- True / False
- Date Picker
- Date Time Picker
- Time Picker
- Color Picker
- Link
- Post Object
- Page Link
- Relationship
- Taxonomy
- User

## ACF Compatibility

This plugin was testes with *ACF 5 FREE Version* .

## SCreenshots

![01](.wordpress-org/screenshot-1.png)
