/*
** Spiffy Calendar Gutenberg Block
**
** External data:
** spiffycal_bonus - true if bonus addons active
** spiffycal_cats - category values
*/	
( function( blocks, editor, i18n, element, components ) {
	var el = element.createElement,
		registerBlockType = blocks.registerBlockType,
		BlockControls = wp.blockEditor.BlockControls,
		InspectorControls = wp.blockEditor.InspectorControls,
		ServerSideRender = wp.serverSideRender,
		// ServerSideRender = components.ServerSideRender,
		TextControl = components.TextControl,
		SelectControl = components.SelectControl,
		RangeControl = components.RangeControl,
		CheckboxControl = components.CheckboxControl,
		Toolbar = components.Toolbar,
		Button = components.Button;
	var __ = i18n.__;
		
	const iconEl = el('svg', { width: 512, height: 512 },
	  el('path', { d: "M0,14h6v6H0V14z M8,20h5v-6H8V20z M15,6h5V0h-5V6z M0,13h6V7H0V13z M15,13h5V7h-5V13z M8,6h5V0H8V6z M8,13h5 V7H8V13z" } )
	);

	/*
	** Define our options
	*/
	var sc_options = [
		{ value: 'spiffy-calendar', label: __('Full Calendar') },
		{ value: 'spiffy-minical', label: __('Mini Calendar') },
		{ value: 'spiffy-week', label: __('Weekly Calendar') },
		{ value: 'spiffy-todays-list', label: __('Today\'s Events') },
		{ value: 'spiffy-upcoming-list', label: __('Upcoming Events') },
	];
	if (spiffycal_bonus) {
		// var new1 = { value: 'spiffy-camptix', label: __('Ticket Purchase Form')};
		// sc_options.push(new1); // XXX only if Camptix installed
		var new2 = { value: 'spiffy-submit', label: __('Front End Submit Form')};
		sc_options.push(new2);
	}

	var list_styles = [
		{ value: 'Popup', label: __('Popup') },
		{ value: 'Expanded', label: __('Expanded') },
		{ value: 'Columns', label: __('Columns') },
	];

	/*
	 * Register the block in JavaScript.
	 */
	registerBlockType( 'spiffy-calendar/main-block', {
		title: __('Spiffy Calendar Block'),
		icon: iconEl,
		category: 'common',
		supports: { html: false },

		/*
		 * In most other blocks, you'd see an 'attributes' property being defined here.
		 * We've defined attributes in the PHP, that information is automatically sent
		 * to the block editor, so we don't need to redefine it here.
		 */

		edit: function( props ) {
				var editButton = [{
					icon: 'shortcode', //'screenoptions',
					title: props.attributes.expand ? __('Show shortcode') : __('Expand'),
					onClick: function () {
						return props.setAttributes({ expand: !props.attributes.expand });
					},
					isActive: !props.attributes.expand
				}];
				var isList = 	( props.attributes.display === 'spiffy-upcoming-list' ) ||
								( props.attributes.display === 'spiffy-todays-list' );
				var isToday = 	( props.attributes.display === 'spiffy-todays-list' );
				var isSubmit =	( props.attributes.display === 'spiffy-submit' );
				var isManage =	( props.attributes.display === 'spiffy-submit' && props.attributes.manage === true );
				var isColumns =	( props.attributes.style === 'Columns' );
					 
				return [
				/* Rendering */
				el( ServerSideRender, {
					block: 'spiffy-calendar/main-block',
					attributes: props.attributes,
				} ),

				/* Block toolbar */
				wp.element.createElement(
					BlockControls,
					{ key: 'controls' },
					wp.element.createElement(Toolbar, { controls: editButton })
				),
				
				/* Block sidebar */
				el( InspectorControls, {},
					el( SelectControl, { 
						label: __('Display'),
						value: props.attributes.display,
						onChange: ( value ) => { props.setAttributes( { display: value } ); },
						options: sc_options,
					} ),				
					el( TextControl, {
						label: __('Title'),
						value: props.attributes.title,
						onChange: ( value ) => { props.setAttributes( { title: value } ); },
					} ),
					!isSubmit && el( SelectControl, { 
						label: __('Categories'),
						multiple: true,
						value: props.attributes.cat_list,
						onChange: ( value ) => { props.setAttributes( { cat_list: value } ); },
						options: spiffycal_cats,
					} ),
					isList && el( RangeControl, {
						label: __('Limit'),
						value: props.attributes.limit,
						onChange: ( value ) => { props.setAttributes( { limit: value } ); },
						min: 0,
					} ),
					isList && el( SelectControl, { 
						label: __('Style'),
						value: props.attributes.style,
						onChange: ( value ) => { props.setAttributes( { style: value } ); },
						options: list_styles,
					} ),
					isColumns && el( RangeControl, {
						label: __('Number of columns'),
						value: props.attributes.num_columns,
						onChange: ( value ) => { props.setAttributes( { num_columns: value } ); },
						min: 1,
						max: 4,
					} ),
					isList && el( TextControl, {
						label: __('None found text (optional):'),
						value: props.attributes.none_found,
						onChange: ( value ) => { props.setAttributes( { none_found: value } ); },
					} ),
					isToday && el( CheckboxControl, {
						label: __('Display today\'s date?'),
						checked: props.attributes.show_date == 'true',
						//onChange: ( value ) => { props.setAttributes( { show_date: value } ); },
						onChange: function( val ) {
									if( val ) {
										props.setAttributes({ show_date: 'true' })
									} else {
										props.setAttributes({ show_date: 'false' })
									}
								}
					} ),
					isSubmit && el( CheckboxControl, {
						label: __('Display event management list?'),
						checked: props.attributes.manage == 'true',
						//onChange: ( value ) => { props.setAttributes( { manage: value } ); },
						onChange: function( val ) {
									if( val ) {
										props.setAttributes({ manage: 'true' })
									} else {
										props.setAttributes({ manage: 'false' })
									}
								}
					} ),
					isManage && el( TextControl, {
						label: __('Title for event management list:'),
						value: props.attributes.manage_title,
						onChange: ( value ) => { props.setAttributes( { manage_title: value } ); },
					} ),
				),
			];
		},

		// We're going to be rendering in PHP, so save() can just return null.
		save: function() {
			return null;
		},
	} );
}(
	window.wp.blocks,
	window.wp.editor,
	window.wp.i18n,
	window.wp.element,
	window.wp.components,
) );