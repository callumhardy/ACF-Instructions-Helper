
# ACF Instructions Helper

This plugin will help you organise and alter Advanced Custom Field (ACF) Instructions or Descriptions. 

This can be done from the back end of Wordpress (WP) or from within the theme files using filters.

# Usage

The plugin can be uploaded and activated as a Wordpress plugin or the file `ACF_Instructions_Helper.php` can be included directly in the theme's function file.

Initially the plugin will filter through any visible ACFs in the back end and wrap their existing instruction text in a hoverable helper button.

If you require some more advanced control over what instruction text is displayed in certain fields then by all means, continue reading.

## Wordpress back end

The settings page for this plugin will be accessible through `Settings > ACF Helpers` in the back end of WP.

There you will find instructions on creating and editing ACF instructions and description.
## Parameters

**$activate_settings_page** (*bool*) Set whether to display the settings page in the back end. Default: `true`

**$allowed_fields** (*array*) Entering a Repeater or Flexible content Field Name here will only let the plugin effect fields inside them and will leave other ACFs alone. If you want to be really specific you can target individual fields as well. By default this array is empty and the plugin will effect all ACFs.

**$allowed_users** (*int*/*string*) You may restrict who can view the Settings page if it is set to display by entering IDs or usernames here. Only users whos ID or username appears in this array can see the settings page. By default the array is empty and so restricts no users at all.

## Filters

If the back end page isn't your style you may adjust or add ACF instructions using the `acf_helper_instructions` filter.

### Filter Parameters

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

## Disabling the Back end settings page

Here we are adjusting the defualt arguments of the plugin.

Setting `activate_settings_page` will stop the settings page from displaying in the backend for all users 

	add_filter( 'acf_instructions_helper_args',function( $args ){
		
		$args = array(
			'activate_settings_page' => false
		);

		return $args;

	});

## Displaying the Settings page only for select users

Since the options on the settings page are usually of little use to a client. It might be a good idea to only let certain users see the settings page at all.

Here we are setting the plugin to only display the settings page for a user with an id or '1' or a user name of 'admin'.

	add_filter( 'acf_instructions_helper_args',function( $args ){
		
		$args = array(
			'allowed_users' => array( 1, 'admin' )
		);

		return $args;

	});

## Using a filter to add an instruction

Below we are adding an instruction to the `$instructions` array.

The instruction that is being added is set to **overwrite** any previous instructions found in any ACFs with the name `foo` or `bar` and that also have a parent ACF that is named `hello` or `world`.

	add_filter( 'acf_instructions_helper', function( $instructions ){
		
		$instructions[] = array(
			'instruction' => '<p>Filtered instruction text!</p>',
			'settings' => array('overwrite'),
			'field_name' => array('foo','bar'),
			'field_parent' => array('hello','world')
		);

		return $instructions;

	});


