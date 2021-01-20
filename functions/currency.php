<?php

//Перечень действующих валют
function usp_get_currency_list() {

    return array(
        'RUB' => [ 'руб', '<i class="uspi fa-ruble-sign" aria-hidden="true"></i>', '₽' ],
        'UAH' => [ 'гривен', 'грн', 'грн' ],
        'KZT' => [ 'тенге', 'тнг', 'тнг' ],
        'USD' => [ 'dollars', '<i class="uspi fa-usd" aria-hidden="true"></i>', '$' ],
        'EUR' => [ 'euro', '<i class="uspi fa-eur" aria-hidden="true"></i>', '€' ],
    );
}

function usp_get_currency( $cur = false, $type = 0 ) {

    $curs = usp_get_currency_list();

    $curs = apply_filters( 'currency_list', $curs );

    if ( ! $cur ) {
        foreach ( $curs as $cur => $nms ) {
            $crs[$cur] = $cur;
        }
        return $crs;
    }

    if ( ! isset( $curs[$cur][$type] ) )
        return false;

    return $curs[$cur][$type];
}

function usp_type_currency_list( $post_id ) {

    if ( usp_get_commerce_option( 'multi_cur' ) ) {
        $type  = get_post_meta( $post_id, 'type_currency', 1 );
        $curs  = array( usp_get_commerce_option( 'primary_cur' ), usp_get_commerce_option( 'secondary_cur' ) );
        $conts = '<select name="usp[type_currency]">';
        foreach ( $curs as $cur ) {
            $conts .= '<option ' . selected( $type, $cur, false ) . ' value="' . $cur . '">' . $cur . '</option>';
        }
        $conts .= '</select>';
    } else {
        $conts = usp_get_commerce_option( 'primary_cur' );
    }
    echo $conts;
}

function usp_get_current_type_currency( $post_id ) {

    if ( usp_get_commerce_option( 'multi_cur' ) ) {
        $type    = get_post_meta( $post_id, 'type_currency', 1 );
        $curs    = array( usp_get_commerce_option( 'primary_cur' ), usp_get_commerce_option( 'secondary_cur' ) );
        if ( $type == $curs[0] || $type == $curs[1] )
            $current = $type;
        else
            $current = $curs[0];
    } else {
        $current = usp_get_commerce_option( 'primary_cur' );
    }
    return $current;
}

function get_current_currency( $post_id ) {
    $current = usp_get_current_type_currency( $post_id );
    return usp_get_currency( $current, 1 );
}

//Вывод основной валюты сайта
function usp_get_primary_currency( $type = 0 ) {
    return usp_get_currency( usp_get_commerce_option( 'primary_cur', 'RUB' ), $type );
}

function usp_primary_currency( $type = 0 ) {
    echo usp_get_primary_currency( $type );
}
