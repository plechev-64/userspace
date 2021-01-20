( function( $ ) {
	var LkMenu = $( '#usp-tabs .usp-tabs-menu' );
	var typeButton = $( '#usp-office' );
	var UspOverlay = $( '#usp-overlay' );

// при ресайзе обновляем
	function moveMenu() {
		LkMenu.append( $( '#sunshine_ext_menu ul' ).html() );
		$( '#usp-tabs .hideshow' ).remove();
		$( '#sunshine_ext_menu' ).remove();
	}

// закрытие меню
	function closeExtMenu() {
		if ( UspOverlay.hasClass( 'sunshine_mbl_menu' ) ) {  // проверяем что это наш оверлей
			UspOverlay.fadeOut( 100 ).removeClass( 'sunshine_mbl_menu' );
		}
		$( '#sunshine_ext_menu' ).removeClass( 'bounce' ).css( {
			'top': '',
			'right': ''
		} );
	}

// определяем какой тип кнопок у нас
	if ( typeButton.hasClass( 'usp-tabs-menu__column' ) ) {
		if ( $( window ).width() <= 768 ) {         // ширина экрана
			typeButton.removeClass( 'usp-tabs-menu__column' ).addClass( 'usp-tabs-menu__row' );
			alignMenu();
		}
		$( window ).resize( function() {           // действия при ресайзе окна
			if ( $( window ).width() <= 768 ) {
				typeButton.removeClass( 'usp-tabs-menu__column' ).addClass( 'usp-tabs-menu__row' );
				closeExtMenu();
				moveMenu();
				alignMenu();
			} else {
				typeButton.removeClass( 'usp-tabs-menu__row' ).addClass( 'usp-tabs-menu__column' );
				closeExtMenu();
				moveMenu();
			}
		} );
	} else if ( typeButton.hasClass( 'usp-tabs-menu__row' ) ) {
		alignMenu();
		$( window ).resize( function() {
			closeExtMenu();
			moveMenu();
			alignMenu();
		} );
	}

// отступ сверху-справа до наших кнопок
	function menuPosition() {
		var hUpMenu = LkMenu.offset().top + 2;
		$( '#sunshine_ext_menu' ).css( {
			'top': hUpMenu
		} );

		// считаем ниже отступ когда экран у нас шире контента. Предотвращаем прижатие окна к правому краю. Теперь меню в области гамбургера
		var wRightMenu = ( $( window ).width() - ( LkMenu.offset().left + LkMenu.outerWidth() ) ) - 100;

		if ( wRightMenu > 10 ) { // если у нас есть отступ и он не отрицательный - сдвигаем менюшку
			$( '#sunshine_ext_menu' ).css( {
				'right': wRightMenu
			} );
		}
	}

// группировка кнопок
	function alignMenu() {
		var mw = LkMenu.outerWidth() - 30;                              // ширина блока - отступ на кнопку
		var menuhtml = '';
		var totalWidth = 0;                                             // сумма ширины всех кнопок

		$.each( LkMenu.children( '.usp-tab-button' ), function() {
			totalWidth += $( this ).outerWidth( true );          // считаем ширину всех кнопок с учетом отступов
			if ( mw < totalWidth ) {                                      // если ширина блока кнопок меньше чем сумма ширины кнопок:
				menuhtml += $( '<div>' ).append( $( this ).clone() ).html();
				$( this ).remove();
			}
		} );
		LkMenu.append(
			'<a class="usp-bttn usp-tab-button usp-bttn__type-primary usp-bttn__size-standart usp-tab-butt hideshow bars">'
			+ '<i class="usp-bttn__ico usp-bttn__ico-left uspi fa-bars"></i>'
			+ '</a>'
			);
		// формируем в кнопке контент
		$( 'body' ).append( '<div id="sunshine_ext_menu"><ul class="usps__line-1">' + menuhtml + '</ul></div>' );

		var hideshow = $( '#usp-tabs .usp-tab-butt.hideshow' );
		if ( menuhtml == '' ) {                                           // если нет контента в кнопке - скрываем её
			hideshow.hide();
		} else {
			hideshow.show();
		}

		$( '#usp-tabs .hideshow' ).on( 'click', function() {
			UspOverlay.fadeToggle( 100 ).toggleClass( 'sunshine_mbl_menu' ); // добавляем наш класс оверлею. Чтоб чужой не закрывать
			menuPosition();
			$( '#sunshine_ext_menu' ).toggleClass( 'bounce', 100 );
		} );

		UspOverlay.on( 'click', function() {
			closeExtMenu();
		} );
		$( '#sunshine_ext_menu' ).on( 'click', function() {
			closeExtMenu();
		} );
	}

} )( jQuery );
