jQuery( function($) {
    $('form#profile-form').on( 'submit', function (e) {

        e.preventDefault();

        // console.log('Form Submitted');
        $.post(simpleAuthAjax.ajax_url, $(this).serialize(), function(response) {
            // console.log(response);
            if (response.success) {
                $('#profile-update-message').val(response.data.message);
            }
        } );
        
    });


    // login

    $('form#simple-auth-login-form').on('submit', function( e ) {
        e.preventDefault();

        wp.ajax.post('simple-auth-login-form', 
            $(this).serialize()
        ).done(function(response) {
            
            $('#login-message').html(response.message);

        }).fail(function(err){
            console.log('Failed', err );
            $('#login-message').html(err.message);
        })
    });

});

