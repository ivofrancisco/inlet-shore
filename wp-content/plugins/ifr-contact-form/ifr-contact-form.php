<?php

/*
Plugin Name: IFrancisTech Contact Form
Description: A custom contact form that sends an email to the Webiste's Admin.
Version: 1.0
Author: IFrancisTech
Author URI: https://ifrancis.tech
*/

/**
 * Generates a shortcode for a custom contact form.
 *
 * @return string The shortcode for the custom contact form.
 */
function ifr_contact_form_shortcode()
{
    ob_start();
?>

    <?php if (isset($_GET['contact_success'])) : ?>
        <!-- Begin: Contact form sent successfully -->
        <div class="alert alert-success" role="alert" style="display: flex; align-items: center; justify-content: center; width: 40%; padding-top: 9px; margin: 0 auto 20px auto; text-align: center;">
            <h6 style="color: #59ce28; font-weight: 400">Your message was sent successfully.</h6>
        </div>
        <!-- End: Contact form sent successfully -->
    <?php endif; ?>

    <!-- BEGIN: CONTACT FORM -->
    <form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" id="s-contact-form" method="POST">

        <input type="hidden" name="action" value="ifr_contact_form">

        <!-- Begin: form-row -->
        <div class="form-row mb-3">
            <!-- Begin: form-group col-md-6 -->
            <div class="form-group col-md-6">
                <label for="fname">Primeiro Nome</label><span class="required">*</span>
                <input type="text" name="fname" class="form-control brd lg" id="fname" />
                <?php if (isset($_GET['errors']['missing_first_name'])) { ?>
                    <small class="form-text text-danger">First name is required.</small>
                <?php } ?>
            </div>
            <!-- End: form-group col-md-6 -->
            <!-- Begin: form-group col-md-6 -->
            <div class="form-group col-md-6">
                <label for="lname">Último Nome</label><span class="required">*</span>
                <input type="text" name="lname" class="form-control" id="lname" />
                <?php if (isset($_GET['errors']['missing_last_name'])) { ?>
                    <small class="form-text text-danger">Last name is required.</small>
                <?php } ?>
            </div>
            <!-- End: form-group col-md-6 -->
        </div>
        <!-- End: form-row -->

        <!-- Begin: form-row -->
        <div class="form-row mb-3">
            <!-- Begin: form-group col-md-6 -->
            <div class="form-group col-md-6">
                <label for="email">E-Mail</label><span class="required">*</span>
                <input type="text" name="email" class="form-control brd lg" id="email" required />
                <?php if (isset($_GET['errors']['missing_email'])) { ?>
                    <small class="form-text text-danger">Email is required.</small>
                <?php } ?>
            </div>
            <!-- End: form-group col-md-6 -->
            <!-- Begin: form-group col-md-6 -->
            <div class="form-group col-md-6">
                <label for="phone">Telefone</label><span class="required" style="color: transparent">*</span>
                <input type="text" name="phone" class="form-control" id="phone" />
            </div>
            <!-- End: form-group col-md-6 -->
        </div>
        <!-- End: form-row -->

        <!-- Begin: form-group -->
        <div class="form-group">
            <label for="message">Mensagem</label><span class="required">*</span>
            <textarea name="message" class="form-control textarea brd md" id="message" rows="3"></textarea>
            <?php if (isset($_GET['errors']['missing_message'])) { ?>
                <small class="form-text text-danger">Message is required.</small>
            <?php } ?>
        </div>
        <!-- End: form-group -->
        <button type="submit" name="submit" class="btn btn-secondary rnd bbl shadow-sm">Enviar Mensagem</button>
    </form>
    <!-- END: CONTACT FORM -->

<?php
    // Plugin styles
    wp_enqueue_style('ifr-contact-form-style', get_template_directory_uri() . '/build/style-index.css');
    return ob_get_clean();
}
add_shortcode('ifr_contact_form', 'ifr_contact_form_shortcode');

function ifr_contact_form_submit()
{
    // Input fields
    $first_name = sanitize_text_field($_POST['fname']);
    $last_name = sanitize_text_field($_POST['lname']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $message = esc_textarea($_POST['message']);

    // Email structure
    $to = "ivof.dev@gmail.com";
    $subject = "Pedido de Informações";
    $body = "Name: $first_name . $last_name\nEmail: $email\nTelefone: $phone\n$message";

    // Store list of errors
    $errors = array();

    // Check if first name is missing
    if (empty($first_name)) {
        $errors['missing_first_name'] = 'First name is required.';
    }

    // Check if last name is missing
    if (empty($last_name)) {
        $errors['missing_last_name'] = 'Last name is required.';
    }

    // Check if email is missing
    if (empty($email)) {
        $errors['missing_email'] = 'Email is required.';
    }

    // Check if message is missing
    if (empty($message)) {
        $errors['missing_message'] = 'Message is required.';
    }

    // Send email
    if (!empty($errors)) {
        // If not sent
        $url = add_query_arg(array('errors' => $errors), site_url('/#p-contact'));
        wp_redirect($url);
        exit;
    } else {
        // If sent successfully
        if (wp_mail($to, $subject, $body)) {
            // Redirect to the home page with success message
            $url = add_query_arg(array('contact_success' => 'Your message was sent successfully'), site_url('/#p-contact'));
            wp_redirect($url);
            exit;
        }
    }
}
add_action('wp_ajax_ifr_contact_form', 'ifr_contact_form_submit');
add_action('wp_ajax_nopriv_ifr_contact_form', 'ifr_contact_form_submit');
