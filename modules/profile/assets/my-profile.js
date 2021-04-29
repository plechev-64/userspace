/* global USP */

function usp_check_profile_form() {
    var uspFormFactory = new USPForm( jQuery( '#usp-subtab-profile form' ) );

    uspFormFactory.addChekForm( 'checkPass', {
        isValid: function() {
            var valid = true;
            if ( this.form.find( '#primary_pass' ).val() ) {
                var user_pass = this.form.find( '#primary_pass' );
                var repeat_pass = this.form.find( '#repeat_pass' );

                if ( user_pass.val() != repeat_pass.val() ) {
                    this.shake( user_pass );
                    this.shake( repeat_pass );
                    this.addError( 'checkPass', USP.local.no_repeat_pass );
                    valid = false;
                } else {
                    this.noShake( user_pass );
                    this.noShake( repeat_pass );
                }
            }
            return valid;
        }
    } );

    return uspFormFactory.validate();
}
