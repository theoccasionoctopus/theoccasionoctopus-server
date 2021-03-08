<?php

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class AccessDeniedRedirectToPublicURLIfPossibleException
 * @package App\Exception
 * This is used by manage account controllers. If the current user does not have access to the manage page, this error is thrown.
 * The controller will either:
 *  - catch it and redirect to a suitable public page
 *  - ignore it and Symfony will tell the user to go away
 */
class AccessDeniedRedirectToPublicURLIfPossibleException extends AccessDeniedException
{
}
