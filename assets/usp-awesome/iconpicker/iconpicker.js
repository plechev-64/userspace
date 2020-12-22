( function( a ) {
	if ( typeof define === "function" && define.amd ) {
		define( [ "jquery" ], a );
	} else {
		a( jQuery );
	}
} )( function( a ) {
	a.ui = a.ui || { };
	var b = a.ui.version = "1.12.1";
	( function() {
		var b, c = Math.max, d = Math.abs, e = /left|center|right/, f = /top|center|bottom/, g = /[\+\-]\d+(\.[\d]+)?%?/, h = /^\w+/, i = /%$/, j = a.fn.pos;
		function k( a, b, c ) {
			return [ parseFloat( a[0] ) * ( i.test( a[0] ) ? b / 100 : 1 ),
				parseFloat( a[1] ) * ( i.test( a[1] ) ? c / 100 : 1 ) ];
		}
		function l( b, c ) {
			return parseInt( a.css( b, c ), 10 ) || 0;
		}
		function m( b ) {
			var c = b[0];
			if ( c.nodeType === 9 ) {
				return {
					width: b.width(),
					height: b.height(),
					offset: {
						top: 0,
						left: 0
					}
				};
			}
			if ( a.isWindow( c ) ) {
				return {
					width: b.width(),
					height: b.height(),
					offset: {
						top: b.scrollTop(),
						left: b.scrollLeft()
					}
				};
			}
			if ( c.preventDefault ) {
				return {
					width: 0,
					height: 0,
					offset: {
						top: c.pageY,
						left: c.pageX
					}
				};
			}
			return {
				width: b.outerWidth(),
				height: b.outerHeight(),
				offset: b.offset()
			};
		}
		a.pos = {
			scrollbarWidth: function() {
				if ( b !== undefined ) {
					return b;
				}
				var c, d, e = a( "<div " + "style='display:block;position:absolute;width:50px;height:50px;overflow:hidden;'>" + "<div style='height:100px;width:auto;'></div></div>" ), f = e.children()[0];
				a( "body" ).append( e );
				c = f.offsetWidth;
				e.css( "overflow", "scroll" );
				d = f.offsetWidth;
				if ( c === d ) {
					d = e[0].clientWidth;
				}
				e.remove();
				return b = c - d;
			},
			getScrollInfo: function( b ) {
				var c = b.isWindow || b.isDocument ? "" : b.element.css( "overflow-x" ), d = b.isWindow || b.isDocument ? "" : b.element.css( "overflow-y" ), e = c === "scroll" || c === "auto" && b.width < b.element[0].scrollWidth, f = d === "scroll" || d === "auto" && b.height < b.element[0].scrollHeight;
				return {
					width: f ? a.pos.scrollbarWidth() : 0,
					height: e ? a.pos.scrollbarWidth() : 0
				};
			},
			getWithinInfo: function( b ) {
				var c = a( b || window ), d = a.isWindow( c[0] ), e = !!c[0] && c[0].nodeType === 9, f = !d && !e;
				return {
					element: c,
					isWindow: d,
					isDocument: e,
					offset: f ? a( b ).offset() : {
						left: 0,
						top: 0
					},
					scrollLeft: c.scrollLeft(),
					scrollTop: c.scrollTop(),
					width: c.outerWidth(),
					height: c.outerHeight()
				};
			}
		};
		a.fn.pos = function( b ) {
			if ( !b || !b.of ) {
				return j.apply( this, arguments );
			}
			b = a.extend( { }, b );
			var i, n, o, p, q, r, s = a( b.of ), t = a.pos.getWithinInfo( b.within ), u = a.pos.getScrollInfo( t ), v = ( b.collision || "flip" ).split( " " ), w = { };
			r = m( s );
			if ( s[0].preventDefault ) {
				b.at = "left top";
			}
			n = r.width;
			o = r.height;
			p = r.offset;
			q = a.extend( { }, p );
			a.each( [ "my", "at" ], function() {
				var a = ( b[this] || "" ).split( " " ), c, d;
				if ( a.length === 1 ) {
					a = e.test( a[0] ) ? a.concat( [ "center"
					] ) : f.test( a[0] ) ? [
						"center" ].concat( a ) : [ "center", "center" ];
				}
				a[0] = e.test( a[0] ) ? a[0] : "center";
				a[1] = f.test( a[1] ) ? a[1] : "center";
				c = g.exec( a[0] );
				d = g.exec( a[1] );
				w[this] = [ c ? c[0] : 0, d ? d[0] : 0 ];
				b[this] = [ h.exec( a[0] )[0], h.exec( a[1] )[0] ];
			} );
			if ( v.length === 1 ) {
				v[1] = v[0];
			}
			if ( b.at[0] === "right" ) {
				q.left += n;
			} else if ( b.at[0] === "center" ) {
				q.left += n / 2;
			}
			if ( b.at[1] === "bottom" ) {
				q.top += o;
			} else if ( b.at[1] === "center" ) {
				q.top += o / 2;
			}
			i = k( w.at, n, o );
			q.left += i[0];
			q.top += i[1];
			return this.each( function() {
				var e, f, g = a( this ), h = g.outerWidth(), j = g.outerHeight(), m = l( this, "marginLeft" ), r = l( this, "marginTop" ), x = h + m + l( this, "marginRight" ) + u.width, y = j + r + l( this, "marginBottom" ) + u.height, z = a.extend( { }, q ), A = k( w.my, g.outerWidth(), g.outerHeight() );
				if ( b.my[0] === "right" ) {
					z.left -= h;
				} else if ( b.my[0] === "center" ) {
					z.left -= h / 2;
				}
				if ( b.my[1] === "bottom" ) {
					z.top -= j;
				} else if ( b.my[1] === "center" ) {
					z.top -= j / 2;
				}
				z.left += A[0];
				z.top += A[1];
				e = {
					marginLeft: m,
					marginTop: r
				};
				a.each( [ "left", "top" ], function( c, d ) {
					if ( a.ui.pos[v[c]] ) {
						a.ui.pos[v[c]][d]( z, {
							targetWidth: n,
							targetHeight: o,
							elemWidth: h,
							elemHeight: j,
							collisionPosition: e,
							collisionWidth: x,
							collisionHeight: y,
							offset: [ i[0] + A[0], i[1] + A[1] ],
							my: b.my,
							at: b.at,
							within: t,
							elem: g
						} );
					}
				} );
				if ( b.using ) {
					f = function( a ) {
						var e = p.left - z.left, f = e + n - h, i = p.top - z.top, k = i + o - j, l = {
							target: {
								element: s,
								left: p.left,
								top: p.top,
								width: n,
								height: o
							},
							element: {
								element: g,
								left: z.left,
								top: z.top,
								width: h,
								height: j
							},
							horizontal: f < 0 ? "left" : e > 0 ? "right" : "center",
							vertical: k < 0 ? "top" : i > 0 ? "bottom" : "middle"
						};
						if ( n < h && d( e + f ) < n ) {
							l.horizontal = "center";
						}
						if ( o < j && d( i + k ) < o ) {
							l.vertical = "middle";
						}
						if ( c( d( e ), d( f ) ) > c( d( i ), d( k ) ) ) {
							l.important = "horizontal";
						} else {
							l.important = "vertical";
						}
						b.using.call( this, a, l );
					};
				}
				g.offset( a.extend( z, {
					using: f
				} ) );
			} );
		};
		a.ui.pos = {
			_trigger: function( a, b, c, d ) {
				if ( b.elem ) {
					b.elem.trigger( {
						type: c,
						position: a,
						positionData: b,
						triggered: d
					} );
				}
			},
			fit: {
				left: function( b, d ) {
					a.ui.pos._trigger( b, d, "posCollide", "fitLeft" );
					var e = d.within, f = e.isWindow ? e.scrollLeft : e.offset.left, g = e.width, h = b.left - d.collisionPosition.marginLeft, i = f - h, j = h + d.collisionWidth - g - f, k;
					if ( d.collisionWidth > g ) {
						if ( i > 0 && j <= 0 ) {
							k = b.left + i + d.collisionWidth - g - f;
							b.left += i - k;
						} else if ( j > 0 && i <= 0 ) {
							b.left = f;
						} else {
							if ( i > j ) {
								b.left = f + g - d.collisionWidth;
							} else {
								b.left = f;
							}
						}
					} else if ( i > 0 ) {
						b.left += i;
					} else if ( j > 0 ) {
						b.left -= j;
					} else {
						b.left = c( b.left - h, b.left );
					}
					a.ui.pos._trigger( b, d, "posCollided", "fitLeft" );
				},
				top: function( b, d ) {
					a.ui.pos._trigger( b, d, "posCollide", "fitTop" );
					var e = d.within, f = e.isWindow ? e.scrollTop : e.offset.top, g = d.within.height, h = b.top - d.collisionPosition.marginTop, i = f - h, j = h + d.collisionHeight - g - f, k;
					if ( d.collisionHeight > g ) {
						if ( i > 0 && j <= 0 ) {
							k = b.top + i + d.collisionHeight - g - f;
							b.top += i - k;
						} else if ( j > 0 && i <= 0 ) {
							b.top = f;
						} else {
							if ( i > j ) {
								b.top = f + g - d.collisionHeight;
							} else {
								b.top = f;
							}
						}
					} else if ( i > 0 ) {
						b.top += i;
					} else if ( j > 0 ) {
						b.top -= j;
					} else {
						b.top = c( b.top - h, b.top );
					}
					a.ui.pos._trigger( b, d, "posCollided", "fitTop" );
				}
			},
			flip: {
				left: function( b, c ) {
					a.ui.pos._trigger( b, c, "posCollide", "flipLeft" );
					var e = c.within, f = e.offset.left + e.scrollLeft, g = e.width, h = e.isWindow ? e.scrollLeft : e.offset.left, i = b.left - c.collisionPosition.marginLeft, j = i - h, k = i + c.collisionWidth - g - h, l = c.my[0] === "left" ? -c.elemWidth : c.my[0] === "right" ? c.elemWidth : 0, m = c.at[0] === "left" ? c.targetWidth : c.at[0] === "right" ? -c.targetWidth : 0, n = -2 * c.offset[0], o, p;
					if ( j < 0 ) {
						o = b.left + l + m + n + c.collisionWidth - g - f;
						if ( o < 0 || o < d( j ) ) {
							b.left += l + m + n;
						}
					} else if ( k > 0 ) {
						p = b.left - c.collisionPosition.marginLeft + l + m + n - h;
						if ( p > 0 || d( p ) < k ) {
							b.left += l + m + n;
						}
					}
					a.ui.pos._trigger( b, c, "posCollided", "flipLeft" );
				},
				top: function( b, c ) {
					a.ui.pos._trigger( b, c, "posCollide", "flipTop" );
					var e = c.within, f = e.offset.top + e.scrollTop, g = e.height, h = e.isWindow ? e.scrollTop : e.offset.top, i = b.top - c.collisionPosition.marginTop, j = i - h, k = i + c.collisionHeight - g - h, l = c.my[1] === "top", m = l ? -c.elemHeight : c.my[1] === "bottom" ? c.elemHeight : 0, n = c.at[1] === "top" ? c.targetHeight : c.at[1] === "bottom" ? -c.targetHeight : 0, o = -2 * c.offset[1], p, q;
					if ( j < 0 ) {
						q = b.top + m + n + o + c.collisionHeight - g - f;
						if ( q < 0 || q < d( j ) ) {
							b.top += m + n + o;
						}
					} else if ( k > 0 ) {
						p = b.top - c.collisionPosition.marginTop + m + n + o - h;
						if ( p > 0 || d( p ) < k ) {
							b.top += m + n + o;
						}
					}
					a.ui.pos._trigger( b, c, "posCollided", "flipTop" );
				}
			},
			flipfit: {
				left: function() {
					a.ui.pos.flip.left.apply( this, arguments );
					a.ui.pos.fit.left.apply( this, arguments );
				},
				top: function() {
					a.ui.pos.flip.top.apply( this, arguments );
					a.ui.pos.fit.top.apply( this, arguments );
				}
			}
		};
		( function() {
			var b, c, d, e, f, g = document.getElementsByTagName( "body" )[0], h = document.createElement( "div" );
			b = document.createElement( g ? "div" : "body" );
			d = {
				visibility: "hidden",
				width: 0,
				height: 0,
				border: 0,
				margin: 0,
				background: "none"
			};
			if ( g ) {
				a.extend( d, {
					position: "absolute",
					left: "-1000px",
					top: "-1000px"
				} );
			}
			for ( f in d ) {
				b.style[f] = d[f];
			}
			b.appendChild( h );
			c = g || document.documentElement;
			c.insertBefore( b, c.firstChild );
			h.style.cssText = "position: absolute; left: 10.7432222px;";
			e = a( h ).offset().left;
			a.support.offsetFractions = e > 10 && e < 11;
			b.innerHTML = "";
			c.removeChild( b );
		} )();
	} )();
	var c = a.ui.position;
} );

( function( a ) {
	"use strict";
	if ( typeof define === "function" && define.amd ) {
		define( [ "jquery" ], a );
	} else if ( window.jQuery && !window.jQuery.fn.iconpicker ) {
		a( window.jQuery );
	}
} )( function( a ) {
	"use strict";
	var b = {
		isEmpty: function( a ) {
			return a === false || a === "" || a === null || a === undefined;
		},
		isEmptyObject: function( a ) {
			return this.isEmpty( a ) === true || a.length === 0;
		},
		isElement: function( b ) {
			return a( b ).length > 0;
		},
		isString: function( a ) {
			return typeof a === "string" || a instanceof String;
		},
		isArray: function( b ) {
			return a.isArray( b );
		},
		inArray: function( b, c ) {
			return a.inArray( b, c ) !== -1;
		},
		throwError: function( a ) {
			throw "Font Awesome Icon Picker Exception: " + a;
		}
	};
	var c = function( d, e ) {
		this._id = c._idCounter++;
		this.element = a( d ).addClass( "iconpicker-element" );
		this._trigger( "iconpickerCreate", {
			iconpickerValue: this.iconpickerValue
		} );
		this.options = a.extend( { }, c.defaultOptions, this.element.data(), e );
		this.options.templates = a.extend( { }, c.defaultOptions.templates, this.options.templates );
		this.options.originalPlacement = this.options.placement;
		this.container = b.isElement( this.options.container ) ? a( this.options.container ) : false;
		if ( this.container === false ) {
			if ( this.element.is( ".dropdown-toggle" ) ) {
				this.container = a( "~ .dropdown-menu:first", this.element );
			} else {
				this.container = this.element.is( "input,textarea,button,.btn" ) ? this.element.parent() : this.element;
			}
		}
		this.container.addClass( "iconpicker-container" );
		if ( this.isDropdownMenu() ) {
			this.options.placement = "inline";
		}
		this.input = this.element.is( "input,textarea" ) ? this.element.addClass( "iconpicker-input" ) : false;
		if ( this.input === false ) {
			this.input = this.container.find( this.options.input );
			if ( !this.input.is( "input,textarea" ) ) {
				this.input = false;
			}
		}
		this.component = this.isDropdownMenu() ? this.container.parent().find( this.options.component ) : this.container.find( this.options.component );
		if ( this.component.length === 0 ) {
			this.component = false;
		} else {
			this.component.find( "i" ).addClass( "iconpicker-component" );
		}
		this._createPopover();
		this._createIconpicker();
		if ( this.getAcceptButton().length === 0 ) {
			this.options.mustAccept = false;
		}
		if ( this.isInputGroup() ) {
			this.container.parent().append( this.popover );
		} else {
			this.container.append( this.popover );
		}
		this._bindElementEvents();
		this._bindWindowEvents();
		this.update( this.options.selected );
		if ( this.isInline() ) {
			this.show();
		}
		this._trigger( "iconpickerCreated", {
			iconpickerValue: this.iconpickerValue
		} );
	};
	c._idCounter = 0;
	c.defaultOptions = {
		title: false,
		selected: false,
		defaultValue: false,
		placement: "bottom",
		collision: "none",
		animation: true,
		hideOnSelect: false,
		showFooter: false,
		searchInFooter: false,
		mustAccept: false,
		selectedCustomClass: "bg-primary",
		icons: [ ],
		fullClassFormatter: function( a ) {
			return a;
		},
		input: "input,.iconpicker-input",
		inputSearch: false,
		container: false,
		component: ".input-group-addon,.iconpicker-component",
		templates: {
			popover: '<div class="iconpicker-popover popover"><div class="arrow"></div>' + '<div class="popover-title"></div><div class="popover-content"></div></div>',
			footer: '<div class="popover-footer"></div>',
			buttons: '<button class="iconpicker-btn iconpicker-btn-cancel btn btn-default btn-sm">Cancel</button>' + ' <button class="iconpicker-btn iconpicker-btn-accept btn btn-primary btn-sm">Accept</button>',
			search: '<input type="search" class="form-control iconpicker-search" placeholder="Поиск..." />',
			iconpicker: '<div class="iconpicker"><div class="iconpicker-items"></div></div>',
			iconpickerItem: '<span role="button" class="iconpicker-item"><i></i></span>'
		}
	};
	c.batch = function( b, c ) {
		var d = Array.prototype.slice.call( arguments, 2 );
		return a( b ).each( function() {
			var b = a( this ).data( "iconpicker" );
			if ( !!b ) {
				b[c].apply( b, d );
			}
		} );
	};
	c.prototype = {
		constructor: c,
		options: { },
		_id: 0,
		_trigger: function( b, c ) {
			c = c || { };
			this.element.trigger( a.extend( {
				type: b,
				iconpickerInstance: this
			},
				c ) );
		},
		_createPopover: function() {
			this.popover = a( this.options.templates.popover );
			var c = this.popover.find( ".popover-title" );
			if ( !!this.options.title ) {
				c.append( a( '<div class="popover-title-text">' + this.options.title + "</div>" ) );
			}
			if ( this.hasSeparatedSearchInput() && !this.options.searchInFooter ) {
				c.append( this.options.templates.search );
			} else if ( !this.options.title ) {
				c.remove();
			}
			if ( this.options.showFooter && !b.isEmpty( this.options.templates.footer ) ) {
				var d = a( this.options.templates.footer );
				if ( this.hasSeparatedSearchInput() && this.options.searchInFooter ) {
					d.append( a( this.options.templates.search ) );
				}
				if ( !b.isEmpty( this.options.templates.buttons ) ) {
					d.append( a( this.options.templates.buttons ) );
				}
				this.popover.append( d );
			}
			if ( this.options.animation === true ) {
				this.popover.addClass( "fade" );
			}
			return this.popover;
		},
		_createIconpicker: function() {
			var b = this;
			this.iconpicker = a( this.options.templates.iconpicker );
			var c = function( c ) {
				var d = a( this );
				if ( d.is( "i" ) ) {
					d = d.parent();
				}
				b._trigger( "iconpickerSelect", {
					iconpickerItem: d,
					iconpickerValue: b.iconpickerValue
				} );
				if ( b.options.mustAccept === false ) {
					b.update( d.data( "iconpickerValue" ) );
					b._trigger( "iconpickerSelected", {
						iconpickerItem: this,
						iconpickerValue: b.iconpickerValue
					} );
				} else {
					b.update( d.data( "iconpickerValue" ), true );
				}
				if ( b.options.hideOnSelect && b.options.mustAccept === false ) {
					b.hide();
				}
			};
			for ( var d in this.options.icons ) {
				if ( typeof this.options.icons[d].title === "string" ) {
					var e = a( this.options.templates.iconpickerItem );
					e.find( "i" ).addClass( 'uspi ' + this.options.fullClassFormatter( this.options.icons[d].title ) );
					e.data( "iconpickerValue", this.options.icons[d].title ).on( "click.iconpicker", c );
					this.iconpicker.find( ".iconpicker-items" ).append( e.attr( "title", "." + this.options.icons[d].title ) );
					if ( this.options.icons[d].searchTerms.length > 0 ) {
						var f = "";
						for ( var g = 0; g < this.options.icons[d].searchTerms.length; g++ ) {
							f = f + this.options.icons[d].searchTerms[g] + " ";
						}
						this.iconpicker.find( ".iconpicker-items" ).append( e.attr( "data-search-terms", f ) );
					}
				}
			}
			this.popover.find( ".popover-content" ).append( this.iconpicker );
			return this.iconpicker;
		},
		_isEventInsideIconpicker: function( b ) {
			var c = a( b.target );
			if ( ( !c.hasClass( "iconpicker-element" ) || c.hasClass( "iconpicker-element" ) && !c.is( this.element ) ) && c.parents( ".iconpicker-popover" ).length === 0 ) {
				return false;
			}
			return true;
		},
		_bindElementEvents: function() {
			var c = this;
			this.getSearchInput().on( "keyup.iconpicker", function() {
				c.filter( a( this ).val().toLowerCase() );
			} );
			this.getAcceptButton().on( "click.iconpicker", function() {
				var a = c.iconpicker.find( ".iconpicker-selected" ).get( 0 );
				c.update( c.iconpickerValue );
				c._trigger( "iconpickerSelected", {
					iconpickerItem: a,
					iconpickerValue: c.iconpickerValue
				} );
				if ( !c.isInline() ) {
					c.hide();
				}
			} );
			this.getCancelButton().on( "click.iconpicker", function() {
				if ( !c.isInline() ) {
					c.hide();
				}
			} );
			this.element.on( "focus.iconpicker", function( a ) {
				c.show();
				a.stopPropagation();
			} );
			if ( this.hasComponent() ) {
				this.component.on( "click.iconpicker", function() {
					c.toggle();
				} );
			}
			if ( this.hasInput() ) {
				this.input.on( "keyup.iconpicker", function( d ) {
					if ( !b.inArray( d.keyCode, [ 38, 40, 37, 39, 16, 17, 18,
						9, 8, 91, 93,
						20, 46, 186, 190, 46, 78, 188, 44, 86 ] ) ) {
						c.update();
					} else {
						c._updateFormGroupStatus( c.getValid( this.value ) !== false );
					}
					if ( c.options.inputSearch === true ) {
						c.filter( a( this ).val().toLowerCase() );
					}
				} );
			}
		},
		_bindWindowEvents: function() {
			var b = a( window.document );
			var c = this;
			var d = ".iconpicker.inst" + this._id;
			a( window ).on( "resize.iconpicker" + d + " orientationchange.iconpicker" + d, function( a ) {
				if ( c.popover.hasClass( "in" ) ) {
					c.updatePlacement();
				}
			} );
			if ( !c.isInline() ) {
				b.on( "mouseup" + d, function( a ) {
					if ( !c._isEventInsideIconpicker( a ) && !c.isInline() ) {
						c.hide();
					}
				} );
			}
		},
		_unbindElementEvents: function() {
			this.popover.off( ".iconpicker" );
			this.element.off( ".iconpicker" );
			if ( this.hasInput() ) {
				this.input.off( ".iconpicker" );
			}
			if ( this.hasComponent() ) {
				this.component.off( ".iconpicker" );
			}
			if ( this.hasContainer() ) {
				this.container.off( ".iconpicker" );
			}
		},
		_unbindWindowEvents: function() {
			a( window ).off( ".iconpicker.inst" + this._id );
			a( window.document ).off( ".iconpicker.inst" + this._id );
		},
		updatePlacement: function( b, c ) {
			b = b || this.options.placement;
			this.options.placement = b;
			c = c || this.options.collision;
			c = c === true ? "flip" : c;
			var d = {
				at: "right bottom",
				my: "right top",
				of: this.hasInput() && !this.isInputGroup() ? this.input : this.container,
				collision: c === true ? "flip" : c,
				within: window
			};
			this.popover.removeClass( "inline topLeftCorner topLeft top topRight topRightCorner " + "rightTop right rightBottom bottomRight bottomRightCorner " + "bottom bottomLeft bottomLeftCorner leftBottom left leftTop" );
			if ( typeof b === "object" ) {
				return this.popover.pos( a.extend( { }, d, b ) );
			}
			switch ( b ) {
				case "inline":
					{
						d = false;
					}
					break;

				case "topLeftCorner":
					{
						d.my = "right bottom";
						d.at = "left top";
					}
					break;

				case "topLeft":
					{
						d.my = "left bottom";
						d.at = "left top";
					}
					break;

				case "top":
					{
						d.my = "center bottom";
						d.at = "center top";
					}
					break;

				case "topRight":
					{
						d.my = "right bottom";
						d.at = "right top";
					}
					break;

				case "topRightCorner":
					{
						d.my = "left bottom";
						d.at = "right top";
					}
					break;

				case "rightTop":
					{
						d.my = "left bottom";
						d.at = "right center";
					}
					break;

				case "right":
					{
						d.my = "left center";
						d.at = "right center";
					}
					break;

				case "rightBottom":
					{
						d.my = "left top";
						d.at = "right center";
					}
					break;

				case "bottomRightCorner":
					{
						d.my = "left top";
						d.at = "right bottom";
					}
					break;

				case "bottomRight":
					{
						d.my = "right top";
						d.at = "right bottom";
					}
					break;

				case "bottom":
					{
						d.my = "center top";
						d.at = "center bottom";
					}
					break;

				case "bottomLeft":
					{
						d.my = "left top";
						d.at = "left bottom";
					}
					break;

				case "bottomLeftCorner":
					{
						d.my = "right top";
						d.at = "left bottom";
					}
					break;

				case "leftBottom":
					{
						d.my = "right top";
						d.at = "left center";
					}
					break;

				case "left":
					{
						d.my = "right center";
						d.at = "left center";
					}
					break;

				case "leftTop":
					{
						d.my = "right bottom";
						d.at = "left center";
					}
					break;

				default:
					{
						return false;
					}
					break;
			}
			this.popover.css( {
				display: this.options.placement === "inline" ? "" : "block"
			} );
			if ( d !== false ) {
				this.popover.pos( d ).css( "maxWidth", a( window ).width() - this.container.offset().left - 5 );
			} else {
				this.popover.css( {
					top: "auto",
					right: "auto",
					bottom: "auto",
					left: "auto",
					maxWidth: "none"
				} );
			}
			this.popover.addClass( this.options.placement );
			return true;
		},
		_updateComponents: function() {
			this.iconpicker.find( ".iconpicker-item.iconpicker-selected" ).removeClass( "iconpicker-selected " + this.options.selectedCustomClass );
			if ( this.iconpickerValue ) {
				this.iconpicker.find( "." + this.options.fullClassFormatter( this.iconpickerValue ).replace( / /g, "." ) ).parent().addClass( "iconpicker-selected " + this.options.selectedCustomClass );
			}
			if ( this.hasComponent() ) {
				var a = this.component.find( "i" );
				if ( a.length > 0 ) {
					a.attr( "class", this.options.fullClassFormatter( this.iconpickerValue ) );
				} else {
					this.component.html( this.getHtml() );
				}
			}
		},
		_updateFormGroupStatus: function( a ) {
			if ( this.hasInput() ) {
				if ( a !== false ) {
					this.input.parents( ".form-group:first" ).removeClass( "has-error" );
				} else {
					this.input.parents( ".form-group:first" ).addClass( "has-error" );
				}
				return true;
			}
			return false;
		},
		getValid: function( c ) {
			if ( !b.isString( c ) ) {
				c = "";
			}
			var d = c === "";
			c = a.trim( c );
			var e = false;
			for ( var f = 0; f < this.options.icons.length; f++ ) {
				if ( this.options.icons[f].title === c ) {
					e = true;
					break;
				}
			}
			if ( e || d ) {
				return c;
			}
			return false;
		},
		setValue: function( a ) {
			var b = this.getValid( a );
			if ( b !== false ) {
				this.iconpickerValue = b;
				this._trigger( "iconpickerSetValue", {
					iconpickerValue: b
				} );
				return this.iconpickerValue;
			} else {
				this._trigger( "iconpickerInvalid", {
					iconpickerValue: a
				} );
				return false;
			}
		},
		getHtml: function() {
			return '<i class="' + this.options.fullClassFormatter( this.iconpickerValue ) + '"></i>';
		},
		setSourceValue: function( a ) {
			a = this.setValue( a );
			if ( a !== false && a !== "" ) {
				if ( this.hasInput() ) {
					this.input.val( this.iconpickerValue );
				} else {
					this.element.data( "iconpickerValue", this.iconpickerValue );
				}
				this._trigger( "iconpickerSetSourceValue", {
					iconpickerValue: a
				} );
			}
			return a;
		},
		getSourceValue: function( a ) {
			a = a || this.options.defaultValue;
			var b = a;
			if ( this.hasInput() ) {
				b = this.input.val();
			} else {
				b = this.element.data( "iconpickerValue" );
			}
			if ( b === undefined || b === "" || b === null || b === false ) {
				b = a;
			}
			return b;
		},
		hasInput: function() {
			return this.input !== false;
		},
		isInputSearch: function() {
			return this.hasInput() && this.options.inputSearch === true;
		},
		isInputGroup: function() {
			return this.container.is( ".input-group" );
		},
		isDropdownMenu: function() {
			return this.container.is( ".dropdown-menu" );
		},
		hasSeparatedSearchInput: function() {
			return this.options.templates.search !== false && !this.isInputSearch();
		},
		hasComponent: function() {
			return this.component !== false;
		},
		hasContainer: function() {
			return this.container !== false;
		},
		getAcceptButton: function() {
			return this.popover.find( ".iconpicker-btn-accept" );
		},
		getCancelButton: function() {
			return this.popover.find( ".iconpicker-btn-cancel" );
		},
		getSearchInput: function() {
			return this.popover.find( ".iconpicker-search" );
		},
		filter: function( c ) {
			if ( b.isEmpty( c ) ) {
				this.iconpicker.find( ".iconpicker-item" ).show();
				return a( false );
			} else {
				var d = [ ];
				this.iconpicker.find( ".iconpicker-item" ).each( function() {
					var b = a( this );
					var e = b.attr( "title" ).toLowerCase();
					var f = b.attr( "data-search-terms" ) ? b.attr( "data-search-terms" ).toLowerCase() : "";
					e = e + " " + f;
					var g = false;
					try {
						g = new RegExp( "(^|\\W)" + c, "g" );
					} catch ( a ) {
						g = false;
					}
					if ( g !== false && e.match( g ) ) {
						d.push( b );
						b.show();
					} else {
						b.hide();
					}
				} );
				return d;
			}
		},
		show: function() {
			if ( this.popover.hasClass( "in" ) ) {
				return false;
			}
			a.iconpicker.batch( a( ".iconpicker-popover.in:not(.inline)" ).not( this.popover ), "hide" );
			this._trigger( "iconpickerShow", {
				iconpickerValue: this.iconpickerValue
			} );
			this.updatePlacement();
			this.popover.addClass( "in" );
			setTimeout( a.proxy( function() {
				this.popover.css( "display", this.isInline() ? "" : "block" );
				this._trigger( "iconpickerShown", {
					iconpickerValue: this.iconpickerValue
				} );
			}, this ), this.options.animation ? 300 : 1 );
		},
		hide: function() {
			if ( !this.popover.hasClass( "in" ) ) {
				return false;
			}
			this._trigger( "iconpickerHide", {
				iconpickerValue: this.iconpickerValue
			} );
			this.popover.removeClass( "in" );
			setTimeout( a.proxy( function() {
				this.popover.css( "display", "none" );
				this.getSearchInput().val( "" );
				this.filter( "" );
				this._trigger( "iconpickerHidden", {
					iconpickerValue: this.iconpickerValue
				} );
			}, this ), this.options.animation ? 300 : 1 );
		},
		toggle: function() {
			if ( this.popover.is( ":visible" ) ) {
				this.hide();
			} else {
				this.show( true );
			}
		},
		update: function( a, b ) {
			a = a ? a : this.getSourceValue( this.iconpickerValue );
			this._trigger( "iconpickerUpdate", {
				iconpickerValue: this.iconpickerValue
			} );
			if ( b === true ) {
				a = this.setValue( a );
			} else {
				a = this.setSourceValue( a );
				this._updateFormGroupStatus( a !== false );
			}
			if ( a !== false ) {
				this._updateComponents();
			}
			this._trigger( "iconpickerUpdated", {
				iconpickerValue: this.iconpickerValue
			} );
			return a;
		},
		destroy: function() {
			this._trigger( "iconpickerDestroy", {
				iconpickerValue: this.iconpickerValue
			} );
			this.element.removeData( "iconpicker" ).removeData( "iconpickerValue" ).removeClass( "iconpicker-element" );
			this._unbindElementEvents();
			this._unbindWindowEvents();
			a( this.popover ).remove();
			this._trigger( "iconpickerDestroyed", {
				iconpickerValue: this.iconpickerValue
			} );
		},
		disable: function() {
			if ( this.hasInput() ) {
				this.input.prop( "disabled", true );
				return true;
			}
			return false;
		},
		enable: function() {
			if ( this.hasInput() ) {
				this.input.prop( "disabled", false );
				return true;
			}
			return false;
		},
		isDisabled: function() {
			if ( this.hasInput() ) {
				return this.input.prop( "disabled" ) === true;
			}
			return false;
		},
		isInline: function() {
			return this.options.placement === "inline" || this.popover.hasClass( "inline" );
		}
	};
	a.iconpicker = c;
	a.fn.iconpicker = function( b ) {
		return this.each( function() {
			var d = a( this );
			if ( !d.data( "iconpicker" ) ) {
				d.data( "iconpicker", new c( this, typeof b === "object" ? b : { } ) );
			}
		} );
	};
        c.defaultOptions = a.extend( c.defaultOptions, {
            icons: [ {
                    title: "fa-user-cog",
                    searchTerms: [ "settings", "gear" ]
                }, {
                    title: "fa-users-cog",
                    searchTerms: [ "settings", "gear" ]
                }, {
                    title: "fa-cog",
                    searchTerms: [ "settings", "gear" ]
                }, {
                    title: "fa-cogs",
                    searchTerms: [ "settings", "gear" ]
                }, {
                    title: "fa-user-secret",
                    searchTerms: [ "whisper", "spy", "incognito", "privacy" ]
                }, {
                    title: "fa-user",
                    searchTerms: [ "person", "man", "head", "profile", "account" ]
                }, {
                    title: "fa-user-friends",
                    searchTerms: [ "person", "man", "head", "profile", "account" ]
                }, {
                    title: "fa-users",
                    searchTerms: [ "people", "profiles", "persons" ]
                }, {
                    title: "fa-address-book",
                    searchTerms: [ "bookmark" ]
                }, {
                    title: "fa-exclamation-triangle",
                    searchTerms: [ "warning", "error", "problem", "notification",
                        "notify",
                        "alert", "danger" ]
                }, {
                    title: "fa-exclamation-circle",
                    searchTerms: [ "warning", "error", "problem", "notification",
                        "notify",
                        "alert", "danger" ]
                }, {
                    title: "fa-question-circle",
                    searchTerms: [ "warning", "error", "problem", "notification",
                        "notify",
                        "alert", "danger" ]
                }, {
                    title: "fa-info-circle",
                    searchTerms: [ "warning", "error", "problem", "notification",
                        "notify",
                        "alert", "danger" ]
                }, {
                    title: "fa-info",
                    searchTerms: [ "help", "information", "more", "details" ]
                }, {
                    title: "fa-bell",
                    searchTerms: [ "alert", "reminder", "notification" ]
                }, {
                    title: "fa-bell-slash",
                    searchTerms: [ "alert", "reminder", "notification" ]
                }, {
                    title: "fa-comment",
                    searchTerms: [ "speech", "notification", "note", "chat",
                        "bubble",
                        "feedback", "message", "texting", "sms", "conversation" ]
                }, {
                    title: "fa-comment-dots",
                    searchTerms: [ "speech", "notification", "note", "chat",
                        "bubble",
                        "feedback", "message", "texting", "sms", "conversation" ]
                }, {
                    title: "fa-comment-row",
                    searchTerms: [ "speech", "notification", "note", "chat",
                        "bubble",
                        "feedback", "message", "texting", "sms", "conversation" ]
                }, {
                    title: "fa-comments",
                    searchTerms: [ "speech", "notification", "note", "chat",
                        "bubble",
                        "feedback", "message", "texting", "sms", "conversation" ]
                }, {
                    title: "fa-envelope",
                    searchTerms: [ "email", "e-mail", "letter", "support", "mail",
                        "message",
                        "notification" ]
                }, {
                    title: "fa-envelope-open",
                    searchTerms: [ "email", "e-mail", "letter", "support", "mail",
                        "message",
                        "notification" ]
                }, {
                    title: "fa-paper-plane",
                    searchTerms: [ "social", "send" ]
                }, {
                    title: "fa-sync",
                    searchTerms: [ "spinner", "load", "loading", "progress" ]
                }, {
                    title: "fa-spinner",
                    searchTerms: [ "load", "loading", "progress" ]
                }, {
                    title: "fa-circle-notched",
                    searchTerms: [ "spinner", "load", "loading", "progress" ]
                }, {
                    title: "fa-list-ol",
                    searchTerms: [ "ul", "ol", "checklist", "list", "todo", "list",
                        "numbers" ]
                }, {
                    title: "fa-list-ul",
                    searchTerms: [ "ul", "ol", "checklist", "todo", "list" ]
                }, {
                    title: "fa-list",
                    searchTerms: [ "ul", "ol", "checklist", "finished",
                        "completed", "done",
                        "todo" ]
                }, {
                    title: "fa-th-list",
                    searchTerms: [ "ul", "ol", "checklist", "finished",
                        "completed", "done",
                        "todo" ]
                }, {
                    title: "fa-link",
                    searchTerms: [ "chain" ]
                }, {
                    title: "fa-unlink",
                    searchTerms: [ "remove", "chain", "chain-broken" ]
                }, {
                    title: "fa-italic",
                    searchTerms: [ "italics", "editor" ]
                }, {
                    title: "fa-bold",
                    searchTerms: [ "editor", "strong" ]
                }, {
                    title: "fa-strikethrough",
                    searchTerms: [ ]
                }, {
                    title: "fa-print",
                    searchTerms: [ ]
                }, {
                    title: "fa-save",
                    searchTerms: [ "floppy", "floppy-o" ]
                }, {
                    title: "fa-copy",
                    searchTerms: [ "duplicate", "clone", "file", "files-o" ]
                }, {
                    title: "fa-file",
                    searchTerms: [ "new", "page", "pdf", "document" ]
                }, {
                    title: "fa-image",
                    searchTerms: [ "photo", "album", "picture", "image" ]
                }, {
                    title: "fa-camera",
                    searchTerms: [ "photo", "picture", "record" ]
                }, {
                    title: "fa-eraser",
                    searchTerms: [ "remove", "delete" ]
                }, {
                    title: "fa-code",
                    searchTerms: [ "html", "brackets" ]
                }, {
                    title: "fa-terminal",
                    searchTerms: [ "command", "prompt", "code" ]
                }, {
                    title: "fa-quote-right",
                    searchTerms: [ ]
                }, {
                    title: "fa-at",
                    searchTerms: [ "email", "e-mail" ]
                }, {
                    title: "fa-pencil",
                    searchTerms: [ "write", "edit", "update", "pencil", "design" ]
                }, {
                    title: "fa-edit",
                    searchTerms: [ "write", "edit", "update", "pencil", "pen" ]
                }, {
                    title: "fa-object-ungroup",
                    searchTerms: [ "design" ]
                }, {
                    title: "fa-window-restore",
                    searchTerms: [ ]
                }, {
                    title: "fa-lock",
                    searchTerms: [ "protect", "admin", "security" ]
                }, {
                    title: "fa-unlock",
                    searchTerms: [ "protect", "admin", "password", "lock" ]
                }, {
                    title: "fa-folder",
                    searchTerms: [ "directory" ]
                }, {
                    title: "fa-folder-open",
                    searchTerms: [ "directory" ]
                }, {
                    title: "fa-paperclip",
                    searchTerms: [ "bookmark" ]
                }, {
                    title: "fa-check-square",
                    searchTerms: [ "checkmark", "done", "todo", "agree", "accept",
                        "confirm",
                        "ok", "select" ]
                }, {
                    title: "fa-square",
                    searchTerms: [ "block", "box" ]
                }, {
                    title: "fa-toggle-on",
                    searchTerms: [ "switch" ]
                }, {
                    title: "fa-toggle-off",
                    searchTerms: [ "switch" ]
                }, {
                    title: "fa-circle",
                    searchTerms: [ "checkmark", "done", "todo", "agree", "accept",
                        "confirm",
                        "tick", "ok", "select" ]
                }, {
                    title: "fa-check-circle",
                    searchTerms: [ "checkmark", "done", "todo", "agree", "accept",
                        "confirm",
                        "tick", "ok", "select" ]
                }, {
                    title: "fa-plus-circle",
                    searchTerms: [ "add", "new", "create", "expand" ]
                }, {
                    title: "fa-ban",
                    searchTerms: [ "delete", "remove", "trash", "hide", "block",
                        "stop", "abort", "cancel", "ban", "prohibit" ]
                }, {
                    title: "fa-times-circle",
                    searchTerms: [ "close", "exit", "x" ]
                }, {
                    title: "fa-window-close",
                    searchTerms: [ "close", "exit", "x" ]
                }, {
                    title: "fa-times",
                    searchTerms: [ "close", "exit", "x" ]
                }, {
                    title: "fa-horizontal-sliders",
                    searchTerms: [ "settings", "sliders" ]
                }, {
                    title: "fa-bars",
                    searchTerms: [ "menu", "settings", "list", "hamburger",
                        "dropdown" ]
                }, {
                    title: "fa-horizontal-ellipsis",
                    searchTerms: [ "dots", "menu", "settings", "list", "hamburger",
                        "dropdown" ]
                }, {
                    title: "fa-vertical-ellipsis",
                    searchTerms: [ "dots", "menu", "settings", "list", "hamburger",
                        "dropdown" ]
                }, {
                    title: "fa-plus-square",
                    searchTerms: [ "add", "new", "create", "expand" ]
                }, {
                    title: "fa-minus-square",
                    searchTerms: [ "hide", "minify", "delete", "remove", "trash",
                        "hide", "collapse" ]
                }, {
                    title: "fa-plus",
                    searchTerms: [ "add", "new", "create", "expand" ]
                }, {
                    title: "fa-minus",
                    searchTerms: [ "hide", "minify", "delete", "remove", "trash",
                        "hide",
                        "collapse" ]
                }, {
                    title: "fa-trash",
                    searchTerms: [ "garbage", "delete", "remove", "hide" ]
                }, {
                    title: "fa-shopping-cart",
                    searchTerms: [ "checkout", "buy", "purchase", "payment" ]
                }, {
                    title: "fa-shopping-cart-in",
                    searchTerms: [ "checkout", "buy", "purchase", "payment", "add"
                    ]
                }, {
                    title: "fa-shopping-cart-add",
                    searchTerms: [ "checkout", "buy", "purchase", "payment", "add"
                    ]
                }, {
                    title: "fa-shopping-basket",
                    searchTerms: [ "checkout", "buy", "purchase", "payment" ]
                }, {
                    title: "fa-dollar-sign",
                    searchTerms: [ "usd", "price", "buck", "bucks", "money", "pay",
                        "cash" ]
                }, {
                    title: "fa-euro-sign",
                    searchTerms: [ "eur", "euro", "price", "money", "pay" ]
                }, {
                    title: "fa-ruble-sign",
                    searchTerms: [ "rbl", "rouble", "price", "money", "pay" ]
                }, {
                    title: "fa-birthday-cake",
                    searchTerms: [ "happy", "dob" ]
                }, {
                    title: "fa-car",
                    searchTerms: [ "vehicle", "automobile", "transport" ]
                }, {
                    title: "fa-money-check",
                    searchTerms: [ "cash", "money", "buy", "checkout", "purchase",
                        "payment",
                        "price" ]
                }, {
                    title: "fa-copyright",
                    searchTerms: [ ]
                }, {
                    title: "fa-coffee",
                    searchTerms: [ "morning", "mug", "breakuspit", "tea", "drink",
                        "cafe" ]
                }, {
                    title: "fa-angle-down",
                    searchTerms: [ "arrow" ]
                }, {
                    title: "fa-angle-up",
                    searchTerms: [ "arrow" ]
                }, {
                    title: "fa-angle-left",
                    searchTerms: [ "previous", "back", "arrow" ]
                }, {
                    title: "fa-angle-right",
                    searchTerms: [ "next", "forward", "arrow" ]
                }, {
                    title: "fa-chevron-circle-left",
                    searchTerms: [ "previous", "back", "arrow" ]
                }, {
                    title: "fa-chevron-circle-right",
                    searchTerms: [ "next", "forward", "arrow" ]
                }, {
                    title: "fa-sign-in",
                    searchTerms: [ "enter", "join", "log in", "login", "sign up",
                        "sign in",
                        "signin", "signup", "arrow", "sign-in" ]
                }, {
                    title: "fa-sign-out",
                    searchTerms: [ "log out", "logout", "leave", "exit", "arrow",
                        "sign-out" ]
                }, {
                    title: "fa-chevron-left",
                    searchTerms: [ "bracket", "previous", "back" ]
                }, {
                    title: "fa-chevron-right",
                    searchTerms: [ "bracket", "next", "forward" ]
                }, {
                    title: "fa-chevron-up",
                    searchTerms: [ ]
                }, {
                    title: "fa-chevron-down",
                    searchTerms: [ ]
                }, {
                    title: "fa-angle-double-left",
                    searchTerms: [ "laquo", "quote", "previous", "back", "arrows" ]
                }, {
                    title: "fa-angle-double-right",
                    searchTerms: [ "raquo", "quote", "next", "forward", "arrows" ]
                }, {
                    title: "fa-caret-left",
                    searchTerms: [ "previous", "back", "triangle left", "arrow" ]
                }, {
                    title: "fa-caret-right",
                    searchTerms: [ "next", "forward", "triangle right", "arrow" ]
                }, {
                    title: "fa-caret-down",
                    searchTerms: [ "more", "dropdown", "menu", "triangle down",
                        "arrow" ]
                }, {
                    title: "fa-caret-up",
                    searchTerms: [ "triangle up", "arrow" ]
                }, {
                    title: "fa-upload",
                    searchTerms: [ "import" ]
                }, {
                    title: "fa-download",
                    searchTerms: [ "import", "export" ]
                }, {
                    title: "fa-arrows",
                    searchTerms: [ "expand", "enlarge", "fullscreen", "bigger",
                        "move",
                        "reorder", "resize", "arrow", "arrows" ]
                }, {
                    title: "fa-expand-arrows",
                    searchTerms: [ "expand", "enlarge", "fullscreen", "bigger",
                        "move",
                        "reorder", "resize", "arrow", "arrows" ]
                }, {
                    title: "fa-arrows-horizontal",
                    searchTerms: [ "resize", "arrows-h" ]
                }, {
                    title: "fa-arrows-vertical",
                    searchTerms: [ "resize", "arrows-v" ]
                }, {
                    title: "fa-external-link-square",
                    searchTerms: [ "open", "new", "external-link-square" ]
                }, {
                    title: "fa-external-link",
                    searchTerms: [ "open", "new", "external-link" ]
                }, {
                    title: "fa-share-square",
                    searchTerms: [ "social", "send" ]
                }, {
                    title: "fa-reply",
                    searchTerms: [ "back" ]
                }, {
                    title: "fa-thumbs-down",
                    searchTerms: [ "dislike", "disapprove", "disagree", "hand",
                        "thumbs-o-down"
                    ]
                }, {
                    title: "fa-thumbs-up",
                    searchTerms: [ "like", "favorite", "approve", "agree", "hand",
                        "thumbs-o-up" ]
                }, {
                    title: "fa-star",
                    searchTerms: [ "award", "achievement", "night", "rating",
                        "score",
                        "favorite" ]
                }, {
                    title: "fa-star-fill",
                    searchTerms: [ "award", "achievement", "night", "rating",
                        "score",
                        "favorite" ]
                }, {
                    title: "fa-star-half-o",
                    searchTerms: [ "award", "achievement", "rating", "score",
                        "star-half-empty", "star-half-full" ]
                }, {
                    title: "fa-star-half",
                    searchTerms: [ "award", "achievement", "rating", "score",
                        "star-half-empty", "star-half-full" ]
                }, {
                    title: "fa-heart",
                    searchTerms: [ "love", "like", "favorite" ]
                }, {
                    title: "fa-heart-fill",
                    searchTerms: [ "love", "like", "favorite" ]
                }, {
                    title: "fa-heartbeat",
                    searchTerms: [ "ekg", "vital signs" ]
                }, {
                    title: "fa-rss",
                    searchTerms: [ "feed", "blog" ]
                }, {
                    title: "fa-wechat",
                    searchTerms: [ ]
                }, {
                    title: "fa-twitter",
                    searchTerms: [ "tweet", "social network" ]
                }, {
                    title: "fa-home",
                    searchTerms: [ "main", "house" ]
                }, {
                    title: "fa-bookmark",
                    searchTerms: [ "save" ]
                }, {
                    title: "fa-book",
                    searchTerms: [ "read", "documentation" ]
                }, {
                    title: "fa-eye",
                    searchTerms: [ "show", "visible", "views" ]
                }, {
                    title: "fa-eye-slash",
                    searchTerms: [ "toggle", "show", "hide", "visible",
                        "visiblity", "views" ]
                }, {
                    title: "fa-search",
                    searchTerms: [ "magnify", "zoom", "enlarge", "bigger" ]
                }, {
                    title: "fa-search-plus",
                    searchTerms: [ "magnify", "zoom", "enlarge", "bigger" ]
                }, {
                    title: "fa-balance-scale",
                    searchTerms: [ ]
                }, {
                    title: "fa-check",
                    searchTerms: [ "checkmark", "done", "todo", "agree", "accept",
                        "confirm",
                        "tick", "ok", "select" ]
                }, {
                    title: "fa-newspaper",
                    searchTerms: [ "press", "article" ]
                }, {
                    title: "fa-bug",
                    searchTerms: [ "report", "insect" ]
                }, {
                    title: "fa-sitemap",
                    searchTerms: [ "directory", "hierarchy", "organization" ]
                }, {
                    title: "fa-database",
                    searchTerms: [ "db" ]
                }, {
                    title: "fa-code-branch",
                    searchTerms: [ "git", "fork", "vcs", "svn", "github", "rebase",
                        "version",
                        "branch", "code-fork" ]
                }, {
                    title: "fa-shield",
                    searchTerms: [ "shield" ]
                }, {
                    title: "fa-rocket",
                    searchTerms: [ "app" ]
                }, {
                    title: "fa-calendar-check",
                    searchTerms: [ "ok" ]
                }, {
                    title: "fa-key",
                    searchTerms: [ "unlock", "password" ]
                }, {
                    title: "fa-magic",
                    searchTerms: [ "wizard", "automatic", "autocomplete" ]
                }, {
                    title: "fa-wired-network",
                    searchTerms: [ ]
                }, {
                    title: "fa-clock",
                    searchTerms: [ "watch", "timer", "late", "timestamp", "date" ]
                }, {
                    title: "fa-history",
                    searchTerms: [ ]
                }, {
                    title: "fa-beaming-face-with-smiling-eyes",
                    searchTerms: [ "face", "emoticon", "happy", "approve",
                        "satisfied",
                        "rating" ]
                } ]
        } );
} );

jQuery( window ).on( 'load', function() {
	usp_init_iconpicker();
} );