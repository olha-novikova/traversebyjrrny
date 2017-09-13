<?php
switch ( $resume->post_status ) :
	case 'publish' :
		if ( resume_manager_user_can_view_resume( $resume->ID ) ) {
			printf( '<p class="resume-submitted">' . __( 'Your portfolio has been submitted successfully. To view your portfolio <a href="%s">click here</a>.', 'wp-job-manager-resumes' ) . '</p>', get_permalink( $resume->ID ) );
		} else {
			print( '<p class="resume-submitted">' . __( 'Your portfolio has been submitted successfully.', 'wp-job-manager-resumes' ) . '</p>' );
		}
	break;
	case 'pending' :
		print( '<p class="resume-submitted">' . __( 'Your account has been submitted for approval by JRRNY admin, you will receive a welcome email and confirmation within 24 - 48 hours.', 'wp-job-manager-resumes' ) . '</p>' );
	break;
	default :
		do_action( 'resume_manager_resume_submitted_content_' . str_replace( '-', '_', sanitize_title( $resume->post_status ) ), $resume );
	break;
endswitch;
