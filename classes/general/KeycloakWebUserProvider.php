<?php

class KeycloakWebUserProvider
{
    /**
     * Retrieve a user by the given credentials.
     *
     * @param array $credentials
     */
    public function retrieveByCredentials(array $credentials)
    {
        //$syncAttributes = config('keycloak-web.sync_attributes');

        $syncAttributes = [
            'LOGIN' => 'username',
            'NAME' => '',
            'LAST_NAME' => '',
            'EMAIL' => 'email',
        ];

        $userData = [];
        foreach ($syncAttributes as $modelAttribute => $keycloakField) {
            if (array_key_exists($keycloakField, $credentials)) {
                $userData[$modelAttribute] = $credentials[$keycloakField] !== '' ? $credentials[$keycloakField] : null;
            }
        }

        $user = CUser::GetByLogin($userData['LOGIN'])->Fetch();

        var_dump($credentials, $user);

        if (empty($user) && COption::GetOptionString("keycloak", "add_user_when_auth", "N") === "Y") {
            $user = new CUser();
            return ['ID' => $user->Add($userData)];
        } elseif (!empty($user) && COption::GetOptionString("keycloak", "update_user_when_auth", "N") === "Y") {
            (new CUser())->Update($user['ID'], $userData);
        }

        return $user;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param mixed $identifier
     */
    public function retrieveById($identifier)
    {
        return CUser::GetList('', '', ['ID' => $identifier])->Fetch();
    }
}