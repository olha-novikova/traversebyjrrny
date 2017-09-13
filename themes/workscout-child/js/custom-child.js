( function( $ ) {


    $(document).on( 'submit', '.small-dialog-content.woo-reg-box form.register', function(e) {
        e.preventDefault();
        var form = $(this);
        var error = false;

        var base = $(this).serialize();
        var button = $(this).find( 'input[type=submit]' );

        $(button).css('backgroundColor','#ddd');
        var data = base + '&' + button.attr("name") + "=" + button.val();
        var action = 'custom_redirect';
            data = data+'&action='+action;
        var $response = $( '#ajax-response' );

        var request = $.ajax({
            url: ws.ajaxurl,
            data: data,
            type: 'POST',
            dataType: 'JSON',
            cache: false,
            success: function(response) {

                form.find( $( '.woocommerce-error' ) ).remove();

                var $response = response;
                $(button).css('backgroundColor',ws.theme_color);

                if (response.success == true){
                    window.location.href = response.redirect;
                }else{
                    var output = "";

                    for (var i=0; i < response.error.length; i++){
                         output += "<div class='woocommerce-error'>"+response.error[i]+"</div>";
                    }
                    form.prepend(output );
                }

            }
        });

    });

    $(document).on( 'submit', '.small-dialog-content form#custom-campaign-form', function(e) {
        var magnificPopup = $.magnificPopup.instance;
        e.preventDefault();
        var form = $(this);
        var error = false;

        var base = $(this).serialize();
        var button = $(this).find( 'input[type=submit]' );

        $(button).css('backgroundColor','#ddd');

        var data = base + '&' + button.attr("name") + "=" + button.val();
        var action = 'create_custom_campaign';
        data = data+'&action='+action;
        form.find( $( '.woocommerce-error' ) ).remove();

        var request = $.ajax({
            url: ws.ajaxurl,
            data: data,
            type: 'POST',
            dataType: 'JSON',
            cache: false,
            success: function(response) {

                form.find( $( '.woocommerce-error' ) ).remove();

                var $response = response;
                $(button).css('backgroundColor',ws.theme_color);

                if (response.success == true){

                    form.find("input[type=text], textarea").val("");

                    $.magnificPopup.open({
                        items: {
                            src: '<div id="singup-dialog" class="small-dialog zoom-anim-dialog apply-popup"><div class="small-dialog-headline">Success!</h2></div><div class="small-dialog-content"><p class="margin-reset">Thank you, one of our representatives will be back to you in 24 hours.</p></div></div>',
                            type: 'inline'
                        }
                    });
                    setTimeout( function(){
                        $.magnificPopup.close();
                    }, 2000);

                }else{
                    var output = "";

                    for (var i=0; i < response.error.length; i++){
                        output += "<div class='woocommerce-error'>"+response.error[i]+"</div>";
                    }
                    form.prepend(output );
                }

            }
        });

    });

    $( document ).ready( function() {

        // prelist page events
        $('.social_products_section').find('.products li').click(function (){
            var package_id = $(this).attr('package');
            $('.job_listing_packages').find('li.job-package input').attr('id', 'package-'+package_id);
            $('.job_listing_packages').find('li.job-package input').attr('value', package_id);
            $('.social_products_section').find('.products li').removeClass("product_active");
            $(this).addClass('product_active');
           var a_link = $(this).find(".plan-price a").attr('href');
           $(".next_button_section").find(".product_detail_button a").attr('href', a_link);
        });

       var panelMenu= $.jPanelMenu({
           menu: "#responsive_new",
           animated: true,
           duration: 500,
           easing: 'ease',
           direction: 'right',
           keyboardShortcuts: false,
           closeOnContentClick: false
        });

        $(".menu-trigger-new").click( function(e) {
            e.preventDefault();
            var menuButton = $(this);
            if ( menuButton.hasClass("active")){
                panelMenu.off();
                menuButton.removeClass("active");
            } else{
                panelMenu.on();
                panelMenu.open();
                menuButton.addClass("active");
            }
        });

        $("#jPanelMenu-menu").removeClass("sf-menu");
        $("#jPanelMenu-menu li ul").removeAttr("style");

        $(window).resize(function() {
            var t = $(window).width(),
                a = $(".menu-trigger");
            if (t > 990){
                panelMenu.off();
                a.removeClass("active");
            }
        });


        var
            sendMsgBtn = $('.wp_job_manager_message_to_application'),
            sendOnReview = $('.wp_job_manager_review_application'),
            messageText = $('.job-manager-application-message-text');

        messageText.keyup(function(){

            var $this = $(this),
                targetForm = $this.closest('form.job-manager-application-message-form'),
                targetButton = targetForm.find('.wp_job_manager_message_to_application');

            if($this.val().length !=0){
                targetButton.removeAttr( "disabled" );
                targetButton.prop('disabled', false);
            } else{
                targetButton.attr('disabled','disabled');
                targetButton.prop('disabled', true);
            }
        });


        sendMsgBtn.click( function (e){

            var $this = $(this),
                targetForm = $this.closest('form.job-manager-application-message-form'),
                targetMessageText = $(targetForm).find('.job-manager-application-message-text'),
                msgList = $this.closest('.msg_part').find('.msg_set');

            e.preventDefault();

            var data = {
                action: 'send_message_to_candidate',
                message: targetForm.serialize()
            };

            jQuery.ajax({
                type: 'POST',
                url:  ws.ajaxurl,
                data: data,
                dataType: 'json',
                success: function( result ) {
                    if (result.success == true){
                        msgList.append( '<div class="msg msg-'+result.message_id+'"><span class="msg_meta"><i class="fa fa-commenting-o"></i> ' +result.from+'<span class="msg_data"> '+result.date+'</span></span><div class="msg_text">'+result.text+'</div></div>');
                        msgList.show();
                    }
                    targetMessageText.text('');
                    targetMessageText.val('');
                    $this.attr('disabled',true);
                    $this.prop('disabled', true);
                },
                error:	function( ) {

                }
            });
        });

        sendOnReview.click(function(e){

            e.preventDefault();
            var $this = $(this),
                targetForm = $this.closest('form.job-manager-application-review-form'),
                targetMessageText = $(targetForm).find('.application-review-msg'),
                action = 'send_on_review';

            targetForm.find( $( '.woocommerce-error' ) ).remove();

            if(typeof targetMessageText.val() != 'undefined' && targetMessageText.val().length !=0){
                var data = targetForm.serialize();

                data = data+'&action='+action;

                jQuery.ajax({
                    type: 'POST',
                    url:  ws.ajaxurl,
                    data: data,
                    dataType: 'json',
                    success: function( result ) {
                        window.location.href = window.location.href;
                    },
                    error:	function( ) {

                    }
                });
            }else{
                targetForm.prepend("<div class='woocommerce-error'>Please, add review message</div>");
            }

        });

        $('.job_packages .user-job-package').click(function(){

            var $this = $(this);
            $('.job_packages .user-job-package').removeClass('active');
            $this.addClass('active');

        });

        update_yotTube();
        update_instagram();
        update_twitter();

        $('#youtube_link').on('change', function(e){ update_yotTube();});
        $('#instagram_link').on('change',  function(e){update_instagram(); } );
        $('#witter_link').on('change',  function(e){update_twitter(); } );

        $('input[name="newsletter"]').on('change', function(){
            var val = $(this).val();
            if ( val == 'yes'){
                $('.newsletter_conditional').removeClass('hide');
                $('.fieldset-newsletter_total').addClass('visible');
            }else{
                $('.newsletter_conditional').addClass('hide');
                $('.fieldset-newsletter_total').removeClass('visible');
                $('input[name="newsletter_subscriber"]').val('');
                $('input[name="newsletter_total"]').val('');
            }
        });

        var facebook_link   = $('input#facebook_link');
        var instagram_link  = $('input#instagram_link');
        var twitter_link    = $('input#twitter_link');
        var youtube_link    = $('input#youtube_link');
        var jrrny_link      = $('input#jrrny_link');
        var candidate_title      = $('input#candidate_title');
        var influencer_website      = $('input#influencer_website');
        var estimated_monthly_visitors      = $('input#estimated_monthly_visitors');
        var influencer_number      = $('input#influencer_number');
        var influencer_location      = $('input#candidate_location');
        var short_influencer_bio      = $('input#short_influencer_bio');
        var candidate_photo      = $('input#candidate_photo');

        $('input[name="sync_social"]').on('change', function(){
            var $this = $(this);

            if ($this.val() == '1') {
                var data = {
                   // user_id: $url,
                    action: 'aj_sync_social'
                }

                jQuery.ajax({
                    type: 'POST',
                    url:  ws.ajaxurl,
                    data: data,
                    dataType: 'json',
                    success: function( response ) {
                        if(response.success) {
                            $.each(response.data, function(key, value){
                                if (key == 'youtube_link') {youtube_link.attr('value',value); update_yotTube();}
                                if (key == 'fb_link') {facebook_link.attr('value',value);}
                                if (key == 'insta_link') {instagram_link.attr('value',value);update_instagram();}
                                if (key == 'twitter_link') {twitter_link.attr('value',value);  update_twitter();}
                                if (key == 'jrrny_link') {jrrny_link.attr('value',value);  update_twitter();}
                                if (key == 'candidate_title') {candidate_title.attr('value',value);}
                                if (key == 'influencer_website') {influencer_website.attr('value',value);}
                                if (key == 'estimated_monthly_visitors') {estimated_monthly_visitors.attr('value',value);}
                                if (key == 'influencer_number') {influencer_number.attr('value',value);}
                                if (key == 'influencer_location') {influencer_location.attr('value',value);}
                                if (key == 'short_influencer_bio') {short_influencer_bio.text(value);}
                                if (key == 'candidate_photo') {
                                  //  $('.fieldset-candidate_photo').find('.job-manager-uploaded-files')
                                    candidate_photo.attr('value',value);
                                }


                            });
                        }
                    },
                    error:	function( ) {

                    }
                });

            }else{
                youtube_link.attr('value','');
                facebook_link.attr('value','');
                instagram_link.attr('value','');
                twitter_link.attr('value','');
                $('.count_subcr').remove();
            }

        });

    } );

    var update_yotTube = function() {

        var $this = $('#youtube_link'),
            $url = $this.val(),
            count = "<span class='count_subcr'><i class='ln  ln-icon-Boy'></i></span>",
            error = "<span class='error_msg'></span>";

        $this.closest('.form-row').find('.count_subcr').remove();
        $this.closest('.form-row').find('.error_msg').remove();

        if ( $url != '' ){
            var data = {
                link: $url,
                action: 'aj_get_youtube_subscriber_count'
            }

            jQuery.ajax({
                type: 'POST',
                url:  ws.ajaxurl,
                data: data,
                dataType: 'json',
                success: function( response ) {
                    if(response.success) {
                        $.each(response.data, function(key, value){
                            $this.after(count);
                            $this.next('.count_subcr').append(value.toString());
                        });
                    }
                    else {
                        $.each(response.data, function(key, value){
                            $this.addClass('error');
                            $this.before(error);
                            $this.prev('.error_msg').text(value.toString());
                        });
                    }

                },
                error:	function( ) {

                }
            });
        }
    }

    var update_twitter = function() {

        var $this = $('#twitter_link'),
            $url = $this.val(),
            count = "<span class='count_subcr'><i class='ln  ln-icon-Boy'></i></span>",
            error = "<span class='error_msg'></span>";

        $this.closest('.form-row').find('.count_subcr').remove();
        $this.closest('.form-row').find('.error_msg').remove();

        if ( $url != '' ){
            var data = {
                twit_link: $url,
                action: 'aj_get_twitter_followers_count'
            }

            jQuery.ajax({
                type: 'POST',
                url:  ws.ajaxurl,
                data: data,
                dataType: 'json',
                success: function( response ) {
                    if(response.success) {
                        $.each(response.data, function(key, value){
                            $this.after(count);
                            $this.next('.count_subcr').append(value.toString());
                        });
                    }
                    else {
                        $.each(response.data, function(key, value){
                            $this.addClass('error');
                            $this.before(error);
                            $this.prev('.error_msg').text(value.toString());
                        });
                    }

                },
                error:	function( ) {

                }
            });
        }
    }

    var update_instagram = function() {

        var $this = $('#instagram_link'),
            $url = $this.val(),
            count = "<span class='count_subcr'><i class='ln  ln-icon-Boy'></i></span>",
            error = "<span class='error_msg'></span>";

        $this.closest('.form-row').find('.count_subcr').remove();
        $this.closest('.form-row').find('.error_msg').remove();

        if ( $url != '' ){
            var data = {
                insta_link: $url,
                action: 'aj_get_instagram_followers_count'
            }

            jQuery.ajax({
                type: 'POST',
                url:  ws.ajaxurl,
                data: data,
                dataType: 'json',
                success: function( response ) {
                    if(response.success) {
                        $.each(response.data, function(key, value){
                            $this.after(count);
                            $this.next('.count_subcr').append(value.toString());
                        });
                    }
                    else {
                        $.each(response.data, function(key, value){
                            $this.addClass('error');
                            $this.before(error);
                            $this.prev('.error_msg').text(value.toString());
                        });
                    }

                },
                error:	function( ) {

                }
            });
        }
    }


} )( jQuery );
