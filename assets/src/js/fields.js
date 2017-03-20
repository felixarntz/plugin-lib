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
		},
		deepClone: function( obj ) {
			var clone = _.clone( obj );

			_.each( clone, function( value, key ) {
				if ( _.isObject( value ) ) {
					clone[ key ] = _.deepClone( value );
				}
			});

			return clone;
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
		model: fieldsAPI.Field,

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

	function _getObjectReplaceableFields( obj ) {
		var fields = {};

		_.each( obj, function( value, key ) {
			if ( _.isObject( value ) && ! _.isArray( value ) ) {
				value = _getObjectReplaceableFields( value );
				if ( ! _.isEmpty( value ) ) {
					fields[ key ] = value;
				}
			} else if ( _.isString( value ) ) {
				if ( value.match( /%([A-Za-z0-9]+)%/g ) ) {
					fields[ key ] = value;
				}
			}
		});

		return fields;
	}

	function _replaceObjectFields( obj, replacements, fields ) {
		if ( _.isUndefined( fields ) ) {
			fields = _getObjectReplaceableFields( obj );
		}

		function _doReplacements( match, name ) {
			if ( ! _.isUndefined( replacements[ name ] ) ) {
				return replacements[ name ];
			}

			return match;
		}

		_.each( fields, function( value, key ) {
			if ( _.isObject( value ) ) {
				if ( ! _.isObject( obj[ key ] ) ) {
					obj[ key ] = {};
				}

				_replaceObjectFields( obj[ key ], replacements, value );
			} else {
				obj[ key ] = value.replace( /%([A-Za-z0-9]+)%/g, _doReplacements );
			}
		});
	}

	function _generateItem( itemInitial, index ) {
		var newItem = _.deepClone( itemInitial );

		_replaceObjectFields( newItem, {
			index: index,
			indexPlus1: index + 1
		});

		return newItem;
	}

	function _adjustRepeatableIndexes( itemInitial, items, startIndex ) {
		if ( ! startIndex ) {
			startIndex = 0;
		}

		var fields = _getObjectReplaceableFields( itemInitial );

		for ( var i = startIndex; i < items.length; i++ ) {
			_replaceObjectFields( items[ i ], {
				index: i,
				indexPlus1: i + 1
			}, fields );
		}

		return items;
	}

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

				if ( ! options.repeatableItemTemplate ) {
					options.repeatableItemTemplate = 'plugin-lib-field-' + model.get( 'slug' ) + '-repeatable-item';
				}

				this.events = this.getEvents( model );
			}

			if ( options.labelTemplate ) {
				this.labelTemplate = wp.template( options.labelTemplate );
			}

			if ( options.contentTemplate ) {
				this.contentTemplate = wp.template( options.contentTemplate );
			}

			if ( options.repeatableItemTemplate ) {
				this.repeatableItemTemplate = wp.template( options.repeatableItemTemplate );
			}

			if ( this.preRender ) {
				this.on( 'preRender', this.preRender, this );
			}

			if ( this.postRender ) {
				this.on( 'postRender', this.postRender, this );
			}

			Backbone.View.apply( this, arguments );
		},

		initialize: function() {
			var $contentWrap = this.$( '.content-wrap' );

			this.trigger( 'postRender', $contentWrap );
		},

		render: function() {
			var $contentWrap;

			if ( this.contentTemplate ) {
				$contentWrap = this.$( '.content-wrap' );

				this.trigger( 'preRender', $contentWrap );
				this.undelegateEvents();

				$contentWrap.replaceWith( this.contentTemplate( this.model.toJSON() ) );

				this.delegateEvents();
				this.trigger( 'postRender', $contentWrap );
			}

			return this;
		},

		changeValue: function( e ) {
			this.model.set( 'currentValue', this.getInputValue( this.$( e.target ) ) );
		},

		changeItemValue: function( e ) {
			var $itemInput = this.$( e.target );
			var $item      = $itemInput.parents( '.plugin-lib-repeatable-item' );
			var itemIndex  = $item.parent().children().index( $item );

			var items = this.model.get( 'items' );
			if ( items[ itemIndex ] ) {
				items[ itemIndex ].currentValue = this.getInputValue( $itemInput );
			}

			this.model.set( 'items', items );
		},

		addItem: function( e ) {
			var limit = this.model.get( 'repeatableLimit' );
			var items   = this.model.get( 'items' );
			if ( limit > 0 && items.length >= limit ) {
				return;
			}

			var $button   = this.$( e.target );
			var $wrap     = this.$( $button.data( 'target' ) );
			var itemIndex = $wrap.children().length;

			$button.prop( 'disabled', true );

			var newItem = _generateItem( this.model.get( 'itemInitial' ), itemIndex );

			items.push( newItem );

			var $newItem = $( this.repeatableItemTemplate( newItem ) );

			this.trigger( 'preRender', $newItem );
			this.undelegateEvents();

			$wrap.append( $newItem );

			this.delegateEvents();
			this.trigger( 'postRender', $newItem );

			this.model.set( 'items', items );

			if ( limit > 0 && items.length >= limit ) {
				$button.hide();
			} else {
				$button.prop( 'disabled', false );
			}
		},

		addItemOnEnter: function( e ) {
			if ( e.which !== 13 ) {
				return;
			}

			var $item = this.$( e.target ).parents( '.plugin-lib-repeatable-item' );

			if ( $item.find( '.plugin-lib-control' ).length > 1 ) {
				return;
			}

			e.preventDefault();
			e.stopPropagation();

			this.addItem({
				target: this.$( e.target ).parents( '.plugin-lib-repeatable-wrap' ).next( '.plugin-lib-repeatable-add-button' )[0]
			});

			if ( $item.next().length ) {
				$item.next().find( '.plugin-lib-control' ).focus();
			}
		},

		removeItem: function( e ) {
			var self = this;

			var limit = this.model.get( 'repeatableLimit' );
			var items = this.model.get( 'items' );

			var $button   = this.$( e.target );
			var $item     = this.$( $button.data( 'target' ) );
			var $wrap     = $item.parent();
			var itemIndex = $wrap.children().index( $item );

			$button.prop( 'disabled', true );

			if ( items[ itemIndex ] ) {
				items.splice( itemIndex, 1 );
				$item.remove();

				if ( itemIndex < items.length ) {
					items = _adjustRepeatableIndexes( this.model.get( 'itemInitial' ), items, itemIndex );
					$wrap.children().each( function( index ) {
						if ( index < itemIndex ) {
							return;
						}

						var $itemToAdjust = $( this );

						self.trigger( 'preRender', $itemToAdjust );
						self.undelegateEvents();

						$itemToAdjust.replaceWith( self.repeatableItemTemplate( items[ index ] ) );

						self.delegateEvents();
						self.trigger( 'postRender', $itemToAdjust );
					});
				}
			}

			this.model.set( 'items', items );

			if ( limit > 0 && items.length < limit ) {
				$( 'button[data-target="#' + $wrap.attr( 'id' ) + '"]' ).prop( 'disabled', false ).show();
			}
		},

		remove: function() {
			if ( this.preRender ) {
				this.off( 'preRender', this.preRender, this );
			}

			if ( this.postRender ) {
				this.off( 'postRender', this.postRender, this );
			}

			return Backbone.View.prototype.remove.apply( this, arguments );
		},

		getEvents: function( model ) {
			if ( model.get( 'repeatable' ) && _.isArray( model.get( 'items' ) ) ) {
				return {
					'click .plugin-lib-repeatable-add-button': 'addItem',
					'keydown .plugin-lib-repeatable-item .plugin-lib-control': 'addItemOnEnter',
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
		}
	});

	fieldsAPI.FieldManager.instances = {};

	$( document ).ready( function() {
		_.each( fieldsAPIData.field_managers, function( instance, instanceId ) {
			fieldsAPI.FieldManager.instances[ instanceId ] = new fieldsAPI.FieldManager( _.values( instance.fields ), {
				instanceId: instanceId
			});

			_.each( fieldsAPI.FieldManager.instances[ instanceId ].models, function( field ) {
				var viewClassName = field.get( 'backboneView' );
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
