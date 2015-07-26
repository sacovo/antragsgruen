<?php

namespace app\models\policies;

class All extends IPolicy
{
    /**
     * @static
     * @return int
     */
    public static function getPolicyID()
    {
        return 1;
    }

    /**
     * @static
     * @return string
     */
    public static function getPolicyName()
    {
        return 'Alle';
    }

    /**
     * @return string
     */
    public function getOnCreateDescription()
    {
        return 'Alle';
    }

    /**
     * @return string
     */
    public function getPermissionDeniedMotionMsg()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getPermissionDeniedAmendmentMsg()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getPermissionDeniedSupportMsg()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getPermissionDeniedCommentMsg()
    {
        return '';
    }

    /**
     * @param bool $allowAdmins
     * @param bool $assumeLoggedIn
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkCurrUser($allowAdmins = true, $assumeLoggedIn = false)
    {
        return true;
    }
}
