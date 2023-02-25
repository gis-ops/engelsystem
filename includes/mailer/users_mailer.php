<?php

use Engelsystem\Models\User\User;

/**
 * @param User $user
 * @return bool
 */
function mail_user_delete($user)
{
    return engelsystem_email_to_user(
        $user,
        __('Your account has been deleted'),
        __(
            'Your %s account has been deleted. If you have any questions regarding your account deletion, please contact heaven.',
            [config('app_name')]
        )
    );
}

// MOD(nils): send email to recipient
/**
 * Sends an email on PM's to the recipient.
 *
 * @param User $userto
 * @param User $userfrom
 * @param string $message
 * @return bool
 */
function mail_user_pm($userto, $userfrom, $message)
{
    error_log(''.$userfrom->name.' sends message to '.$userto->email.'.', 0);
    $email = $userto->contact->email ? $userto->contact->email : $userto->email;
    if (!$email) {
        return false;
    }
    return engelsystem_email_to_user(
        $userto,
        __('New message from '.$userfrom->name.'.'),
        __(
            '

'.$userfrom->name.' has sent you a direct message:

'.$message.'

(replies only via %s, not via email)
--
',
            [config('app_name')]
        )
    );
}
// END MOD(nils)
