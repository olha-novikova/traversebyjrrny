<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $job_manager;

get_header(); ?>
<div class="container">

    <div class="sixteen columns">
        <?php
        do_action( 'woocommerce_before_account_navigation' );
        ?>
        <div class="woocommerce-account">
            <div class="woocommerce">
                <nav class="woocommerce-MyAccount-navigation">
                    <ul>
                        <?php
                        function the_slug($id) {
                            $post_data = get_post($id, ARRAY_A);
                            $slug = $post_data['post_name'];
                            return $slug;
                        }
                        global $wp;
                        $page = $wp->query_vars[ 'pagename' ];

                        $user = wp_get_current_user();

                        if ( in_array( 'candidate', (array) $user->roles ) || in_array( 'administrator', (array) $user->roles ) ) :
                            $candidate_dashboard_page_id = get_option( 'resume_manager_candidate_dashboard_page_id' );

                            $page = the_slug($candidate_dashboard_page_id);
                            $class = ( $wp->query_vars[ 'pagename' ]== $page )?'is-active':'';

                            printf( __( '<li class="woocommerce-MyAccount-navigation-link %s"><a href="%s"> My Portfolios </a></li>', 'workscout' ),
                                $class,
                                get_permalink($candidate_dashboard_page_id)

                            );
                        endif;

                        if ( in_array( 'employer', (array) $user->roles ) || in_array( 'administrator', (array) $user->roles ) ) :
                            $employer_dashboard_page_id = get_option( 'job_manager_job_dashboard_page_id' );

                            $page = the_slug($employer_dashboard_page_id);
                            $class = ( $wp->query_vars[ 'pagename' ]== $page )?'is-active':'';
                            printf( __( '<li class="woocommerce-MyAccount-navigation-link %s"><a href="%s"> My Listings </a></li>', 'workscout' ),
                                $class,
                                get_permalink($employer_dashboard_page_id)
                            );
                        endif;
                        if ( in_array( 'candidate', (array) $user->roles ) || in_array( 'administrator', (array) $user->roles ) ) :

                            $pagename = 'my-pitches';
                            $class = ( $wp->query_vars[ 'pagename' ]== $pagename )?'is-active':'';


                            printf( __( '<li class="woocommerce-MyAccount-navigation-link %s"><a href="%s"> My Pitches </a></li>', 'workscout' ),
                                $class,
                                home_url('/my-pitches')
                            );
                        endif;
                        /*bookmarks*/
                        /*  $bookmarks_page_id = ot_get_option('pp_bookmarks_page');
                          $pagename = the_slug($bookmarks_page_id);
                          $class = ( $wp->query_vars[ 'pagename' ]== $pagename )?'is-active':'';
                          if ( (in_array( 'candidate', (array) $user->roles ) || in_array( 'administrator', (array) $user->roles )) && !empty($bookmarks_page_id) ) :
                              printf( __( '<li class="woocommerce-MyAccount-navigation-link %s"><a href="%s"> My Bookmarks </a></li>', 'workscout' ),
                                  $class,
                                  get_permalink($bookmarks_page_id)
                              );
                          endif;*/

                        ?>
                        <?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
                            <?php if($label !='Orders' && $label !='Downloads' && $label !='Addresses' && $label != 'Dashboard'){?>
                                <?php

                                if ( $label =='Logout' ){

                                    if ( in_array( 'candidate', (array) $user->roles ) ) :
                                        $pagename = 'my-balance';
                                        $class = ( $wp->query_vars[ 'pagename' ]== $pagename )?'is-active':'';

                                        printf( __( '<li class="woocommerce-MyAccount-navigation-link %s"><a href="%s"> Account Balance</a></li>', 'workscout' ),
                                            $class,
                                            home_url('/my-balance')
                                        );
                                    endif;
                                    if ( in_array( 'employer', (array) $user->roles )  ) :
                                        $pagename = 'my-balance';
                                        $class = ( $wp->query_vars[ 'pagename' ]== $pagename )?'is-active':'';

                                        printf( __( '<li class="woocommerce-MyAccount-navigation-link %s"><a href="%s"> Payment History</a></li>', 'workscout' ),
                                            $class,
                                            home_url('/my-balance')
                                        );
                                    endif;
                                    if (  in_array( 'administrator', (array) $user->roles ) ) :
                                        $pagename = 'my-balance';
                                        $class = ( $wp->query_vars[ 'pagename' ]== $pagename )?'is-active':'';

                                        printf( __( '<li class="woocommerce-MyAccount-navigation-link %s"><a href="%s"> Balance/Payment</a></li>', 'workscout' ),
                                            $class,
                                            home_url('/my-balance')
                                        );
                                    endif;
                                }
                                ?>
                                <li class="woocommerce-MyAccount-navigation-link">
                                    <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( ucwords($label) ); ?></a>
                                </li>
                                <?php
                                if ( $endpoint == 'edit-account') { ?>
                                    <li class="woocommerce-MyAccount-navigation-link">
                                        <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ).'?password=change'; ?>"><?php echo esc_html( ucwords('Change Password') ); ?></a>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                        <?php endforeach;?>
                    </ul>
                </nav>

                <?php do_action( 'woocommerce_after_account_navigation' ); ?>
                <div class="woocommerce-MyAccount-content">
                    <h2 class="my-acc-h2">Prolong This Job Listing</h2>
                    <p>This listing doesn't have any pitches yet, so you can </p>
                    <div class="prolong-page">
                        <form action="<?php echo esc_url( $action ); ?>" method="post" id="prolong-job-form" class="job-manager-form">
                            <?php if ( job_manager_user_can_post_job() ) : ?>

                                <?php $user_id = get_current_user_id(); ?>

                                <p class="send-btn-border">
                                    <input type="hidden" name="job_manager_form" value="<?php echo esc_attr($form); ?>" />
                                    <input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
                                    <input type="submit" name="prolong_job" class="button big" value="<?php echo esc_attr( $submit_button_text ); ?>" />
                                </p>

                            <?php else : ?>

                                <?php do_action( 'submit_job_form_disabled' ); ?>

                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>