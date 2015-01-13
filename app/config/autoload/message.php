<?php
/**
 * Local Configuration Override
 *
 * This configuration override file is for overriding environment-specific and
 * security-sensitive configuration information. Copy this file without the
 * .dist extension at the end and populate values as needed.
 *
 * @NOTE: This file is ignored from Git by default with the .gitignore included
 * in ZendSkeletonApplication. This is a good practice, as it prevents sensitive
 * credentials from accidentally being committed into version control.
 */

return array(
    'message' => array(
    
        'logout' => 'You are now logged out.',
        'loggedin' => 'You are now logged in.',
        'login_failed' => 'Username/Password is not correct.',
        'email_sent_failed' => 'Failed to sent email. Please try again.',
        'email_sent_successfully' => 'Email sent successfully.',
        'reset_password_failed' => 'Failed to reset password. Please try again.',
        'email_address_not_exist' => 'Email address is not exist',
        'saved_successfully' => 'Saved successfully',
        'deleted_successfully' => 'Deleted successfully',
        'invalid_request' => 'Invalid request',
        'failed_save_data' => 'Failed to save data. Please try again.',
        'failed_delete_data' => 'Failed to delete data. Please try again',
        'successfully_active_account' => 'Your account is active now.',
        'failed_to_active_account' => 'Failed to active your account. Please try again.',
        'invalid_code' => 'Invalid activation code',
        'facebook_login_failed' => 'You are not linked your facebook account to this website.',
        'account_confirmation' => 'Account confirmation is required. Please, check your email for the confirmation link.',
        'step_not_complete' => 'Please give data in first step input field',
        'subscription_successfully' => 'Membership subscription completed successfully',
        'passport_image_upload' => 'Please upload your passport or ID card. <br />',
        'mobile_invoice_upload' => 'Please upload your last mobile invoice. <br />',
        'tax_document_upload' => 'Please upload your business registration. <br />',
        'image_invalid_msg' => 'Valid extension(jpeg, jpg, gif, png).Maximum file Size:1MB',
        'video_invalid_msg' => 'Valid extension(MP4).Maximum file Size:1MB',
        'already_commented' => 'You have already commented for this item.',
        'not_loggedin' => 'You are not logged in into current system.',
        'renew_completed' => 'Renew completed successfully. Please complete your payment for enable this product.'
    ),
);
