(function( $, window, document, undefined ) {

	/**
	 * Fall back for older browsers that don't support Object.create
	 */
	if( typeof Object.create !== 'function' ) {

		Object.create = function( object ) {

			function Obj(){}
			Obj.prototype = object;
			return new Obj();
		};
	}

	$document = $(document);
	$window = $(window);

	$document.ready(function($) {

		/**
		 * Handles manipulating the ACF Descriptions/Instruction fields in Repeater and Flexible Content fields
		 * 
		 * @return this
		 */
		ACF_Description = {

			init: function(){

				var self = this;

				//	Replace the ACFs descriptions with Question Mark Helpers
				self.replaceDescriptions();
				
			},

			/**
			 * Replace all ACF descriptions for Question Mark Helpers
			 * 
			 * @return this
			 */
			replaceDescriptions: function(){

				var self = this;

				$subFieldInstructions = $('.sub-field-instructions');

				$subFieldInstructions.each(function(){

					$subFieldInstruction = $(this);
					$subFieldInstructionHTML = $subFieldInstruction.html();

					if( $subFieldInstructionHTML !== '' ) {

						$subFieldInstruction.addClass('helper');

						$subFieldInstruction.html('?');

						$helperBox = $('<div>');

						$helperBox.addClass('helper-box');

						$helperBox.html( $subFieldInstructionHTML );

						$subFieldInstruction.append( $helperBox );
					}
				});

				return self;
				
			}

		};

		//	Create the Content Block Object
		var acf_description = Object.create( ACF_Description );

		//	Initialise it
		acf_description.init();

	});

})( jQuery, window, document );