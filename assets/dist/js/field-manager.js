/*!
 * plugin-lib (https://github.com/felixarntz/plugin-lib)
 * By Felix Arntz (https://leaves-and-love.net)
 * Licensed under GPL-3.0
 */
( function( exports, $, _, Backbone, wp, pluginLibData ) {
	'use strict';

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

	var fieldManager = {
		data: {},

		select2Enabled: 'undefined' !== typeof $.fn.select2,

		select2Options: {},

		init: function( data ) {
			var self = fieldManager;

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

	fieldManager.init( pluginLibData );

	exports.pluginLibFieldManager = fieldManager;

}( window, jQuery, _, Backbone, wp, pluginLibFieldManagerData ) );
