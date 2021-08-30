<?php

/**
 * @TODO: If field allows multiple options then
 * decide if it's going to store as json or on a different table.
 * It it's single option, then the type should be varchar
 */
class ACF_FICT_Field_Taxonomy extends ACF_FICT_Field_Relationship
{
    public function type()
    {
        return 'taxonomy';
    }

    public function column_type($acf_field)
    {
        return !in_array($acf_field['field_type'], ['multi_select', 'checkbox']) ? 'bigint(20) unsigned' : 'longtext';
    }
}

new ACF_FICT_Field_Taxonomy();
