# DefaultMetadata
[Default Metadata] is an Omeka plugin that allows administrators to specify default values for item metadata fields, including Dublin Core and Item Type Metadata fields. Metadata fields are then auto-filled with default values when items are created or edited.

## Notes
Super users can set the default value for metadata fields by setting values in the Default Metadata page, which super users can access from the  left-hand navigation of the Admin dashboard. Super users can also set a default Item Type. When users add or edit an item, if a default value has been set and that specific metadata field is blank, the default value will be inserted onto the item edit form. The value is then saved when the item is saved. Users can modify the values for individual items as needed.

Please note: values will not apply to items added automatically to Omeka, such as through [CSV imports] or the [Omeka API]. They will also not apply to previously added items unless users edit them manually after setting a default value.

Since the 3.0 update, the plugin supports multiple default values for each metadata field using the "add input" feature of item editing. Please note that the plugin will only insert default values onto items that have a completely empty metadata field. For example, if an item has one DC:Subject but there are two default subjects set by the plugin, the plugin will not insert the second default value onto the item's subject field.

All Dublin Core fields have been included, even though the metadata standard recommends unique values for some fields, such as DC:Identifier.  The thought here is that the field could still be "partially" auto-filled in a useful way, such as if each identifier started with a specific string.

Super users can configure the plugin to also allow admin users to add or edit default values in addition to super users.

This plugin is an expanded version of the [DefaultDC] plugin by Eric Weig.

## License
This software on this site is provided "as-is," without any express or implied warranty. In no event shall the creators be held liable for any damages arising from the use of the software.

[Default Metadata]: https://github.com/Bosterm/DefaultMetadata
[CSV imports]: https://omeka.org/classic/plugins/CsvImport/
[Omeka API]: https://omeka.readthedocs.io/en/latest/Reference/api/
[DefaultDC]: https://github.com/libmanuk/DefaultDC
