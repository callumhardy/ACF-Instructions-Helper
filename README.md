
# ACF Instructions Helper

This plugin will help you organise and alter Advanced Custom Field (ACF) Instructions or Descriptions. 

This can be done from the back end of Wordpress (WP) or from within the theme files using filters.

# Usage

The plugin can be uploaded and activated as a Wordpress plugin or included directly in the theme by including `Description.php` in `functions.php`.

## Wordpress back end

The settings page for this plugin will be accessible through `Settings > ACF Helpers` in the back end of WP.

There you will find instructions on creating and editing ACF instructions and description.

## Filters

If the back end page isn't your style you may adjust or add ACF instructions using the `acf_helper_instructions` filter.

# Parameters

**$instruction**

> (*string*)(*required*) The instruction text that you wish to insert into an ACF.

**$settings**

> (*array*)(*optional*) Configurable settings for this instruction.

> - **overwrite** - Replaces any current instructions in the targeted field with this instructions text.

**$field_name**

> (*array*)(*required*) An array of ACF names that you wish the above instruction text to appear on. The names must be the 'slug' like version of the ACF name. EG: they must have be lower-case letters with underscores.

**$field_parent**

> (*array*)(*optional*) An array of ACF names that the targeted ACF must have as a parent field. This will always be either a Repeater field, Flexible Content field or the layout of a Flexible Content Field . The names must be the 'slug' like version of the ACF name. EG: they must have be lower-case letters with underscores.

# Examples

## Using a filter to add an instruction

Below we are adding an instruction to the `$helper_instructions` array.

The instruction that is being added is set to **overwrite** any previous instructions found in any ACFs with the name `foo` or `bar` and that also have a parent ACF that is named `hello` or `world`.

	add_filter( 'acf_helper_instructions', function( $helper_instructions ){
		
		$helper_instructions[] = array(
			'instruction' => '<p>Filtered instruction text!</p>',
			'settings' => array('overwrite'),
			'field_name' => array('foo','bar'),
			'field_parent' => array('hello','world')
		);

		return $helper_instructions;

	});
