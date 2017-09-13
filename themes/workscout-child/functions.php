<?php 
add_action( 'wp_enqueue_scripts', 'workscout_enqueue_styles' );
function workscout_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css',array('workscout-base','workscout-responsive','workscout-font-awesome') );

}

function workscout_child_dequeue_script() {
    wp_dequeue_script( 'workscout-custom' );
}

add_action( 'wp_print_scripts', 'workscout_child_dequeue_script', 100 );


function workscout_child_scripts(){
    wp_enqueue_script( 'workscout-custom-parent', get_stylesheet_directory_uri() . '/js/custom.parent.js', array('jquery'), '20150705', true );
    wp_enqueue_script( 'workscout-custom-child', get_stylesheet_directory_uri() . '/js/custom-child.js', array('jquery' ,'workscout-custom-parent'), '20150705', true );
    wp_register_script('message-by-job',get_stylesheet_directory_uri() . '/js/message-by-job.js', array('jquery'), '20150705', true);

    $ajax_url = admin_url( 'admin-ajax.php' );

    wp_localize_script( 'workscout-custom-child', 'ws',
        array(
            'logo'				=> Kirki::get_option( 'workscout','pp_logo_upload', ''),
            'retinalogo'		=> Kirki::get_option( 'workscout','pp_retina_logo_upload',''),
            'transparentlogo'			=> Kirki::get_option( 'workscout','pp_transparent_logo_upload', ''),
            'transparentretinalogo'		=> Kirki::get_option( 'workscout','pp_transparent_retina_logo_upload',''),
            'ajaxurl' 			=> $ajax_url,
            'theme_color' 		=> Kirki::get_option( 'workscout', 'pp_main_color' ),
            'woo_account_page'	=> get_permalink(get_option('woocommerce_myaccount_page_id')),
            'theme_url'			=> get_template_directory_uri(),

        )
    );

}

add_action( 'wp_enqueue_scripts', 'workscout_child_scripts' );

function overwrite_shortcode() {
    include_once get_stylesheet_directory() . '/inc/spotlight_jobs_custom.php';
    include_once get_stylesheet_directory() . '/inc/jobs_custom.php';

    remove_shortcode('spotlight_jobs');
    remove_shortcode('jobs');

    add_shortcode( 'spotlight_jobs', 'spotlight_jobs_custom' );
    add_shortcode( 'jobs', 'jobs_custom' );
}

add_action( 'wp_loaded', 'overwrite_shortcode' );


//remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

function remove_parent_theme_features() {
    remove_filter( 'woocommerce_login_redirect', 'wc_custom_user_redirect', 10, 2 );
    remove_action( 'template_redirect', array( 'WC_Form_Handler', 'save_account_details' ) );
    remove_action( 'wp_loaded', array( 'WP_Job_Manager_Applications_Dashboard', 'edit_handler' ) );

}

add_action( 'after_setup_theme', 'remove_parent_theme_features', 10 );

function workscout_child_setup() {

    register_nav_menus( array(
        'without_login_menu' => esc_html__( 'Without Login Menu', 'workscout' ),

    ) );

}

add_action( 'after_setup_theme', 'workscout_child_setup' );

remove_action( 'woocommerce_edit_account_form', 'my_woocommerce_edit_account_form' );


add_action( 'wp_loaded',  'application_edit_handler'  );

function application_edit_handler() {
    if ( ! empty( $_POST['wp_job_manager_edit_application'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'edit_job_application' ) ) {
        global $wp_post_statuses;

        $application_id = absint( $_POST['application_id'] );

        $application = get_post( $application_id );

        if ( ! $application ) {
            return false;
        }

        $job = get_post( $application->post_parent );

        // Permissions
        if ( ! $job || ! $application || $application->post_type !== 'job_application' || $job->post_type !== 'job_listing' || !job_manager_user_can_edit_job( $job->ID ) ) {
            return false;
        }

        $application_status = sanitize_text_field( $_POST['application_status'] );
        $application_rating = floatval( $_POST['application_rating'] );
        $application_rating = $application_rating < 0 ? 0 : $application_rating;
        $application_rating = $application_rating > 5 ? 5 : $application_rating;

        update_post_meta( $application_id, '_rating', $application_rating );

        if ( array_key_exists( $application_status, $wp_post_statuses ) ) {
            wp_update_post( array(
                'ID'          => $application_id,
                'post_status' => $application_status
            ) );
        }

        if ( $application_status == 'completed' ){
            $candidate_application_author  =  get_post_meta( $application_id, '_candidate_user_id', true );

            $job_price          = get_post_meta(  $job->ID , '_targeted_budget', true );
            if ( !$job_price ) $job_price = get_post_meta($job->ID, 'Budget_for_the_influencer', true );

            if ( $candidate_application_author && $job_price ){
                $current_deposit = get_user_meta($candidate_application_author, '_available_money', true );

                if ( !get_user_meta( $candidate_application_author, '_available_money_for_'. $application_id, true ) ){

                    update_user_meta( $candidate_application_author, '_available_money_for_'. $application_id, $job_price );
                    update_user_meta( $candidate_application_author, '_available_money', $current_deposit + $job_price );

                }

            }
        }

    }
}

add_action( 'woocommerce_edit_account_form', 'my_woocommerce_edit_account_form_child' );

function my_woocommerce_edit_account_form_child() {

    $user_id = get_current_user_id();
    $user = get_userdata( $user_id );

    if ( !$user )
        return;

    $str = get_userdata($user_id);

    if($str->roles[0] == "candidate"){

        $number =  get_user_meta($user_id,'phone_number',true);
        $logo = get_user_meta( $user_id, 'photo', true );
        $website = get_user_meta( $user_id, 'website', true );
        $monthlyvisit = get_user_meta( $user_id, 'monthlyvisit', true );
        $insta = get_user_meta( $user_id, 'insta', true );
        $fb = get_user_meta( $user_id, 'fb', true );
        $twitter = get_user_meta( $user_id, 'twitter', true );

        $youtube = get_user_meta( $user_id, 'youtube', true );
        $shortbio = get_user_meta( $user_id, 'shortbio', true );

        $newsletter = get_user_meta( $user_id, 'newsletter', true );
        $newsletter_subscriber_count = get_user_meta( $user_id, 'newsletter_subscriber_count', true );

        $traveler_type = get_user_meta( $user_id, 'traveler_type', true );
        $location = get_user_meta( $user_id, 'location', true );

        $jrrny_link_auto = get_user_meta($user_id,'_jrrny_link', true);
        $jrrny_link_own = get_user_meta($user_id,'jrrny_link', true);
        ?>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="number">PHONE NUMBER</label>
                <input type="text" name="phone_number" value="<?php echo esc_attr( $number ); ?>" class="input-text" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="shortbio">SHORT BIO</label>
                <textarea type="textfield" name="shortbio" class="input-text" /><?php echo esc_attr( $shortbio ); ?></textarea>
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <?php if($logo) {
                    $dir = wp_get_upload_dir();?>
                    <img class="img-responsive" src="<?php echo $dir['baseurl'].'/users/'.$logo; ?>" />
                <?php } ?>
                <label for="logo">PHOTO</label>
                <input type="file" name="logo" value="<?php echo esc_attr( $logo ); ?>" class="input-text" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="website">WEBSITE</label>
                <input type="text" name="website" value="<?php echo esc_attr( $website ); ?>" class="input-text" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="monthlyvisit">ESTIMATED MONTHLY VISIT</label>
                <input type="text" name="monthlyvisit" value="<?php echo esc_attr( $monthlyvisit ); ?>" class="input-text" />
            </p>
        </fieldset>


        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="insta">INSTAGRAM
                <input type="text" name="insta" id = "instagram_link" value="<?php echo esc_attr( $insta ); ?>" class="input-text" />
                </label>
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="fb">FACEBOOK</label>
                <input type="text" name="fb" id = "fb_link" value="<?php echo esc_attr( $fb ); ?>" class="input-text" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="birthdate">TWITTER
                <input type="text" name="twitter"  id ="twitter_link" value="<?php echo esc_attr($twitter); ?>" class="input-text" />
                </label>
            </p>
        </fieldset>


        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="youtube">YOUTUBE
                    <input type="text" name="youtube" id = "youtube_link" value="<?php echo esc_attr( $youtube ); ?>" class="input-text" />
                </label>
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="youtube">JRRNY.COM <?php if (!$jrrny_link_own && $jrrny_link_auto) echo "(account was created automatically, you can use your own if you have)";?>
                    <input type="text" name="jrrny_link" id = "jrrny_link" value="<?php echo esc_attr( $jrrny_link_own ? $jrrny_link_own: $jrrny_link_auto); ?>" class="input-text" />
                </label>
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="newsletter">DO YOU HAVE A NEWSLETTER?</label>
                <input type="radio" name="newsletter" value="yes" <?php if ($newsletter == 'yes') echo 'checked = "checked"';?>> YES
                <input type="radio" name="newsletter" value="no" <?php if ($newsletter == 'no') echo 'checked = "checked"';?>> NO
            </p>
            <p class="form-row form-row-thirds newsletter_conditional <?php if ($newsletter != 'yes') echo 'hide';?>" >
                <label for="newsletter_subscriber">IF YES, HOW MANY SUBSCRIBERS?</label>
                <input type="text" name="newsletter_subscriber" value="<?php echo esc_attr( $newsletter_subscriber_count ); ?>" class="input-text" />
            </p>
        </fieldset>

        <?php
        global $wpdb;
        $sql = $wpdb->get_results("SELECT * FROM travler_type");
        ?>
        <?php wp_enqueue_script( 'wp-job-manager-multiselect' ); ?>
        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="birthdate">TARGET AUDIENCE</label>
                <select name="traveler_type[]" class="job-manager-multiselect" multiple="multiple" data-no_results_text="<?php _e( 'No results match', 'wp-job-manager' ); ?>" data-multiple_text="<?php _e( 'Select Some Options', 'wp-job-manager' ); ?>">
                    <?php
                    foreach ($sql as $result){ ?>
                        <option value="<?php echo $result->travler_type; ?>" <?php if ( in_array( $result->travler_type, $traveler_type) ) echo "selected" ; ?>><?php echo $result->travler_type; ?></option>
                    <?php }
                    ?>
                </select>
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="location">LOCATIONS YOU HAVE ACCESS TO</label>
                <input type="text" name="location" value="<?php echo esc_attr( $location ); ?>" class="input-text" />
            </p>
        </fieldset>

        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('input[name="newsletter"]').on('change', function(){
                    var val = $(this).val();
                    if ( val == 'yes'){
                        $('.newsletter_conditional').removeClass('hide');
                    }else{
                        $('.newsletter_conditional').addClass('hide');
                        $('input[name="newsletter_subscriber"]').val('');
                    }
                });

            });
        </script>

    <?php
    }
    if($str->roles[0] == "employer")
    {
        $company_name= get_user_meta( $user_id, 'company_name', true );
        $number = get_user_meta( $user_id, 'number', true );
        $logo = get_user_meta( $user_id, 'logo', true );
        $website = get_user_meta( $user_id, 'website', true );
        $insta = get_user_meta( $user_id, 'insta', true );
        $fb = get_user_meta( $user_id, 'fb', true );
        $twitter = get_user_meta( $user_id, 'twitter', true );
        $youtube = get_user_meta( $user_id, 'youtube', true );
        $newsletter = get_user_meta( $user_id, 'newsletter', true );
        $shortbio = get_user_meta( $user_id, 'shortbio', true );
        $traveler_type = get_user_meta( $user_id, 'traveler_type', true );
        ?>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="company_name">COMPANY</label>
                <input type="text" name="company_name" value="<?php echo esc_attr( $company_name ); ?>" class="input-text sdjhjksdhk" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="number">PHONE NUMBER</label>
                <input type="text" name="number" value="<?php echo esc_attr( $number ); ?>" class="input-text sdjhjksdhk" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="birthdate">SHORT BIO</label>
                <textarea name="shortbio"><?php echo esc_attr( $shortbio ); ?></textarea>
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <?php if( $logo ) {
                    $dir = wp_get_upload_dir();?>
                    <img class="img-responsive" src="<?php echo $dir['baseurl'].'/users/'.$logo; ?>" />
                <?php } ?>
                <label for="logo">LOGO</label>
                <input type="file" name="logo" value="<?php echo esc_attr( $logo ); ?>" class="input-text" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="website">WEBSITE</label>
                <input type="text" name="website" value="<?php echo esc_attr( $website ); ?>" class="input-text" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="birthdate">INSTAGRAM
                <input type="text" name="insta" id = "instagram_link" value="<?php echo esc_attr( $insta ); ?>" class="input-text" />
                </label>
            </p>
        </fieldset>


        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="birthdate">FACEBOOK</label>
                <input type="text" name="fb" value="<?php echo esc_attr( $fb ); ?>" class="input-text" />
            </p>
        </fieldset>


        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="birthdate">TWITTER
                <input type="text" name="twitter" id ="twitter_link" value="<?php echo esc_attr($twitter); ?>" class="input-text" />
                </label>
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="birthdate">PINTEREST</label>
                <input type="text" name="pinterest" value="<?php echo esc_attr($pinterest); ?>" class="input-text" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="youtube">YOUTUBE
                    <input type="text" name="youtube" id = "youtube_link" value="<?php echo esc_attr( $youtube ); ?>" class="input-text" />
                </label>
            </p>
        </fieldset>

        <?php
        global $wpdb;
        $sql = $wpdb->get_results("SELECT * FROM travler_type");
        ?>
        <?php wp_enqueue_script( 'wp-job-manager-multiselect' ); ?>
        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="birthdate">TARGET AUDIENCE</label>
                <select name="traveler_type[]" class="job-manager-multiselect" multiple="multiple" data-no_results_text="<?php _e( 'No results match', 'wp-job-manager' ); ?>" data-multiple_text="<?php _e( 'Select Some Options', 'wp-job-manager' ); ?>">
                    <?php
                    foreach ($sql as $result){ ?>
                        <option value="<?php echo $result->travler_type; ?>" <?php if ( in_array( $result->travler_type, $traveler_type) ) echo "selected" ; ?>><?php echo $result->travler_type; ?></option>
                    <?php }
                    ?>
                </select>
            </p>
        </fieldset>

    <?php

    }

    if($str->roles[0] == "administrator") {
        //Brand
        $company_name= get_user_meta( $user_id, 'company_name', true );
        $number = get_user_meta( $user_id, 'number', true );
        ?>
        <h2>My Brand Fields</h2>
        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="company_name">COMPANY</label>
                <input type="text" name="company_name" value="<?php echo esc_attr( $company_name ); ?>" class="input-text sdjhjksdhk" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="number">PHONE NUMBER</label>
                <input type="text" name="number" value="<?php echo esc_attr( $number ); ?>" class="input-text sdjhjksdhk" />
            </p>
        </fieldset>

        <h2>My Candidate Fields</h2>
        <?php
        //Candidate
        $number =  get_user_meta($user_id,'phone_number',true);
        $monthlyvisit = get_user_meta( $user_id, 'monthlyvisit', true );
        $newsletter = get_user_meta( $user_id, 'newsletter', true );
        $newsletter_subscriber_count = get_user_meta( $user_id, 'newsletter_subscriber_count', true );
        $traveler_type = get_user_meta( $user_id, 'traveler_type', true );
        $location = get_user_meta( $user_id, 'location', true );
        $jrrny_link_auto = get_user_meta($user_id,'_jrrny_link', true);
        $jrrny_link_own = get_user_meta($user_id,'jrrny_link', true);

        ?>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="number">PHONE NUMBER</label>
                <input type="text" name="phone_number" value="<?php echo esc_attr( $number ); ?>" class="input-text" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="monthlyvisit">ESTIMATED MONTHLY VISIT</label>
                <input type="text" name="monthlyvisit" value="<?php echo esc_attr( $monthlyvisit ); ?>" class="input-text" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="youtube">JRRNY.COM <?php if (!$jrrny_link_own && $jrrny_link_auto) echo "(account was created automatically, you can use your own if you have)";?>
                    <input type="text" name="jrrny_link" id = "jrrny_link" value="<?php echo esc_attr( $jrrny_link_own ? $jrrny_link_own: $jrrny_link_auto); ?>" class="input-text" />
                </label>
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="newsletter">DO YOU HAVE A NEWSLETTER?</label>
                <input type="radio" name="newsletter" value="yes" <?php if ($newsletter == 'yes') echo 'checked = "checked"';?>> YES
                <input type="radio" name="newsletter" value="no" <?php if ($newsletter == 'no') echo 'checked = "checked"';?>> NO
            </p>
            <p class="form-row form-row-thirds newsletter_conditional <?php if ($newsletter != 'yes') echo 'hide';?>" >
                <label for="newsletter_subscriber">IF YES, HOW MANY SUBSCRIBERS?</label>
                <input type="text" name="newsletter_subscriber" value="<?php echo esc_attr( $newsletter_subscriber_count ); ?>" class="input-text" />
            </p>
        </fieldset>

        <?php
        global $wpdb;
        $sql = $wpdb->get_results("SELECT * FROM travler_type");
        ?>
        <?php wp_enqueue_script( 'wp-job-manager-multiselect' ); ?>
        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="birthdate">TARGET AUDIENCE</label>
                <select name="traveler_type[]" class="job-manager-multiselect" multiple="multiple" data-no_results_text="<?php _e( 'No results match', 'wp-job-manager' ); ?>" data-multiple_text="<?php _e( 'Select Some Options', 'wp-job-manager' ); ?>">
                    <?php
                    foreach ($sql as $result){ ?>
                        <option value="<?php echo $result->travler_type; ?>" <?php if ( in_array( $result->travler_type, $traveler_type) ) echo "selected" ; ?>><?php echo $result->travler_type; ?></option>
                    <?php }
                    ?>
                </select>
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="location">LOCATIONS YOU HAVE ACCESS TO</label>
                <input type="text" name="location" value="<?php echo esc_attr( $location ); ?>" class="input-text" />
            </p>
        </fieldset>

        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('input[name="newsletter"]').on('change', function(){
                    var val = $(this).val();
                    if ( val == 'yes'){
                        $('.newsletter_conditional').removeClass('hide');
                    }else{
                        $('.newsletter_conditional').addClass('hide');
                        $('input[name="newsletter_subscriber"]').val('');
                    }
                });

            });
        </script>
        <?php
        //Both
        $website = get_user_meta( $user_id, 'website', true );
        $logo = get_user_meta( $user_id, 'logo', true );
        $insta = get_user_meta( $user_id, 'insta', true );
        $fb = get_user_meta( $user_id, 'fb', true );
        $twitter = get_user_meta( $user_id, 'twitter', true );
        $youtube = get_user_meta( $user_id, 'youtube', true );
        $shortbio = get_user_meta( $user_id, 'shortbio', true );

        ?>

        <h2>Both Type Fields</h2>
        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="birthdate">SHORT BIO</label>
                <textarea name="shortbio" /><?php echo esc_attr( $shortbio ); ?></textarea>
            </p>
        </fieldset>

        <fieldset>

            <p class="form-row form-row-thirds">
                <?php if( $logo ) {
                    $dir = wp_get_upload_dir();?>
                    <img class="img-responsive" src="<?php echo $dir['baseurl'].'/users/'.$logo; ?>" />
                <?php } ?>
                <label for="logo">PHOTO/LOGO</label>
                <input type="file" name="logo" value="<?php echo esc_attr( $logo ); ?>" class="input-text" />
            </p>

        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="website">WEBSITE</label>
                <input type="text" name="website" value="<?php echo esc_attr( $website ); ?>" class="input-text" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="insta">INSTAGRAM
                    <input type="text" name="insta" id = "instagram_link" value="<?php echo esc_attr( $insta ); ?>" class="input-text" />
                </label>
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="fb">FACEBOOK</label>
                <input type="text" name="fb" id = "fb_link" value="<?php echo esc_attr( $fb ); ?>" class="input-text" />
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="birthdate">TWITTER
                    <input type="text" name="twitter"  id ="twitter_link" value="<?php echo esc_attr($twitter); ?>" class="input-text" />
                </label>
            </p>
        </fieldset>

        <fieldset>
            <p class="form-row form-row-thirds">
                <label for="youtube">YOUTUBE
                    <input type="text" name="youtube" id = "youtube_link" value="<?php echo esc_attr( $youtube ); ?>" class="input-text" />
                </label>
            </p>
        </fieldset>


    <?php
    }
} // end func


remove_action( 'woocommerce_save_account_details', 'my_woocommerce_save_account_details' );
add_action( 'woocommerce_save_account_details', 'my_woocommerce_save_account_details_child' );

function my_woocommerce_save_account_details_child( $user_id ) {
    $str = get_userdata($user_id);

    if($str->roles[0] == "candidate")
    {
        if(isset($_FILES['logo']['name']) && !empty($_FILES['logo']['name'])){
            $errors= array();
            $file_name = $_FILES['logo']['name'];
            $file_size =$_FILES['logo']['size'];
            $file_tmp =$_FILES['logo']['tmp_name'];
            $file_type=$_FILES['logo']['type'];
            $file_ext=strtolower(end(explode('.',$_FILES['logo']['name'])));

            $expensions= array("jpeg","jpg","png","gif");

            if(in_array($file_ext,$expensions)=== false){
                $errors[]=$file_ext." extension not allowed, please choose a JPEG or PNG file.";
            }

            if($file_size > 2097152){
                $errors[]='File size must be excately 2 MB';
            }

            if(empty($errors)==true){
                $dir = wp_get_upload_dir();
                move_uploaded_file($file_tmp, $dir['basedir']."/users/".$file_name);
                update_user_meta( $user_id, 'photo', htmlentities( $_FILES['logo']['name'] ) );
                echo "Success";
            }else{
                print_r($errors);
            }
        }
        update_user_meta( $user_id, 'website', htmlentities( $_POST[ 'website' ] ) );
        update_user_meta( $user_id, 'jrrny_link', htmlentities( $_POST[ 'jrrny_link' ] ) );
        update_user_meta( $user_id, 'monthlyvisit', htmlentities( $_POST[ 'monthlyvisit' ] ) );
        update_user_meta( $user_id, 'insta', htmlentities( $_POST[ 'insta' ] ) );
        update_user_meta( $user_id, 'fb', htmlentities( $_POST[ 'fb' ] ) );
        update_user_meta( $user_id, 'twitter', htmlentities( $_POST[ 'twitter' ] ) );
        update_user_meta( $user_id, 'youtube', htmlentities( $_POST[ 'youtube' ] ) );
        update_user_meta( $user_id, 'newsletter', htmlentities( $_POST[ 'newsletter' ] ) );
        update_user_meta( $user_id, 'newsletter_subscriber_count', htmlentities( $_POST[ 'newsletter_subscriber' ] ) );
        update_user_meta( $user_id, 'shortbio', htmlentities( $_POST[ 'shortbio'] ) );
        update_user_meta( $user_id, 'traveler_type',  $_POST['traveler_type' ] );
        update_user_meta( $user_id, 'location', htmlentities( $_POST['location'] ) );
        update_user_meta( $user_id, 'phone_number', htmlentities( $_POST[ 'phone_number' ] ) );


        $query_args = array(
            'post_type'     => 'resume',
            'fields'     => 'ids',
            'posts_per_page' => -1,
            'post_status'  => 'any',
            'author'     => intval($user_id)
        );

      /*  $resume_data = new WP_Query( $query_args);

        if ($resume_data->have_posts()):
            foreach( $resume_data->posts as $id ):
                echo $id.'<br>';
                update_post_meta( $id, '_influencer_number', htmlentities( $_POST[ 'phone_number' ] ) );
                update_post_meta( $id, '_influencer_website', htmlentities( $_POST[ 'website' ] ) );
                update_post_meta( $id, '_estimated_monthly_visitors', htmlentities( $_POST[ 'monthlyvisit' ] ) );
                update_post_meta( $id, '_instagram_link', htmlentities( $_POST[ 'insta' ] ) );
                update_post_meta( $id, '_facebook_link', htmlentities( $_POST[ 'fb' ] ) );
                update_post_meta( $id, '_twitter_link', htmlentities( $_POST[ 'twitter' ] ) );
                update_post_meta( $id, '_youtube_link', htmlentities( $_POST[ 'youtube' ] ) );
                update_post_meta( $id, '_newsletter', htmlentities( $_POST[ 'newsletter' ] ) );
                update_post_meta( $id, '_short_influencer_bio', htmlentities( $_POST[ 'shortbio'] ) );
                update_post_meta( $id, '_candidate_photo', htmlentities( $_FILES['logo']['name'] ) );
            endforeach;
        endif;*/
    }

    if($str->roles[0] == "employer") {

//job_listing
        if(isset($_FILES['logo']['name']) && !empty($_FILES['logo']['name'])){
            $errors= array();
            $file_name = $_FILES['logo']['name'];
            $file_size =$_FILES['logo']['size'];
            $file_tmp =$_FILES['logo']['tmp_name'];
            $file_type=$_FILES['logo']['type'];
            $file_ext=strtolower(end(explode('.',$_FILES['logo']['name'])));

            $expensions= array("jpeg","jpg","png");

            if(in_array($file_ext,$expensions)=== false){
                $errors[]="extension not allowed, please choose a JPEG or PNG file.";
            }

            if($file_size > 2097152){
                $errors[]='File size must be excately 2 MB';
            }
            if(empty($errors)==true){
                $dir = wp_get_upload_dir();
                move_uploaded_file($file_tmp, $dir['basedir']."/users/".$file_name);
                update_user_meta( $user_id, 'logo', htmlentities( $_FILES['logo']['name'] ) );
                echo "Success";
            }else{
                print_r($errors);
            }

        }

        update_user_meta( $user_id, 'number', htmlentities( $_POST[ 'number' ] ) );
        update_user_meta( $user_id, 'company_name', htmlentities( $_POST[ 'company_name' ] ) );

        update_user_meta( $user_id, 'website', htmlentities( $_POST[ 'website' ] ) );
        update_user_meta( $user_id, 'contactname', htmlentities( $_POST[ 'contactname' ] ) );
        update_user_meta( $user_id, 'insta', htmlentities( $_POST[ 'insta' ] ) );
        update_user_meta( $user_id, 'fb', htmlentities( $_POST[ 'fb' ] ) );
        update_user_meta( $user_id, 'twitter', htmlentities( $_POST[ 'twitter' ] ) );
        update_user_meta( $user_id, 'pinterest', htmlentities( $_POST[ 'pinterest' ] ) );
        update_user_meta( $user_id, 'youtube', htmlentities( $_POST[ 'youtube' ] ) );
        update_user_meta( $user_id, 'newsletter', htmlentities( $_POST[ 'newsletter' ] ) );
        update_user_meta( $user_id, 'shortbio', htmlentities( $_POST[ 'shortbio'] ) );
        update_user_meta( $user_id, 'traveler_type',  $_POST['traveler_type' ] );

    /*    $query_args = array(
            'post_type'     => 'job_listing',
            'fields'     => 'ids',
            'posts_per_page' => -1,
            'post_status'  => 'any',
            'author'     => intval($user_id)
        );

        $job_listing_data = new WP_Query( $query_args);

        if ($job_listing_data->have_posts()):
            foreach( $job_listing_data->posts as $id ):
                echo $id.'<br>';
                update_post_meta( $id, '_company_name', htmlentities( $_POST[ 'company_name' ] ) );
                update_post_meta( $id, '_company_website', htmlentities( $_POST[ 'website' ] ) );
                update_post_meta( $id, '_company_tagline ', htmlentities( $_POST[ 'shortbio'] ) );
                update_post_meta( $id, '_company_logo ', htmlentities( $_FILES['logo']['name'] ) );
            endforeach;
        endif;

        wp_reset_postdata();*/

    }
    if($str->roles[0] == "administrator") {

        if(isset($_FILES['logo']['name']) && !empty($_FILES['logo']['name'])){
            $errors= array();
            $file_name = $_FILES['logo']['name'];
            $file_size =$_FILES['logo']['size'];
            $file_tmp =$_FILES['logo']['tmp_name'];
            $file_type=$_FILES['logo']['type'];
            $file_ext=strtolower(end(explode('.',$_FILES['logo']['name'])));

            $expensions= array("jpeg","jpg","png");

            if(in_array($file_ext,$expensions)=== false){
                $errors[]="extension not allowed, please choose a JPEG or PNG file.";
            }

            if($file_size > 2097152){
                $errors[]='File size must be excately 2 MB';
            }
            if(empty($errors)==true){
                $dir = wp_get_upload_dir();
                move_uploaded_file($file_tmp, $dir['basedir']."/users/".$file_name);
                update_user_meta( $user_id, 'logo', htmlentities( $_FILES['logo']['name'] ) );
                update_user_meta( $user_id, 'photo', htmlentities( $_FILES['logo']['name'] ) );
                echo "Success";
            }else{
                print_r($errors);
            }

        }

        update_user_meta( $user_id, 'number', htmlentities( $_POST[ 'number' ] ) );
        update_user_meta( $user_id, 'phone_number', htmlentities( $_POST[ 'number' ] ) );

        update_user_meta( $user_id, 'company_name', htmlentities( $_POST[ 'company_name' ] ) );

        update_user_meta( $user_id, 'website', htmlentities( $_POST[ 'website' ] ) );
        update_user_meta( $user_id, 'insta', htmlentities( $_POST[ 'insta' ] ) );
        update_user_meta( $user_id, 'fb', htmlentities( $_POST[ 'fb' ] ) );
        update_user_meta( $user_id, 'twitter', htmlentities( $_POST[ 'twitter' ] ) );
        update_user_meta( $user_id, 'pinterest', htmlentities( $_POST[ 'pinterest' ] ) );
        update_user_meta( $user_id, 'youtube', htmlentities( $_POST[ 'youtube' ] ) );

        update_user_meta( $user_id, 'shortbio', htmlentities( $_POST[ 'shortbio'] ) );

        update_user_meta( $user_id, 'jrrny_link', htmlentities( $_POST[ 'jrrny_link' ] ) );
        update_user_meta( $user_id, 'monthlyvisit', htmlentities( $_POST[ 'monthlyvisit' ] ) );

        update_user_meta( $user_id, 'newsletter', htmlentities( $_POST[ 'newsletter' ] ) );
        update_user_meta( $user_id, 'newsletter_subscriber_count', htmlentities( $_POST[ 'newsletter_subscriber' ] ) );

        update_user_meta( $user_id, 'traveler_type',  $_POST['traveler_type' ] );
        update_user_meta( $user_id, 'location', htmlentities( $_POST['location'] ) );


    }
}// end func

function custom_admin_js() {
    $url = get_bloginfo('template_directory') . '/js/wp-admin.js';
    echo '"<script type="text/javascript" src="'. $url . '"></script>"';
    ?>
    <script>
        // Variable type options are valid for variable workshop.
        //$( '.show_if_variable:not(.hide_if_gift-card)' ).addClass( 'show_if_gift-card' );

        // Trigger change
        jQuery( 'select#product-type' ).change(function(){

            // Show variable type options when new attribute is added.


            jQuery( '#product_attributes .show_if_variable:not(.hide_if_gift-card)' ).addClass( 'show_if_gift-card' );

            var $attributes     = jQuery( '#product_attributes' ).find( '.woocommerce_attribute' );

            if (jQuery( 'select#product-type' ).val() =="job_package") {
                //alert('job_package');
                //$attributes.find( '.enable_variation' ).show();
                jQuery('.variations_tab' ).css({'display':'block !important'});
                jQuery('.variations_tab' ).addClass('test');
                jQuery('#product_attributes .show_if_variable' ).addClass('test');
            }
        });


    </script>
<?php
}
add_action('admin_footer', 'custom_admin_js');

function custom_admin_css()
{
    ?>
    <style type="text/css">
        ul li.test{ display:block !important;}
        #product_attributes .test{ display:block !important;}
    </style>
<?php
}
add_action('admin_footer', 'custom_admin_css');

remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
//add_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_thumbnails', 20 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

add_action( 'woocommerce_single_product_summary','add_new_package');

function add_new_package()
{
    $queried_object = get_queried_object();

    if ( $queried_object ) {
        $post_id = $queried_object->ID;
        // echo $post_id;
    }

    $args = array(
        'post_type'     => 'product_variation',
        'post_status'   => array( 'private', 'publish' ),
        'numberposts'   => -1,
        'orderby'       => 'menu_order',
//'order'         => 'asc',
        'post_parent'   => $post_id // $post->ID
    );
    $variations = get_posts( $args );
//echo "<pre>"; print_r($variations); echo "</pre>";
    echo '<section id="pricePlans">
		<ul id="plans">	';
    echo do_shortcode('[submit_job_form]');
    $current_page_id = $wp_query->post->ID;
    echo $current_page_id;
    $category_detail=get_the_category($current_page_id);
    echo $category_detail;

    $counter=1;
    foreach($variations as $variation)
    {
        ?>
        <li class="plan" package="<?php echo $variation->ID; ?>">
            <ul class="planContainer">
                <li class="title"><h2  <?php if($counter==2) { ?>class="bestPlanTitle"<?php } ?>><?php  echo $variation->post_title; ?></h2></li>
                <li class="price"><p <?php if($counter==2) { ?> class="bestPlanPrice"<?php } ?>> <?php
                        global $woocommerce;
                        $product_variation = new WC_Product_Variation($variation->ID);
                        $regular_price = $product_variation->regular_price;
                        echo get_woocommerce_currency_symbol().''.$regular_price;
                        ?>/Amount</p></li>
                <li>
                    <ul class="options">
                        <?php echo get_post_meta( $variation->ID, '_variation_description', true ); ?>
                    </ul>
                </li>
                <li class=""><a href="http://traversebyjrrny.com/?add-to-cart=<?php echo $post_id ; ?>&variation_id=<?php echo $variation->ID; ?>" class="letmecheck">Add to cart</a></li>
                <li class="button"><a class="bestPlanButton">Purchase</a></li>
            </ul>
        </li>
        <?php
        $counter++;
    }
    ?>
    <?php
    echo '</ul></section>';
}

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action('init', 'myStartSession', 1);
function myStartSession() {
    if(!session_id()) {
        session_start();
    }
}

function clear_cart() {
    if( function_exists('WC') ){
        WC()->cart->empty_cart();
    }
}
add_action('wp_logout', 'clear_cart');

add_filter( 'job_manager_packages_admin_required_packages_frontend', 'smyles_packages_demo_admin_require_packages' );
function smyles_packages_demo_admin_require_packages(){
    return true;
}


add_action( 'woocommerce_archive_description','add_package_list');
function add_package_list(){
    echo do_shortcode('[submit_job_form]');
}

//add_shortcode( 'my_products', 'bbloomer_user_products_bought' );

function bbloomer_user_products_bought() {
    global $product, $woocommerce, $woocommerce_loop;
    $columns = 3;
    $current_user = wp_get_current_user();
    $args = array(
        'post_type'             => 'product',
        'posts_per_page'    => -1,
        'post_status'           => 'publish',
        'tax_query'         => array(
            array(
                'taxonomy' => 'product_type',
                'field'    => 'slug',
                'terms'    => 'job_package'
            ))

    );
    $loop = new WP_Query($args);

    woocommerce_product_loop_start();

    while ( $loop->have_posts() ) :
        $loop->the_post();
        $theid = get_the_ID();
        if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $theid ) ) {
            wc_get_template_part( 'content', 'product' );
        }
    endwhile;

    woocommerce_product_loop_end();

    woocommerce_reset_loop();
    wp_reset_postdata();

}

/**********************************Adding status to the job applicatioins ***********************************/


add_filter( 'job_application_statuses', 'add_new_job_application_status' );

function add_new_job_application_status( $statuses ) {
    $statuses['hired'] = _x( 'In Progress', 'job_application', 'wp-job-manager-applications' );
    $statuses['completed'] = _x( 'Completed', 'job_application', 'wp-job-manager-applications' );
    $statuses['in_review'] = _x( 'In review', 'job_application', 'wp-job-manager-applications' );

    unset($statuses['interviewed']);
    unset($statuses['offer']);

    return $statuses;
}
/***********************************************************************************************************/


/******************** shortcode for the applications with status In Progress / Completed *******************/

function get_candidate_projects()
{
    global $wpdb;
    // If user is not logged in, abort
    if ( ! is_user_logged_in() ) {
        do_action( 'job_manager_job_applications_past_logged_out' );
        return;
    }

    $args = apply_filters( 'job_manager_job_applications_past_args', array(
        'post_type'           => 'job_application',
        'post_status'         => array_keys( get_job_application_statuses() ),//array_keys( get_job_application_statuses() )array('In Progress','Completed')
        'posts_per_page'      => 25,
        'offset'              => ( max( 1, get_query_var('paged') ) - 1 ) * 25,
        'ignore_sticky_posts' => 1,
        'meta_key'            => '_candidate_user_id',
        'meta_value'          => get_current_user_id(),
    ) );

    $applications = new WP_Query( $args );

    ob_start();

    if ( $applications->have_posts() ) {
        get_job_manager_template( 'project-applications.php', array( 'applications' => $applications->posts, 'max_num_pages' => $applications->max_num_pages ), 'wp-job-manager-applications', JOB_MANAGER_APPLICATIONS_PLUGIN_DIR . '/templates/' );
    } else {
        get_job_manager_template( 'past-applications-none-projects.php', array(), 'wp-job-manager-applications', JOB_MANAGER_APPLICATIONS_PLUGIN_DIR . '/templates/' );
    }

    //return ob_get_clean();
}

add_shortcode('candidate_projects','get_candidate_projects');

/*************************************************************************************************************/

//// Send an email to the employer when a job listing is approved

function listing_published_send_email($post_id) {
    if( 'job_listing' != get_post_type( $post_id ) ) {
        return;
    }
    $post = get_post($post_id);
    $author = get_userdata($post->post_author);

    $message = "
	  Hi ".$author->display_name.",
	  Your listing, ".$post->post_title." has just been approved at ".get_permalink( $post_id ).". Well done!
	";
    @wp_mail($author->user_email, "Your job listing is online", $message);
}
add_action('pending_to_publish', 'listing_published_send_email');
add_action('pending_payment_to_publish', 'listing_published_send_email');

//////// Send an email to the employer when a job listing expires

function listing_expired_send_email($post_id) {
    $post = get_post($post_id);
    $author = get_userdata($post->post_author);

    $message = "
      Hi ".$author->display_name.",
      Your listing, ".$post->post_title." has now expired: ".get_permalink( $post_id );
    @wp_mail($author->user_email, "Your job listing has expired", $message);
}
add_action('expired_job_listing', 'listing_expired_send_email');

/////////// Send an email to the candidate when their resume is approved

function resume_published_send_email($post_id) {
    if( 'resume' != get_post_type( $post_id ) ) {
        return;
    }
    $post = get_post($post_id);
    $author = get_userdata($post->post_author);

    $message = "
      Hi ".$author->display_name.",
      Your resume, ".$post->post_title." has just been approved at ".get_permalink( $post_id ).". Well done!
   ";
    @wp_mail($author->user_email, "Your resume is online", $message);
}
add_action('pending_to_publish', 'resume_published_send_email');
add_action('pending_payment_to_publish', 'resume_published_send_email');


/**
 * Snippet Name: Redirect to homepage after logout
 * Snippet URL: http://www.wpcustoms.net/snippets/redirect-homepage-logout/
 */
function wpc_auto_redirect_after_logout(){
    wp_redirect( home_url() );
    exit();
}
add_action('wp_logout','wpc_auto_redirect_after_logout');

function add_script_in_last()
{
    ?>
    <script>

        if (jQuery('.tax-product_cat article form').hasClass('job-manager-form')) {
            jQuery('.tax-product_cat article ul.products').remove();
        }


        jQuery('.tax-product_cat article form#job_preview').attr('class','job_preview_artifex');


        if (jQuery('.tax-product_cat article form').hasClass('job_preview_artifex')) {

            jQuery('.tax-product_cat article ul.products').remove();

        }

    </script>
<?php
}
add_action('wp_footer','add_script_in_last');



//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function my_custom_friends_filter($friends) {

    // Get current user information //

    global $current_user;
    get_currentuserinfo();

    // Get friend list of the current user with the help of your custom function
    // It will be an array containing friends id like array('2','5','7')
    //

    // $friends = get_friend_list($current_user->ID);
    $userid = get_current_user_id();
    $user_meta=get_userdata($userid);
    $user_roles=$user_meta->roles[0];
    if($user_roles=="candidate")
    {


        global $wpdb;
        // If user is not logged in, abort
        if ( ! is_user_logged_in() ) {
            do_action( 'job_manager_job_applications_past_logged_out' );
            return;
        }


        $friends=array();
        $args = apply_filters( 'job_manager_job_applications_past_args', array(
            'post_type'           => 'job_application',
            'post_status'         => array_keys( get_job_application_statuses() ),//array_keys( get_job_application_statuses() )array('In Progress','Completed')
            'posts_per_page'      => 25,
            'offset'              => ( max( 1, get_query_var('paged') ) - 1 ) * 25,
            'ignore_sticky_posts' => 1,
            'meta_key'            => '_candidate_user_id',
            'meta_value'          => get_current_user_id(),
        ) );

        $applications = new WP_Query( $args );

        ob_start();

        if ( $applications->have_posts() ) {
            foreach ( $applications as $application ) {
                global $wp_post_statuses;

                $application_id = $application->ID;
                $job_id         = wp_get_post_parent_id( $application_id );
                $job            = get_post( $job_id );
                $job_title      = get_post_meta( $application_id, '_job_applied_for', true );
                $user_id      = get_post_meta( $application_id, '_job_author', true );
                array_push($friends,$order);

            }

        } else {

        }

    }

    elseif($user_roles=="employer")
    {
        $args = array(
            'post_type'     => 'job_listing',
            'post_status'   => array( 'private', 'publish' ),
            'numberposts'   => -1,
            'orderby'       => 'menu_order',
//'order'         => 'asc',
            'meta_key'            => '_job_author',
            'meta_value'          => get_current_user_id(),
        );

        $listings = new WP_Query( $args );

        ob_start();
        $friends=array();
        if ( $listings->have_posts() ) {
            foreach($listings as $listing)
            {
                global $wp_post_statuses;

                $listing_id = $listing->ID;


                $args = apply_filters( 'job_manager_job_applications_past_args', array(
                    'post_type'           => 'job_application',
                    'post_status'         => array_keys( get_job_application_statuses() ),//array_keys( get_job_application_statuses() )array('In Progress','Completed')
                    'posts_per_page'      => 25,
                    'offset'              => ( max( 1, get_query_var('paged') ) - 1 ) * 25,
                    'ignore_sticky_posts' => 1,
                    'meta_key'            => 'post_parent',
                    'meta_value'          => $listing_id,
                ) );

                $applications = new WP_Query( $args );

                ob_start();

                if ( $applications->have_posts() ) {
                    foreach ( $applications as $application ) {
                        global $wp_post_statuses;

                        $application_id = $application->ID;
                        $job_id         = wp_get_post_parent_id( $application_id );
                        $job            = get_post( $job_id );
                        $job_title      = get_post_meta( $application_id, '_job_applied_for', true );
                        $user_id      = get_post_meta( $application_id, '_job_author', true );
                        array_push($friends,$order);



                    }

                }


            }

        }
    }
    elseif($user_roles=="guest")
    {
        $friends=array();
        ?>
        <style> .ifc #ifc-app-container.ifc-light .ifc-chat-list .ifc-chat-list-roster .ifc-chat-list-roster-sub-group.ifc-chat-list-roster-room
            {
                display:block !important;
            }

        </style>
    <?php
    }
    else
    {}

    return $friends;
}
add_filter('iflychat_get_user_friends_filter', 'my_custom_friends_filter');

define('DEPOSIT_ID', 1286); //live
//define('DEPOSIT_ID', 1397); //local


/*
add_action('woocommerce_checkout_process', 'c_custom_checkout_field_process');
function c_custom_checkout_field_process() {
// Check if set, if its not set add an error.
if ( ! $_POST['c_type'] && ($_POST['payment_method'] == 'cheque'))
wc_add_notice( __( 'Please enter the custom field.' ), 'error' );
?>
<script>
alert(<?php echo $_SESSION['job_id']; ?>);
</script>
<?php

}*/

add_action('admin_footer','add_css_in_header');
function add_css_in_header()
{
    ?>
    <script>
        jQuery('input#acf-field-Budget_for_the_influencer').attr('disabled','disabled');
        jQuery('input#acf-field-job_listing_order_id').attr('disabled','disabled');
    </script>
<?php
}


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action('show_user_profile', 'my_user_profile_edit_action');
function my_user_profile_edit_action($user) {

    ?>
    <h3>Phone Number</h3>
    <label for="artwork_approved">
        <input name="phn_number" type="text" id="artwork_approved" value="">
    </label>
<?php
}


add_filter( 'submit_job_form_fields', 'frontend_add_category_field' );

function frontend_add_category_field( $fields ) {
    $fields['job']['job_category'] = array(
        'label'       => __( 'Salary ($)', 'job_manager' ),
        'type'        => 'text',
        'placeholder' => 'e.g. 20000',
        'description' => '',
        'priority'    => 7
    );
    return $fields;
}

add_filter( 'job_manager_job_listing_data_fields', 'admin_add_category_field' );

function admin_add_category_field( $fields ) {
    $fields['_job_category'] = array(
        'label'       => __( 'Job category', 'job_manager' ),
        'type'        => 'text',
        'placeholder' => '',
        'description' => ''
    );
    return $fields;
}

add_action( 'job_manager_update_job_data', 'update_employer_woocommerce_fields', 100, 2 );

function update_employer_woocommerce_fields( $job_id, $values ){
    $user_id = get_current_user_id();
    $user = get_userdata( $user_id );

    $my_input_id = isset( $_POST['job_category'] ) ? intval( $_POST['job_category'] ) : false;
    $my_input_name = isset( $_POST['job_category_name'] ) ? sanitize_text_field( $_POST['job_category_name'] ) : false;
    $job_company_name = isset( $_POST['job_company_name'] ) ? sanitize_text_field( $_POST['job_company_name'] ) : false;


    if (term_exists( $my_input_id, 'job_listing_category' )){
        wp_set_object_terms( $job_id, $my_input_id, 'job_listing_category' );
    }else{
        wp_set_object_terms( $job_id, $my_input_name, 'job_listing_category' );
    }

    if( $my_input_name ) update_post_meta( $job_id, '_job_category', $my_input_name );

    if( $job_company_name ) update_post_meta( $job_id, '_company_name', $job_company_name );

   /* if ( $user ){
        $str = get_userdata($user_id);

        if($str->roles[0] == "employer"){
            update_user_meta( $user_id, 'billing_company', htmlentities( $_POST[ 'company_name' ]) );
            update_user_meta( $user_id, 'website', htmlentities( $_POST[ 'company_website' ] ) );
            update_user_meta( $user_id, 'company_name', htmlentities( $_POST[ 'company_name' ] ) );
            update_user_meta( $user_id, 'shortbio', htmlentities( $_POST[ 'company_tagline'] ) );
            update_user_meta( $user_id, 'logo', htmlentities( $_FILES['company_logo']['name'] ) );

        }
    }*/

}
//add_action( 'job_manager_update_job_data', 'add_deposit_to_order', 100, 2 );

function add_deposit_to_order( $job_id, $values ){
    global $woocommerce;

    $deposit_product_id = DEPOSIT_ID;

    if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
        foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
            $_product = $values['data'];

            if ( $_product->id == $deposit_product_id )
                $found = true;
        }
        // if product not found, add it
        if ( ! $found )
            WC()->cart->add_to_cart( $deposit_product_id );
    } else {
        // if no products in cart, add it
        WC()->cart->add_to_cart( $deposit_product_id );
    }

}

//add_action( 'woocommerce_before_calculate_totals', 'add_custom_price' );

function add_custom_price( $cart_object ) {

    $deposit_product_id = DEPOSIT_ID;
    if (isset ($_SESSION['deposit_value']))
    $custom_price = $_SESSION['deposit_value'];

    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

        if($cart_item['data']->get_id() == $deposit_product_id){

            $cart_item['data']->set_price($custom_price);
        }

    }


}

//add_action( 'woocommerce_before_checkout_form', 'deposit_add_checkout_notice', 11 );
function deposit_add_checkout_notice() {
    global $woocommerce;

    $products = array();
    if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
        foreach ( WC()->cart->get_cart() as  $values ) {

            $_product = $values['data'];
            wc_add_to_cart_message( $_product->id );
            $products[] = $_product->id;
        }

    }
}



/*add_filter( 'submit_resume_form_save_resume_data', 'update_candidate_woocommerce_fields' );

function update_candidate_woocommerce_fields(){
    $user_id = get_current_user_id();
    $user = get_userdata( $user_id );
    if ( $user ){
        $str = get_userdata($user_id);
        if($str->roles[0] == "candidate"){

            update_user_meta( $user_id, 'website', htmlentities( $_POST[ 'influencer_website' ] ) );
            update_user_meta( $user_id, 'monthlyvisit', htmlentities( $_POST[ 'estimated_monthly_visitors' ] ) );
            update_user_meta( $user_id, 'fb', htmlentities( $_POST[ 'facebook_link' ] ) );
            update_user_meta( $user_id, 'insta', htmlentities( $_POST[ 'instagram_link' ] ) );
            update_user_meta( $user_id, 'twitter', htmlentities( $_POST[ 'twitter_link' ] ) );
            update_user_meta( $user_id, 'youtube', htmlentities( $_POST[ 'youtube_link' ] ) );
            update_user_meta( $user_id, 'newsletter', htmlentities( $_POST[ 'newsletter' ] ) );
            update_user_meta( $user_id, 'shortbio', htmlentities( $_POST[ 'short_influencer_bio'] ) );
            update_user_meta( $user_id, 'video', htmlentities( $_POST['video_sample_embed'] ) );
            update_user_meta( $user_id, 'location', htmlentities( $_POST['candidate_location'] ) );
            update_user_meta( $user_id, 'photo', htmlentities( $_FILES['candidate_photo']['name'] ) );
        }

    }
}*/



//add_action( 'woocommerce_thankyou', 'bbloomer_checkout_save_user_meta');

function bbloomer_checkout_save_user_meta( $order_id ) {
    global $wp_session;
    $wp_session = WP_Session::get_instance();
    session_start();
    global $post;
    $order = new WC_Order( $order_id );
    $user_id = $order->user_id;

    $user_meta=get_userdata($user_id);

    $user_roles=$user_meta->roles;

    if($user_roles[0]=="employer")
    {
        update_field('Budget_for_the_influencer', $_SESSION['deposit_value'], $_SESSION['job_id'] );
        update_field('job_listing_order_id', $order_id, $_SESSION['job_id']);
    }

    ?>
    <script>
        jQuery(document).ready(function(){
            setTimeout(function() {
                window.location.href = "<?php echo get_site_url(); ?>/job-dashboard/"
            }, 5000);
        });
    </script>
    <?php

}


function isa_order_received_text( $text, $order ) {

    $new =  ' Your deposit has been received.  Influencers should begin to appear in your Influencers section within 48 hours.';
    return $new;

}
//add_filter('woocommerce_thankyou_order_received_text', 'isa_order_received_text', 10, 2 );



add_action('wp_ajax_application_form_handler_direct', 'application_form_handler_direct');
add_action('wp_ajax_nopriv_application_form_handler_direct', 'application_form_handler_direct');

function application_form_handler_direct() {
    parse_str( $_POST['form_data'], $formData);
    if ( ! empty( $formData['wp_job_manager_send_application_directly'] ) ) {
        try {
            $values = array();
            $job_id = absint( $formData['job_id'] );
            $job    = get_post( $job_id );
            $meta   = array();

            $meta['_secret_dir']      = '';
            $meta['_attachment']      = array();
            $meta['_attachment_file'] = array();

            if ( empty( $job_id ) || ! $job || 'job_listing' !== $job->post_type ) {
                wp_send_json_error(array('error' => __( 'Invalid job', 'wp-job-manager-applications' )));
            }

            if ( 'publish' !== $job->post_status ) {
                wp_send_json_error(array('error' => __( 'That job is not available', 'wp-job-manager-applications' )));
            }

            if ( get_option( 'job_application_prevent_multiple_applications' ) && user_has_applied_for_job( get_current_user_id(), $job_id ) ) {
                wp_send_json_error(array('error' => __( 'You have already applied for this job', 'wp-job-manager-applications' )));
            }

            $resume_id = absint( $formData['resume_id'] );

            $application_message = $formData['application_message'];


            $meta['_resume_id'] = absint( $resume_id );

            $application_message = implode( "\n\n", $application_message );

            $current_user = is_user_logged_in() ? wp_get_current_user() : false;

            $author_resume = get_post_field('post_author', $resume_id, 'db');

            if ( $current_user != $author_resume ) {
                $from_name = $current_user->first_name . ' ' . $current_user->last_name;
            }else{
                $from_name = get_the_title( $resume_id );
            }

            if ( $current_user ) {
                $from_email = $current_user->user_email;
            }

            $meta     = apply_filters( 'job_application_form_posted_meta', $meta, $values );


            if ( ! $application_id = create_job_application( $job_id, $from_name, $from_email, $application_message, $meta ) ) {
                wp_send_json_error(array('error' => __( 'Could not create job application', 'wp-job-manager-applications' )));
            }

            $args = array(
                'post_type'        => 'application_message',
                'post_status'      => 'any',
                'posts_per_page'   => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => '_target_resume',
                        'value' => $resume_id
                    ),
                    array(
                        'key' => '_target_job',
                        'compare' => $job_id,
                    )

                )
            );

            $messages = new WP_Query( $args );

            if ($messages ->have_posts()){
                while ($messages ->have_posts()){
                    $messages -> the_post();
                    update_post_meta( $messages->post->ID, '_target_application', $application_id );
                }
            }

            // Candidate email
            $candidate_email_content = get_job_application_candidate_email_content();
            if ( $candidate_email_content ) {
                $existing_shortcode_tags = $GLOBALS['shortcode_tags'];
                remove_all_shortcodes();
                job_application_email_add_shortcodes( array(
                    'application_id'      => $application_id,
                    'job_id'              => $job_id,
                    'user_id'             => get_current_user_id(),
                    'candidate_name'      => $from_name,
                    'candidate_email'     => $from_email,
                    'application_message' => $application_message,
                    'meta'                => $meta
                ) );
                $subject = do_shortcode( get_job_application_candidate_email_subject() );
                $message = do_shortcode( $candidate_email_content );
                $message = str_replace( "\n\n\n\n", "\n\n", implode( "\n", array_map( 'trim', explode( "\n", $message ) ) ) );
                $is_html = ( $message != strip_tags( $message ) );

                // Does this message contain formatting already?
                if ( $is_html && ! strstr( $message, '<p' ) && ! strstr( $message, '<br' ) ) {
                    $message = nl2br( $message );
                }

                $GLOBALS['shortcode_tags'] = $existing_shortcode_tags;
                $headers   = array();
                $headers[] = 'From: ' . get_bloginfo( 'name' ) . ' <noreply@' . str_replace( array( 'http://', 'https://', 'www.' ), '', site_url( '' ) ) . '>';
                $headers[] = $is_html ? 'Content-Type: text/html' : 'Content-Type: text/plain';
                $headers[] = 'charset=utf-8';

                wp_mail(
                    apply_filters( 'create_job_application_candidate_notification_recipient', $from_email, $job_id, $application_id ),
                    apply_filters( 'create_job_application_candidate_notification_subject', $subject, $job_id, $application_id ),
                    apply_filters( 'create_job_application_candidate_notification_message', $message ),
                    apply_filters( 'create_job_application_candidate_notification_headers', $headers, $job_id, $application_id ),
                    apply_filters( 'create_job_application_candidate_notification_attachments', array(), $job_id, $application_id )
                );
            }

            // Message to display
            add_action( 'job_content_start', array( $this, 'application_form_success' ) );

            // Trigger action
            do_action( 'new_job_application', $application_id, $job_id );

        } catch ( Exception $e ) {
            $this->error = $e->getMessage();
            add_action( 'job_content_start', array( $this, 'application_form_errors' ) );
        }
        wp_send_json_success();
    }
    wp_send_json_error();
	}


function get_paid_listings_employer_packages( $user_id ) {
    global $wpdb;

    $listings_data = array();
    $packages = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcpl_user_packages WHERE user_id = %d AND package_type IN ( 'job_listing' ) ;", $user_id ), OBJECT_K );

    foreach ($packages as $package){
        $_product = wc_get_product( $package->product_id );

        $listing_data = array();
        $listing_data['product_id'] = $package->product_id;
        $listing_data['name'] = $_product->get_name();
        $listing_data['currency'] = get_woocommerce_currency_symbol();
        $listing_data['price'] = $_product->get_price();

        $args = array(
            'post_type'     => 'job_listing',
            'post_status'   => 'any',
            'posts_per_page'=> 1,
            'author'        => $user_id,
            'meta_key'      => '_wcpl_jmfe_product_id',
            'meta_value'    => $package->product_id
        );

        $jobs_query = new WP_Query( $args );

        if ( $jobs_query -> have_posts())
            $listing_data['listing'] = $jobs_query->posts;

        $listings_data[] = $listing_data;
    }

    return $listings_data;
}

function get_employer_account_balance_info($user_id){

    $args = array(
        'post_type'     => 'job_listing',
        'post_status'   => 'any',
        'posts_per_page'   => -1,
        'author'        => $user_id,
        'fields'        => 'ids'
    );

    $jobs_query = new WP_Query( $args );

    if ($jobs_query -> have_posts())  $jobs = $jobs_query -> posts; else __return_false();

    $args_1 = apply_filters( 'job_manager_job_applications_past_args', array(
        'post_type'           => 'job_application',
        'post_status'         => 'completed',
        'posts_per_page'      => -1,
        'ignore_sticky_posts' => 1,
        'post_parent__in'     => $jobs
    ) );


    $applications = new WP_Query( $args_1 );

    $applications_list = array();

    if ( $applications ->have_posts()){
        while ( $applications ->have_posts() ) {
            $applications -> the_post();

            $application_id     = $applications->post ->ID;
            $application_status = get_post_status( $application_id );

            $job_id             = wp_get_post_parent_id( $application_id );
            $job                = get_post( $job_id );

            if ( get_post_status($job_id) == 'publish' ){
                $job_link = get_permalink($job_id);
            }
            $job_name           = get_post_meta( $application_id, '_job_applied_for', true );
            $job_author_id      = get_post_meta( $application_id, '_job_author', true );
            $job_title          = ( $job_link )?( '<a href="'.$job_link.'">'.$job_name.'</a>' ):$job_name;
            $job_price          = get_post_meta( $job_id, '_targeted_budget', true );
            $resume_id          = get_job_application_resume_id( $application_id );
            $resume_title       = get_the_title( $resume_id );

            if ( !$job_price ) $job_price = get_post_meta($job_id, 'Budget_for_the_influencer', true );

            $current_apl = array();

            $current_apl['application_id']      = $application_id;
            $current_apl['application_status']  = $application_status;
            $current_apl['job_status']          = get_post_status($job_id);
            $current_apl['job_id']              = $job_id;
            $current_apl['job_title']           = $job_title;
            $current_apl['job_price']           = $job_price;
            $current_apl['influencer_id']       = $resume_id;
            $current_apl['currency']            = get_woocommerce_currency_symbol();

            $applications_list[] = $current_apl;

            unset($application_id);
            unset($job_id);
            unset($job_link);

        }
        wp_reset_postdata();
    }
    return $applications_list;

}

function get_candidate_account_balance_info($user_id){

    $args = apply_filters( 'job_manager_job_applications_past_args', array(
        'post_type'           => 'job_application',
        'post_status'         => array_keys( get_job_application_statuses() ),
        'posts_per_page'      => -1,
        'ignore_sticky_posts' => 1,
        'meta_key'            => '_candidate_user_id',
        'meta_value'          => $user_id,
    ) );
    $applications_list = array();
    $applications = new WP_Query( $args );
    if ( $applications ->have_posts()){
        while ( $applications ->have_posts() ) {
            $applications -> the_post();

            $application_id     = $applications->post ->ID;
            $application_status = get_post_status( $application_id );

            $job_id             = wp_get_post_parent_id( $application_id );
            $job                = get_post( $job_id );

            if ( get_post_status($job_id) == 'publish' ){
                $job_link = get_permalink($job_id);
            }
            $job_name           = get_post_meta( $application_id, '_job_applied_for', true );
            $job_author_id      = get_post_meta( $application_id, '_job_author', true );
            $job_title          = ( $job_link )?( '<a href="'.$job_link.'">'.$job_name.'</a>' ):$job_name;
            $job_price          = get_post_meta( $job_id, '_targeted_budget', true );

            if ( !$job_price ) $job_price = get_post_meta($job_id, 'Budget_for_the_influencer', true );

            $current_apl = array();

            $current_apl['application_id']      = $application_id;
            $current_apl['application_status']  = $application_status;
            $current_apl['job_status']          = get_post_status($job_id);
            $current_apl['job_id']              = $job_id;
            $current_apl['job_title']           = $job_title;
            $current_apl['job_price']           = $job_price;

            $applications_list[] = $current_apl;

            unset($application_id);
            unset($job_id);
            unset($job_link);

        }
        wp_reset_postdata();
    }
    return $applications_list;

}


function get_candidate_cash_out_sum($user_id){

    $args = apply_filters( 'job_manager_job_applications_past_args', array(
        'post_type'           => 'job_application',
        'post_status'         => 'completed',
        'posts_per_page'      => -1,
        'ignore_sticky_posts' => 1,
        'meta_key'            => '_candidate_user_id',
        'meta_value'          => $user_id,
    ) );

    $applications_list = array();
    $applications = new WP_Query( $args );
    $available_cash = 0;
    if ( $applications ->have_posts()){
        while ( $applications ->have_posts() ) {
            $applications -> the_post();

            $application_id     = $applications->post ->ID;

            $job_id             = wp_get_post_parent_id( $application_id );

            $job                = get_post( $job_id );

            if ( !$job ) return false;


            $job_price          = get_post_meta( $job_id, '_targeted_budget', true );
            if ( !$job_price ) $job_price = get_post_meta($job_id, 'Budget_for_the_influencer', true );

            $available_cash += $job_price;
        }
        wp_reset_postdata();
    }

    $sum_in_log = get_user_meta( $user_id, '_available_money', true );

    return $sum_in_log;

}

require_once 'message_system.php';



if ( ! function_exists( 'get_application_id_user_has_applied_for_job' ) ) {

    function get_application_id_user_has_applied_for_job( $user_id, $job_id ) {
        if ( ! $user_id ) {
            return false;
        }
        $application=  get_posts( array(
            'post_type'      => 'job_application',
            'post_status'    => array_merge( array_keys( get_job_application_statuses() ), array( 'publish' ) ),
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'post_parent'    => $job_id,
            'meta_query'     => array(
                array(
                    'key' => '_candidate_user_id',
                    'value' => absint( $user_id )
                )
            )
        ) ) ;

        if ( count($application)>0 ){
            return $application[0];
        }
    }
}




if ( ! function_exists( 'brand_has_listing' ) ) {

    function brand_has_listing(  ) {

        $current_user_id = get_current_user_id();

        $user_meta = get_userdata($current_user_id);

        $user_roles = $user_meta->roles[0];

        if( $user_roles != "candidate" && $user_roles != "guest" ){

            $query_args = array(
                'post_type'              => 'job_listing',
                'post_status'            => 'publish',
                'ignore_sticky_posts'    => 1,
                'posts_per_page'         => -1,
                'author'                 => $current_user_id
            );

            $result = new WP_Query( $query_args );

            if ( $result -> have_posts() ) return $result->post_count;

        } else
            return false;

    }
}


if ( ! function_exists( 'get_brand_listings_list' ) ) {

    function get_brand_listings_list( $select_view = false ) {
        global $post;

        $current_resume_id = $post->ID;
        $candidate_id     = get_post_field( 'post_author', $current_resume_id );

        $current_user_id = get_current_user_id();

        $user_meta = get_userdata($current_user_id);

        $user_roles = $user_meta->roles[0];

        if( $user_roles != "candidate" && $user_roles != "guest" ){

            $query_args = array(
                'post_type'              => 'job_listing',
                'post_status'            => 'publish',
                'ignore_sticky_posts'    => 1,
                'posts_per_page'         => -1,
                'author'                 => $current_user_id
            );

            $result = new WP_Query( $query_args );

            if ( $result -> have_posts()  ){

                if ( !$select_view ) return $result;

                else {

                    $select_view = '<select name="job_id">';

                    while ( $result ->have_posts()  ) :

                        $result->the_post();

                        $theid = get_the_ID();
                        $thename = get_the_title();

                        $select_view .= '<option value="'.$theid.'">'.$thename;
                        if (user_has_applied_for_job( $candidate_id, $theid ) ) $select_view .=' <b> - applied for this job</b>';
                        $select_view .='</option>';

                    endwhile;

                    wp_reset_postdata();

                    $select_view .= '</select>';

                    return $select_view;
                }
            }

        }

        return false;

    }
}

function wc_paid_listings_has_user_package( $user_id ) {
    global $wpdb;

    $packages_count = intval($wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}wcpl_user_packages WHERE user_id = '".$user_id."' AND package_type IN ( 'job_listing' ) AND ( package_count < package_limit OR package_limit = 0 );"));

    return $packages_count;
}

add_action( 'init', 'add_post_status_to_job_listing', 12 );

function add_post_status_to_job_listing(){
    global $job_manager;

    register_post_status( 'pending_payment', array(
        'label'                     => __( 'Pending Payment' ),
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => false,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Pending Payment <span class="count">(%s)</span>', 'Pending Payment <span class="count">(%s)</span>' ),
    ) );

   // add_action( 'pending_payment_to_publish', array( $job_manager->post_types, 'set_expirey' ) );
}

add_filter( 'the_job_status',  'the_job_status_pending' , 10, 2 );

function the_job_status_pending( $status, $job ) {

    if ( $job->post_status == 'pending_payment' ) {
        $status = __( 'Pending Payment', 'wp-job-manager-simple-paid-listings' );
    }
    return $status;
}

add_filter( 'job_manager_valid_submit_job_statuses',  'valid_submit_job_statuses_pending' );

function valid_submit_job_statuses_pending( $status ) {

    $status[] = 'pending_payment';

    return $status;

}
add_filter( 'submit_job_steps',  'submit_job_steps_pending' , 10 );

function submit_job_steps_pending( $steps ) {

    $steps['preview']['handler'] =  'preview_handler_pending' ;

    return $steps;
}

function preview_handler_pending() {
    if ( ! $_POST ) {
        return;
    }

    $form = WP_Job_Manager_Form_Submit_Job::instance();

    if ( ! empty( $_POST['edit_job'] ) ) {
        $form->previous_step();
    }

    if ( ! empty( $_POST['continue'] ) ) {

        $job = get_post( $_POST['job_id']  );

        if ( $job->post_status == 'preview' ) {
            $update_job                  = array();
            $update_job['ID']            = $job->ID;
            $update_job['post_status']   = 'pending_payment';
            $update_job['post_date']     = current_time( 'mysql' );
            $update_job['post_date_gmt'] = current_time( 'mysql', 1 );
            $update_job['post_author']   = get_current_user_id();
            wp_update_post( $update_job );

            $job = get_post( $update_job['ID']  );

            $job_package = get_post_meta($job->ID, '_wcpl_jmfe_product_id',true);
            $product_terms = wp_get_object_terms( $job_package,  'product_cat' );

            $price = get_post_meta( $job_package, '_regular_price', true);

            if ( ! empty( $product_terms ) ) {
                if ( ! is_wp_error( $product_terms ) ) {
                    foreach( $product_terms as $term ) {
                        wp_set_object_terms( $job->ID, esc_html( $term->name ), 'job_listing_category' );
                    }
                }
            }

        }

        //$budget= intval(get_post_meta($job->ID, '_targeted_budget', true));

        $amount = round($price - ($price*0.3), 2);

        update_field('Budget_for_the_influencer',$amount,$job->ID);
        update_post_meta($job->ID, '_targeted_budget', $amount);
       // WC()->cart->empty_cart();

   /*     if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
            foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
                $_product = $values['data'];

                if ( $_product->id == DEPOSIT_ID )
                    $found = true;
            }
            // if product not found, add it
            if ( ! $found )
                WC()->cart->add_to_cart( DEPOSIT_ID, 1 );
        } else {
            // if no products in cart, add it
            WC()->cart->add_to_cart( DEPOSIT_ID, 1 );
        }

        wc_add_to_cart_message( DEPOSIT_ID );*/

        $is_user_package = false;


        if ( ! empty( $_POST['job_package'] ) ) {
            if ( is_numeric( $_POST['job_package'] ) ) {
                $is_user_package = false;
            } else {
                $is_user_package = true;
            }
        } elseif ( ! empty( $_COOKIE['chosen_package_id'] ) ) {
            $is_user_package = absint( $_COOKIE['chosen_package_is_user_package'] ) === 1;
        }
        $form->next_step();
        // Redirect to checkout page
       /* if ( $is_user_package ){
            wp_redirect( get_permalink( wc_get_page_id( 'checkout' ) ) );
            exit;

        } else{

        }*/

    }
}

add_filter('woocommerce_add_cart_item_data','wdm_add_item_data',1,10);

function wdm_add_item_data($cart_item_data, $product_id) {

    global $woocommerce;
    $new_value = array();
    $new_value['job_id'] = $_POST['job_id'];

    if(empty($cart_item_data)) {
        return $new_value;
    } else {
        return array_merge($cart_item_data, $new_value);
    }
}

add_filter('woocommerce_get_cart_item_from_session', 'wdm_get_cart_items_from_session', 1, 3 );
function wdm_get_cart_items_from_session($item,$values,$key) {

    if (array_key_exists( 'job_id', $values ) ) {
        $item['job_id'] = $values['job_id'];
    }

    return $item;
}

add_action('woocommerce_add_order_item_meta','wdm_add_values_to_order_item_meta',1,2);
function wdm_add_values_to_order_item_meta($item_id, $values) {
    global $woocommerce,$wpdb;
    $job = get_post( absint( $values['job_id'] ) );

    wc_add_order_item_meta( $item_id, __( 'Job Listing', 'wp-job-manager-wc-paid-listings' ), $job->post_title );
    wc_add_order_item_meta( $item_id, '_job_id', $values['job_id'] );

}

add_filter('woocommerce_cart_item_name','add_usr_custom_session',1,3);
function add_usr_custom_session($product_name, $values, $cart_item_key ) {

    $return_string = $product_name . "<br />" . $values['_job_id'];
    return $return_string;

}

//add_filter( 'submit_job_step_preview_submit_text',  'submit_button_text_pending', 10 );

function submit_button_text_pending( $button_text ) {
    $button_text = __( 'Pay deposit for listing &rarr;' );
    return $button_text;

}

add_action( 'woocommerce_order_status_completed', 'so_payment_complete' );

function so_payment_complete( $order_id ){

    $order = wc_get_order( $order_id );

    $order = new WC_Order( $order );

    $order_item = $order->get_items();

    foreach( $order_item as $item_id => $product ) {
        $job_id = wc_get_order_item_meta ($item_id, '_job_id');
        $job = get_post( absint( $job_id, 'ARRAY_A') );

        if ( $job ){
            $post = array();
            $post['ID'] = $job_id;
            $post['post_status'] = 'publish';
            wp_update_post($post);
        }
    }
}

add_action( 'job_manager_job_submitted_content_pending_payment', 'job_submitted_pending' , 10 );

function job_submitted_pending( $job ) {

    printf( __( 'Thanks. Your Job listing was submitted successfully and will be visible once payment is verified.' ), get_permalink( $job->ID ) );
}

add_filter( 'job_manager_get_dashboard_jobs_args',  'dashboard_job_args_pending'  );
add_filter( 'job_manager_my_job_actions',  'my_job_actions_pending' , 10, 2 );
add_action( 'job_manager_my_job_do_action',  'my_job_do_action_pending' , 10, 2 );

function dashboard_job_args_pending( $args = array() ) {
    $args['post_status'][] = 'pending_payment';

    return $args;
}

function my_job_actions_pending( $actions, $job ) {
    if ( $job->post_status == 'pending_payment' && get_option( 'job_manager_submit_page_slug' ) ) {
        $actions['pay'] = array( 'label' => __( 'Pay', 'job_manager' ), 'nonce' => true );
    }

    return $actions;
}

function my_job_do_action_pending( $action = '', $job_id = 0 ) {
    if ( $action == 'pay' && $job_id ) {
        wp_redirect( add_query_arg( array( 'step' => 'preview', 'job_id' => absint( $job_id ) ), get_permalink( get_page_by_path( get_option( 'job_manager_submit_page_slug' ) )->ID ) ) );
        exit;
    }
}
add_action( 'register_form_child', 'workscout_register_form_child' );

function workscout_register_form_child() {

    global $wp_roles;

    echo '<p class="form-row form-row-wide">';
    echo 'I’m  <span class="required"> *</span>';
    echo '</p>';
    echo '<p class="form-row ">';
    echo '<label class="col-3" for="candidate"><input type="radio" name="role" id="candidate" value="candidate" /> an Influencer </label>';
    echo '<label class="col-3" for="employer"><input type="radio" name="role" value="employer" /> a Brand </label>';
    echo '<label class="col-3" for="employer"><input type="radio" name="role" value="employer" /> an Agency </label>';
    echo '</p>';

    if ( ! empty( $_POST['role'] ) ) {
        update_user_meta( $user_id, 'role', trim( $_POST['role'] ) );
    }

}

add_filter( 'woocommerce_login_redirect', 'user_login_redirect_child', 10, 2 );

function user_login_redirect_child( $redirect, $user ) {

    $role = $user->roles[0];

    $dashboard = admin_url();

    $myaccount = get_permalink( wc_get_page_id( 'myaccount' ) );

    if( $role == 'employer' ) {

        if(get_option( 'job_manager_job_dashboard_page_id')) {
            $redirect = home_url().'/brandhome';
        } else {
            $redirect= home_url();
        };
    } elseif ( $role == 'candidate' ) {
        if(get_option( 'resume_manager_candidate_dashboard_page_id')) {
            $redirect = home_url().'/influencer-php';
        } else {
            $redirect= home_url();
        };
    } elseif ( $role == 'customer' || $role == 'subscriber' ) {
        //Redirect customers and subscribers to the "My Account" page
        $redirect = $myaccount;
    } else {
        //Redirect any other role to the previous visited page or, if not available, to the home
        $redirect = wp_get_referer() ? wp_get_referer() : home_url();
    }
    return $redirect;
}

function custom_redirect(){

    $nonce_value = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
    $nonce_value = isset( $_POST['woocommerce-register-nonce'] ) ? $_POST['woocommerce-register-nonce'] : $nonce_value;
    $response = array();
    $response['success'] = false;

    if ( ! empty( $_POST['register'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-register' ) ) {

        $username = 'no' === get_option( 'woocommerce_registration_generate_username' ) ? $_POST['username'] : '';
        $password = 'no' === get_option( 'woocommerce_registration_generate_password' ) ? $_POST['password'] : '';
        $email    = $_POST['email'];

        try {
            $validation_error = new WP_Error();
            $validation_error = apply_filters( 'woocommerce_process_registration_errors', $validation_error, $username, $password, $email );

            $error = array();

            if ( $validation_error->get_error_code() ) {
                $error[] = $validation_error->get_error_message();;
            }
            // Anti-spam trap
            if ( ! empty( $_POST['email_2'] ) ) {
                $error[] = __( 'Anti-spam field was filled in.', 'woocommerce' ) ;
            }

            if ( !isset($_POST['role']) || $_POST['role']=='' ){
                $error[] = __('Please, select who you are');
            }

            if ( count($error ) == 0){
                $new_customer = wc_create_new_customer( sanitize_email( $email ), wc_clean( $username ), $password );

                if ( is_wp_error( $new_customer ) ) {
                    $error[] =  $new_customer->get_error_message();
                }
                if ( apply_filters( 'woocommerce_registration_auth_new_customer', true, $new_customer ) ) {
                    wc_set_customer_auth_cookie( $new_customer );
                }

                if ( !is_wp_error( $new_customer ) ) {
                    $response['success'] = true;

                    $myaccount = get_permalink( wc_get_page_id( 'myaccount' ) );

                    $role = $_POST['role'];

                    if( $role == 'employer' ) {

                        $redirect= $myaccount.'/edit-account?success=1';

                    } elseif ( $role == 'candidate' ) {
                        $redirect= $myaccount.'/edit-account?success=1';
                    } elseif ( $role == 'customer' || $role == 'subscriber' ) {
                        $redirect = $myaccount;
                    } else {

                        $redirect = wp_get_referer() ? wp_get_referer() : home_url();
                    }

                    $response['redirect'] = $redirect;

                }
            }

        } catch ( Exception $e ) {
            $error[] = $e->getMessage();
        }
    }

    $response['error'] = $error;
    echo json_encode($response);
    wp_die();
}

remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'process_registration' ), 20 );

//add_action('wp_loaded', 'custom_redirect', 20);


add_action('wp_ajax_custom_redirect', 'custom_redirect');
add_action('wp_ajax_nopriv_custom_redirect', 'custom_redirect');

function create_custom_campaign(){
    $nonce_value = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
    $nonce_value = isset( $_POST['custom-campaign'] ) ? $_POST['custom-campaign'] : $nonce_value;
    $response = array();
    $response['success'] = false;
   // $admin_mail = "olha.novikova@gmail.com";
    $admin_mail = "admin@jrrny.com";

    if ( ! empty( $_POST['submit-campaign'] ) && wp_verify_nonce( $nonce_value, 'custom-campaign' ) ) {
        $error = array();
        $form_error = new WP_Error;

        if( empty( $_POST['name'] ) ){
            $form_error->add('no_name', 'First / Last Name can\'t be empty');
        }

        if( empty( $_POST['email'] ) ){
            $form_error->add('no_email', 'Email can\'t be empty');
        } elseif( ! is_email( $_POST['email'] ) ){
            $form_error->add('invalid_email', 'Invalid email');
        }

        if( empty( $_POST['phone'] ) ){
            $form_error->add('no_phone', 'Phone Number can\'t be empty');
        }elseif( !preg_match('/^\(?\+?([0-9]{1,4})\)?[-\. ]?(\d{3})[-\. ]?([0-9]{7})$/', trim($_POST['phone'])) ) {
            $form_error->add('invalid_phone','Please enter a valid phone number');
        }

        if( empty( $_POST['description'] ) ){
            $form_error->add('no_description', 'Description for the Campaign can\'t be empty');
        }

        if( empty( $_POST['budget'] ) ){
            $form_error->add('no_budget', 'Budget for the Campaign can\'t be empty');
        }elseif( !is_numeric( trim( $_POST['budget'] ) ) ) {
            $form_error->add('invalid_budget','Please enter a valid Budget ');
        }

        if ( $form_error->get_error_code() ) {

            foreach( $form_error->get_error_messages() as $error_message ){
                $error[]= $error_message;
            }

        }else{
            $user_mail      = sanitize_email($_POST['email']);
            $user_name      = sanitize_text_field($_POST['name']);
            $user_phone     = sanitize_text_field($_POST['phone']);
            $user_brand     = sanitize_text_field($_POST['brand']);
            $user_website   = $_POST['website'];
            $user_budget    = preg_replace('/[^0-9\.\-]+/','',strtolower($_POST['budget']));
            $user_description = sanitize_text_field($_POST['description']);

            $message = "Hi!\n ".
                "New Custom Campaign!\n".
                "Hi! I'm ".$user_name. " from ".$user_brand.". I'm interested in create new  Custom Campaign.\n".
                "Description for the Campaign:\n".
                $user_description."\n".
                "Budget for the Campaign is $".$user_budget."\n".
                "My contacts: \n".
                "Phone Number: ". $user_phone."\n".
                "Email address: ".$user_mail."\n";

            if ( isset($_POST['website'])&&$_POST['website']!='' )
                $message .= "Website URL: ".$user_website;

            @wp_mail($admin_mail, "New Custom Campaign", $message);
            $response['success'] = true;
        }

    }
    $response['error'] = $error;
    echo json_encode($response);
    wp_die();
}

add_action('wp_ajax_create_custom_campaign', 'create_custom_campaign');
add_action('wp_ajax_nopriv_create_custom_campaign', 'create_custom_campaign');

function send_on_review (){

    if ( ! empty( $_POST['wp_job_manager_review_application'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'edit_job_application' ) ) {

        global $wp_post_statuses;
        $response = array();
        $response['success'] = false;

        $application_id = absint( $_POST['application_id'] );

        $application = get_post( $application_id );

        if ( $application && $application->post_type == 'job_application') {
            $application_status = sanitize_text_field( $_POST['application_status'] );

            if ( array_key_exists( $application_status, $wp_post_statuses ) ) {
                wp_update_post( array(
                    'ID'          => $application_id,
                    'post_status' => $application_status
                ) );
            }

            $review_text = sanitize_text_field(trim($_POST['application-review-msg']));

            update_post_meta($application_id, '_review_msg', $review_text);
            $response['test'] = $review_text;
            $response['success'] = true;

        }

        echo json_encode($response);
        wp_die();

    }
}

add_action('wp_ajax_send_on_review', 'send_on_review');
add_action('wp_ajax_nopriv_send_on_review', 'send_on_review');


add_action( 'template_redirect',  'custom_save_account_details'  );

function custom_save_account_details() {

    if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
        return;
    }

    if ( empty( $_POST['action'] ) || 'save_account_details' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_account_details' ) ) {
        return;
    }

    $errors       = new WP_Error();
    $user         = new stdClass();

    $user->ID     = (int) get_current_user_id();
    $current_user = get_user_by( 'id', $user->ID );

    if ( $user->ID <= 0 ) {
        return;
    }

    $account_first_name = ! empty( $_POST['account_first_name'] ) ? wc_clean( $_POST['account_first_name'] ) : '';
    $account_last_name  = ! empty( $_POST['account_last_name'] ) ? wc_clean( $_POST['account_last_name'] ) : '';
    $account_email      = ! empty( $_POST['account_email'] ) ? wc_clean( $_POST['account_email'] ) : '';
    $pass_cur           = ! empty( $_POST['password_current'] ) ? $_POST['password_current'] : '';
    $pass1              = ! empty( $_POST['password_1'] ) ? $_POST['password_1'] : '';
    $pass2              = ! empty( $_POST['password_2'] ) ? $_POST['password_2'] : '';
    $save_pass          = true;

    $user->first_name   = $account_first_name;
    $user->last_name    = $account_last_name;


    // Prevent emails being displayed, or leave alone.
    $user->display_name = is_email( $current_user->display_name ) ? $user->first_name : $current_user->display_name;

    // Handle required fields
    $required_fields = apply_filters( 'woocommerce_save_account_details_required_fields', array(
        'account_first_name' => __( 'First name', 'woocommerce' ),
        'account_last_name'  => __( 'Last name', 'woocommerce' ),
        'account_email'      => __( 'Email address', 'woocommerce' ),
    ) );

    foreach ( $required_fields as $field_key => $field_name ) {
        if ( empty( $_POST[ $field_key ] ) ) {
            wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . esc_html( $field_name ) . '</strong>' ), 'error' );
        }
    }

    if ( $account_email ) {
        $account_email = sanitize_email( $account_email );
        if ( ! is_email( $account_email ) ) {
            wc_add_notice( __( 'Please provide a valid email address.', 'woocommerce' ), 'error' );
        } elseif ( email_exists( $account_email ) && $account_email !== $current_user->user_email ) {
            wc_add_notice( __( 'This email address is already registered.', 'woocommerce' ), 'error' );
        }
        $user->user_email = $account_email;
    }

    if ( ! empty( $pass_cur ) && empty( $pass1 ) && empty( $pass2 ) ) {
        wc_add_notice( __( 'Please fill out all password fields.', 'woocommerce' ), 'error' );
        $save_pass = false;
    } elseif ( ! empty( $pass1 ) && empty( $pass_cur ) ) {
        wc_add_notice( __( 'Please enter your current password.', 'woocommerce' ), 'error' );
        $save_pass = false;
    } elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
        wc_add_notice( __( 'Please re-enter your password.', 'woocommerce' ), 'error' );
        $save_pass = false;
    } elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
        wc_add_notice( __( 'New passwords do not match.', 'woocommerce' ), 'error' );
        $save_pass = false;
    } elseif ( ! empty( $pass1 ) && ! wp_check_password( $pass_cur, $current_user->user_pass, $current_user->ID ) ) {
        wc_add_notice( __( 'Your current password is incorrect.', 'woocommerce' ), 'error' );
        $save_pass = false;
    }

    if ( $pass1 && $save_pass ) {
        $user->user_pass = $pass1;
    }

    // Allow plugins to return their own errors.
    do_action_ref_array( 'woocommerce_save_account_details_errors', array( &$errors, &$user ) );

    if ( $errors->get_error_messages() ) {
        foreach ( $errors->get_error_messages() as $error ) {
            wc_add_notice( $error, 'error' );
        }
    }

    if ( wc_notice_count( 'error' ) === 0 ) {


        wc_add_notice( __( 'Account details changed successfully.', 'woocommerce' ) );
        do_action( 'woocommerce_save_account_details', $user->ID );

        $myaccount = wc_get_page_permalink( 'myaccount' ) ;

        wp_update_user( $user );

        if( $current_user->roles[0] == 'candidate'){

            $args = apply_filters( 'resume_manager_get_dashboard_resumes_args', array(
                'post_type'           => 'resume',
                'post_status'         => 'any',
                'ignore_sticky_posts' => 1,
                'posts_per_page'      => -1,
                'author'              => $user->ID
            ) );

            $resumes = new WP_Query( $args );

            if ( $resumes->have_posts() ){

                wp_safe_redirect($myaccount.'/edit-account');

            }else{

                wp_safe_redirect($myaccount.'/edit-account/?success=2');
            }
            exit;
        }
        if( $current_user->roles[0] == 'employer' ) {

            wp_safe_redirect($myaccount.'/edit-account?success=2');
            exit;
            /*if(get_option( 'job_manager_job_dashboard_page_id')) {
                wp_safe_redirect($myaccount.'/edit-account?success=2');
              //  wp_safe_redirect( home_url().'/brandhome');
            } else {
                wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
            };*/
        }/* elseif ( $current_user->roles[0] == 'candidate' ) {
            if(get_option( 'resume_manager_candidate_dashboard_page_id')) {
                wp_safe_redirect($myaccount.'/edit-account?success=1');
              //  wp_safe_redirect( home_url().'/influencer-php' );
            } else {
                wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
            };
        }*/ else {
            wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
            exit;
        }
    }
}


function aj_sync_social(){

    $user_id = get_current_user_id();

    if ( !$user_id )  wp_send_json_error( array('error'=> 'User not found') );

    $insta_link     = get_user_meta( $user_id, 'insta', true);
    $fb_link        = get_user_meta( $user_id, 'fb', true);
    $twitter_link   = get_user_meta( $user_id, 'twitter', true);
    $youtube_link   = get_user_meta( $user_id, 'youtube', true);
    $jrrny_link     = get_user_meta( $user_id, '_jrrny_link', true);

    $website        = get_user_meta( $user_id, 'website', true );
    $monthly_visitors = get_user_meta( $user_id, 'monthlyvisit', true);
    $influencer_number = get_user_meta( $user_id, 'phone_number', true);
    $influencer_location = get_user_meta( $user_id, 'location', true);
    $influencer_bio = get_user_meta( $user_id, 'shortbio', true);
    $logo           = get_user_meta( $user_id, 'photo', true );

    $response = array();
    if ( $insta_link )
        $response['insta_link'] = $insta_link;

    if ( $fb_link )
        $response['fb_link'] = $fb_link;

    if ( $twitter_link )
        $response['twitter_link'] = $twitter_link;

    if ( $youtube_link )
        $response['youtube_link'] = $youtube_link;

    if ( $jrrny_link )
        $response['jrrny_link'] = $jrrny_link;

    if ( $website )
        $response['influencer_website'] = $website;

    if ( $monthly_visitors )
        $response['estimated_monthly_visitors'] = $monthly_visitors;

    if ( $influencer_number )
        $response['influencer_number'] = $influencer_number;

    if ( $influencer_location )
        $response['influencer_location'] = $influencer_location;

    if ( $influencer_bio )
        $response['short_influencer_bio'] = $influencer_bio;

    if ( $logo ){
        $dir = wp_get_upload_dir();
        $response['candidate_photo'] = $dir['baseurl'].'/users/'.$logo;
    }

    wp_send_json_success( $response );

}

add_action('wp_ajax_aj_sync_social', 'aj_sync_social');
add_action('wp_ajax_nopriv_aj_sync_social', 'aj_sync_social');


require_once 'socilal-apis.php';

add_action('init', 'paypal_request_payment');

function paypal_request_payment(){

    if(isset($_POST['action']) && $_POST['action'] == 'send_payment_request' && wp_verify_nonce($_POST['r_nonce'], 'r-nonce')) {

        $paypal_email = $_POST['payout_destination'];
        $paypal_amount = $_POST['amount'];
        $success = 1;


        if ( !preg_match('/\$?((\d{1,4}(,\d{1,3})*)|(\d+))(\.\d{2})?\$?/', $paypal_amount)) {

            $_SESSION['error'] = 'Please verify amount';
            $success = 0;

        }

        $paypal_amount = preg_replace('/\$/', '', $paypal_amount);


        if ( !is_numeric($paypal_amount) ){
            $_SESSION['error'] = 'Please verify amount';
            $success = 0;
        }

        $user = wp_get_current_user();
        $currency = get_woocommerce_currency_symbol();

        if ( in_array( 'candidate', (array) $user->roles ) || in_array( 'administrator', (array) $user->roles ) ) {

            $available_cash = get_candidate_cash_out_sum($user->ID);

        } else {
            $_SESSION['error'] .= 'You don\'t have permission for this operation';
            $success = 0;
        }


        if ( $available_cash < $paypal_amount) {
            $_SESSION['error'] .= 'Available amount is less then Requested amount';
            $success = 0;
        }

        if ( $success  ){
            $message = "Hi,\n
	        Your have new cash request from ".$user->first_name." ".$user->last_name.".\n
            Available amount is ".$currency.$available_cash.".\n
            Requested amount is ".$currency.$paypal_amount.".\n
            PayPal email is ".$paypal_email.".\n
            Date of request is ".date('F j, Y, g:i a')."\n
	        ";

            $headers = "Content-Type: text/html; charset=UTF-8\n";
            @wp_mail('admin@jrrny.com', "New Cash Request", $message);
           // @wp_mail('olha.novikova@gmail.com', "New Cash Request", $message, $headers);

            $_SESSION['success'] = "OK";
        }

    }
}
?>
