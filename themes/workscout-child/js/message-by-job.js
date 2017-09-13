( function( $ ) {

    $( document ).ready( function() {

        var
            sendMsgBtn = $('.send_msg_by_job'),
            messageText = $('.send_msg_by_job_text');

        messageText.keyup(function(){

            var $this = $(this),
                targetForm = $this.closest('form.send_msg_by_jo_form'),
                targetButton = targetForm.find('.send_msg_by_job');

            if($this.val().length !=0){
                targetButton.removeAttr( "disabled" );
                targetButton.prop('disabled', false);
            } else{
                targetButton.attr('disabled','disabled');
                targetButton.prop('disabled', true);
            }
        });


        sendMsgBtn.click( function (e) {

            var $this = $(this),
                targetForm = $this.closest('form.send_msg_by_jo_form'),
                targetMessageText = $(targetForm).find('.send_msg_by_job_text');

            e.preventDefault();

            var data = {
                action: 'send_message_to_candidate_by_job',
                message: targetForm.serialize()
            };

            jQuery.ajax({
                type: 'POST',
                url:  ws.ajaxurl,
                data: data,
                dataType: 'json',
                success: function( result ) {
                    messageText.val('');
                    targetForm.before('<h3>Your message was send to '+result.to+'</h3>');
                    targetForm.hide();
                    setTimeout(function(){  window.location.href = window.location.href;}, 2000);
                },
                error:	function( ) {

                }
            });
        });

        $('body').on( 'submit', '.job-manager-application-form-directly', function(e) {
            var form    = $(this);
            var success = true;
            e.preventDefault();

            var data = {
                action: 'application_form_handler_direct',
                form_data: form.serialize()
            };

            $('.job-manager-applications-error').remove();

            $(this).find(':input[required]').each(function(){
                if ( ! $(this).val() ) {
                    var message = job_manager_applications.i18n_required.replace( '%s', $(this).closest('fieldset').find('label').text() );
                    form.prepend( '<p class="job-manager-error job-manager-applications-error">' + message + '</p>' );
                    success = false;
                    return false;
                }
            });

            // Prevent multiple submissions
            if ( success ) {
                $( 'input.wp_job_manager_send_application_directly' ).attr( 'disabled', 'disabled' ).addClass( 'disabled' );
                jQuery.ajax({
                    type: 'POST',
                    url:  ws.ajaxurl,
                    data: data,
                    dataType: 'json',
                    success: function( result ) {

                        setTimeout(function(){  window.location.href = window.location.href;}, 2000);
                    },
                    error:	function( ) {

                    }
                });

            }

            return success;
        });

    });

} )( jQuery );
