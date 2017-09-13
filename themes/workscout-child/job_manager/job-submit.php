<?php
/**
 * Job Submission Form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $job_manager;

?>
<style>
    .form.fieldset-application{
        display: none;
    }
</style>
<div class="submit-page">
<form action="<?php echo esc_url( $action ); ?>" method="post" id="submit-job-form" class="job-manager-form" enctype="multipart/form-data">

	<?php if ( apply_filters( 'submit_job_form_show_signin', true ) ) : ?>

		<?php get_job_manager_template( 'account-signin.php' ); ?>

	<?php endif; ?>

	<?php if ( job_manager_user_can_post_job() ) : ?>

        <?php $user_id = get_current_user_id(); $company_name = get_user_meta( $user_id, 'company_name', true );?>
		<!-- Job Information Fields -->
		<?php do_action( 'submit_job_form_job_fields_start' ); ?>
		
		<?php global   $redux_demo; ?>

        <?php if( $redux_demo['header_image']['url']){ ?>
        <img src="<?php echo  $redux_demo['header_image']['url']; ?>" class="image-responsive" style="width:100%; float:left; height:400px" />
        <?php } ?>
        <?php
        global $wp_query;
        $current_package = $wp_query->get_queried_object();
        $jobs_category = get_term_by('name', ucfirst(strtolower($current_package->name)), 'job_listing_category');
        ?>

		<?php foreach ( $job_fields as $key => $field ) : ?>
			<fieldset class="form fieldset-<?php echo esc_attr( $key ); ?>">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo $field['label'] . apply_filters( 'submit_job_form_required_label', $field['required'] ? '' : ' <small>' . esc_html__( '(optional)', 'workscout' ) . '</small>', $field ); ?></label>
				<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
					<?php get_job_manager_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field ) ); ?>
				</div>
			</fieldset>
		<?php endforeach; ?>

        <input type="hidden"  id = "job_category" name="job_category" value="<?php echo $jobs_category->term_id; ?>" />
        <input type="hidden"  id = "job_category_name" name="job_category_name" value="<?php echo ucfirst(strtolower($current_package->name)); ?>" />
        <input type="hidden"  id = "job_company_name" name="job_company_name" value="<?php echo $company_name; ?>" />
		<?php do_action( 'submit_job_form_job_fields_end' ); ?>

<!--		--><?php //if ( $company_fields ) : ?>
<!--			<div class="divider"><h3>--><?php //esc_html_e( 'Company Details', 'workscout' ); ?><!--</h3></div>-->
<!---->
<!--			--><?php //do_action( 'submit_job_form_company_fields_start' ); ?>
<!---->
<!--			--><?php //foreach ( $company_fields as $key => $field ) : ?>
<!--				<fieldset class="form fieldset---><?php //echo esc_attr( $key ); ?><!--">-->
<!--					<label for="--><?php //echo esc_attr( $key ); ?><!--">--><?php //echo $field['label'] . apply_filters( 'submit_job_form_required_label', $field['required'] ? '' : ' <small>' . esc_html__( '(optional)', 'workscout' ) . '</small>', $field ); ?><!--</label>-->
<!--					<div class="field --><?php //echo $field['required'] ? 'required-field' : ''; ?><!--">-->
<!--						--><?php //get_job_manager_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field ) ); ?>
<!--					</div>-->
<!--				</fieldset>-->
<!--			--><?php //endforeach; ?>
<!---->
<!--			--><?php //do_action( 'submit_job_form_company_fields_end' ); ?>
<!--		--><?php //endif; ?>

		<p class="send-btn-border">

			<input type="hidden" name="job_manager_form" value="<?php echo esc_attr($form); ?>" />
			<input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>" />
			<input type="submit" name="submit_job" class="button big" value="<?php echo esc_attr( $submit_button_text ); ?>" />
		</p>

	<?php else : ?>

		<?php do_action( 'submit_job_form_disabled' ); ?>

	<?php endif; ?>
</form>
</div>
