( function( exports, $, _, Backbone, wp, fieldsAPIData ) {
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

	var fieldsAPI = {};

	/**
	 * pluginLibFieldsAPI.Field
	 *
	 * A field.
	 *
	 * A field has no persistence with the server.
	 *
	 * @class
	 * @augments Backbone.Model
	 */
	fieldsAPI.Field = Backbone.Model.extend({
		sync: function() {
			return false;
		}
	});

	/**
	 * pluginLibFieldsAPI.FieldManager
	 *
	 * A collection of fields.
	 *
	 * This collection has no persistence with the server.
	 *
	 * @class
	 * @augments Backbone.Collection
	 *
	 * @param {array}  [models]             Models to initialize with the collection.
	 * @param {object} [options]            Options hash for the collection.
	 * @param {string} [options.instanceId] Instance ID for the collection.
	 *
	 */
	fieldsAPI.FieldManager = Backbone.Collection.extend({
		/**
		 * @type {pluginLibFieldsAPI.Field}
		 */
		model: Field,

		/**
		 * @param {Array} [models=[]] Array of models used to populate the collection.
		 * @param {Object} [options={}]
		 */
		initialize: function( models, options ) {
			options = options || {};

			if ( options.instanceId ) {
				this.instanceId = options.InstanceId;
			}
		}
	});

	/**
	 * pluginLibFieldsAPI.FieldView
	 *
	 * A field view.
	 *
	 * @class
	 * @augments Backbone.View
	 *
	 * @param {object} [options]       Options hash for the view.
	 * @param {object} [options.model] Field model.
	 *
	 */
	fieldsAPI.FieldView = Backbone.View.extend({
		/**
		 * @param {Object} [options={}]
		 */
		constructor: function( options ) {
			options = options || {};

			var model;

			if ( options.model ) {
				model = options.model;

				if ( ! options.el ) {
					options.el = '#' + model.get( 'id' ) + '-wrap';
				}

				if ( ! options.labelTemplate ) {
					options.labelTemplate = 'plugin-lib-field-' + model.get( 'slug' ) + '-label';
				}

				if ( ! options.contentTemplate ) {
					options.contentTemplate = 'plugin-lib-field-' + model.get( 'slug' ) + '-content';
				}

				this.events = this.getEvents( model );
			}

			if ( options.labelTemplate ) {
				this.labelTemplate = wp.template( options.labelTemplate );
			}

			if ( options.contentTemplate ) {
				this.contentTemplate = wp.template( options.contentTemplate );
			}

			this.on( 'preRender', this.preRender, this );
			this.on( 'postRender', this.postRender, this );

			Backbone.View.apply( this, arguments );
		},

		initialize: function() {
			var $contentWrap = this.$( '.content-wrap' );

			this.trigger( 'postRender', $contentWrap );
		}

		render: function() {
			var $contentWrap;

			if ( this.contentTemplate ) {
				$contentWrap = this.$( '.content-wrap' );

				this.trigger( 'preRender', $contentWrap );
				this.undelegateEvents();

				$contentWrap.replace( this.contentTemplate( this.model.toJSON() ) );

				this.delegateEvents();
				this.trigger( 'postRender', $contentWrap );
			}

			return this;
		},

		changeValue: function( e ) {
			this.model.set( 'current_value', this.getInputValue( this.$( e.target ) ) );
		},

		changeItemValue: function( e ) {
			var $item     = this.$( e.target );
			var $itemWrap = $item.parents( '.plugin-lib-repeatable-item' );
			var itemIndex = $itemWrap.parent().index( $itemWrap );

			var items = this.model.get( 'items' );
			if ( items[ itemIndex ] ) {
				items[ itemIndex ].current_value = this.getInputValue( $item );
			}

			this.model.set( 'items', items );
		},

		addItem: function( e ) {
			//TODO
		},

		removeItem: function( e ) {
			//TODO
		},

		remove: function() {
			this.off( 'preRender', this.preRender, this );
			this.off( 'postRender', this.postRender, this );

			return Backbone.View.prototype.remove.apply( this, arguments );
		},

		getEvents: function( model ) {
			if ( model.get( 'repeatable' ) && _.isArray( model.get( 'items' ) ) ) {
				return {
					'click .plugin-lib-repeatable-add-button': 'addItem',
					'click .plugin-lib-repeatable-remove-button': 'removeItem',
					'change :input': 'changeItemValue'
				};
			}

			return {
				'change :input': 'changeValue'
			};
		},

		getInputValue: function( $input ) {
			var currentValue = null;

			if ( ( $input.is( ':checkbox' ) && '[]' === $input.attr( 'name' ).substr( -2 ) ) ) {
				currentValue = [];

				$input.parent().each( ':checkbox:checked', _.bind( function( index, element ) {
					currentValue.push( this.$( element ).val() );
				}, this ) );
			} else if ( $input.is( ':radio' ) ) {
				currentValue = $input.parent().find( ':radio:checked' ).val();
			} else if ( $input.is( ':checkbox' ) ) {
				if ( $input.prop( 'checked' ) ) {
					currentValue = true;
				} else {
					currentValue = false;
				}
			} else if ( $input.is( 'select' ) && $input.prop( 'multiple' ) ) {
				currentValue = [];

				$input.each( 'option:selected', _.bind( function( index, element ) {
					currentValue.push( this.$( element ).val() );
				}, this ) );
			} else {
				currentValue = $input.val();
			}

			return currentValue;
		},

		preRender: function( $wrap ) {
			// Empty method body.
		},

		postRender: function( $wrap ) {
			// Empty method body.
		}
	});

	fieldsAPI.FieldManager.instances = {};

	$( document ).ready( function() {
		_.each( fieldsAPIData.field_managers, function( instance, instanceId ) {
			fieldsAPI.FieldManager.instances[ instanceId ] = new fieldsAPI.FieldManager( _.values( instance.fields ), {
				instanceId: instanceId
			});

			_.each( fieldsAPI.FieldManager.instances[ instanceId ].models, function( field ) {
				var viewClassName = field.get( 'view' );
				var FieldView     = fieldsAPI.FieldView;

				if ( viewClassName && 'FieldView' !== viewClassName && fieldsAPI.FieldView[ viewClassName ] ) {
					FieldView = fieldsAPI.FieldView[ viewClassName ];
				}

				new FieldView({
					model: field
				});
			});
		});
	});

	exports.pluginLibFieldsAPI = fieldsAPI;

}( window, jQuery, _, Backbone, wp, pluginLibFieldsAPIData ) );
