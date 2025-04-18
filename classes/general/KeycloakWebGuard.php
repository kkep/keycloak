<?php

class KeycloakWebGuard
{
    protected static $instance = null;

    /**
     * @var CUser
     */
    protected $user;

    public function __construct(&$user)
    {
        $this->user = $user;
    }

    /**
     * @return static
     */
    public static function instance()
    {
        if (static::$instance === null) {
            global $USER;
            static::$instance = new KeycloakWebGuard($USER);
        }

        return static::$instance;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return $this->user->IsAuthorized();
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }

    /**
     * Try to authenticate the user
     *
     * @return boolean
     */
    public function authenticate()
    {
        // Get Credentials
        $credentials = KeycloakWeb::retrieveToken();
        if (empty($credentials)) {
            return false;
        }

        $user = KeycloakWeb::getUserProfile($credentials);
        if (empty($user)) {
            KeycloakWeb::forgetToken();

            return false;
        }

        $this->user->Authorize($user["ID"], true);

        return true;
    }


}