( function( exports, $, _, wp, pluginLibData ) {

	_.mixin({
		attrs: function( attrs ) {
			var attributeString = '';

			_.each( attrs, function( value, attr ) {
				if ( _.isBoolean( value ) ) {
					if ( value ) {
						attributeString += ' ' + attr;
					}
				} else {
					attributeString += ' ' + attr + '="' + value + '"';
				}
			});

			return attributeString;
		}
	});

	function formatSelect2( selection ) {
		if ( 'undefined' === typeof selection.id ) {
			return selection.text;
		}

		return selection.text;
	}

	var FieldManager = {
		data: {},

		select2Enabled: 'undefined' !== typeof $.fn.select2,

		select2Options: {},

		init: function( data ) {
			var self = FieldManager;

			self.data = data;

			self.select2Options = {
				width: 'element',
				closeOnSelect: true,
				templateResult: formatSelect2,
				templateSelection: formatSelect2,
				minimumResultsForSearch: 8
			};
		}
	};

	FieldManager.init( pluginLibData );

	exports.pluginLibFieldManager = FieldManager;

}( window, jQuery, _, wp, pluginLibFieldManagerData ) );
