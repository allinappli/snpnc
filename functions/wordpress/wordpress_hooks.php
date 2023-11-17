<?php
add_action('wpcf7_before_send_mail',function($contact_form, &$abort, $submission){
    $wpcf7 = WPCF7_ContactForm::get_current();
    
    // ID formulaire
    $idFormulaire = 1475;
    if($wpcf7->id == $idFormulaire) {
        $post_id = sanitize_text_field($_POST['_wpcf7']);
        $cf7fru = get_post_meta($post_id, "_cf7fru_", true);
        $cf7fre = get_post_meta($post_id, "_cf7fre_", true);
        //$cf7frr = get_post_meta($post_id, "_cf7frr_", true);
    
        $enable = get_post_meta($post_id,'_cf7fr_enable_registration');
        if($enable[0]!=0)
        {
            if (!isset($cfdata->posted_data) && class_exists('WPCF7_Submission')) {
                if ($submission) {
                    $formdata = $submission->get_posted_data();
                
                    $compagnie = $submission->get_posted_data('compagnie');
                    $sanitize_nom = sanitize_title($submission->get_posted_data('nom'));
                    $sanitize_prenom = sanitize_title($submission->get_posted_data('prenom'));
                }
            } elseif (isset($cfdata->posted_data)) {
                $formdata = $cfdata->posted_data;
            }
            $password = wp_generate_password( 20, false );
            $email = $formdata["".$cf7fre.""];
            $name = $formdata["".$cf7fru.""];
            // Construct a username from the user's name
            $username = strtolower(str_replace(' ', '', $name));
            $name_parts = explode(' ',$name);
            if ( !email_exists( $email ) )
            {
                // Find an unused username
                $username_tocheck = $username;
                $i = 1;
                while ( username_exists( $username_tocheck ) ) {
                    $username_tocheck = $username . $i++;
                }
                $username = $username_tocheck;
                // Create the user
                $userdata = array(
                  'user_login' => $sanitize_prenom . '.' . $sanitize_nom,
                  'user_pass' => $password,
                  'user_email' => $email,
                  'nickname' => reset($name_parts),
                  'display_name' => $name,
                  'first_name' => $submission->get_posted_data('prenom'),
                  'last_name' => $submission->get_posted_data('nom'),
                  'role' => 'subscriber'
                );
            
                // Use WP’s built-in email new user notification
                add_action( 'user_register', function( $user_id ) {
                    wp_new_user_notification( $user_id, NULL, 'user' );
                } );
            
                $user_id = wp_insert_user( $userdata );
                $user = get_user_by( 'ID', $user_id );
    
                // mapping ACF
                mappingAcfFields($submission, $user);
                
                if ( $user ) {
                    $user->add_role( sanitize_title($compagnie[0]) );
                }
            }
        
        }
    }
},10,3);