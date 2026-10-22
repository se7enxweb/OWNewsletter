<?php

$ini = eZINI::instance( 'newsletter.ini' );
$ConfirmationTimeOut = OWNewsletterUtils::parseDurationSeconds( $ini->variable( 'NewsletterSettings', 'ConfirmationTimeOut' ) );

if( $ConfirmationTimeOut === false || $ConfirmationTimeOut <= 0 ) {
    $message = 'OWNewsletter: [NewsletterSettings] ConfirmationTimeOut must be a positive number of seconds or a product such as 6*24*60*60; no pending user was removed.';
    eZDebug::writeError( $message, 'newsletter_users_clean_pending' );
    if( isset( $cli ) && is_object( $cli ) ) {
        $cli->error( $message );
    }
    return;
}

$pendingUserList = OWNewsletterUser::fetchList( array( 'status' => OwNewsletterUser::STATUS_PENDING ) );
foreach( $pendingUserList as $pendingUser ) {
    if( ($pendingUser->Created + $ConfirmationTimeOut) < time() ) {
        $pendingUser->remove();
    }
}
