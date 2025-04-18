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
            'LOGIN' => '',
            'NAME' => '',
            'LAST_NAME' => '',
            'EMAIL' => '',
        ];

        $userData = [];
        foreach ($syncAttributes as $modelAttribute => $keycloakField) {
            if (array_key_exists($keycloakField, $credentials)) {
                $userData[$modelAttribute] = $credentials[$keycloakField] !== '' ? $credentials[$keycloakField] : null;
            }
        }

        $dbUsers = CUser::GetList('', '', ['LOGIN' => $userData['LOGIN']]);
        $user = $dbUsers->Fetch();

        unset($dbUsers);

        if (empty($user) && COption::GetOptionString("keycloak", "add_user_when_auth", "N") === "Y") {
            $user = new CUser();
            return ['ID' => $user->Add($userData)];
        } elseif (!empty($user) && COption::GetOptionString("keycloak", "update_user_when_auth", "Y") === "Y") {
            (new CUser())->Update($user['ID'], $userData);
        }

//        $user = $this->eloquent->retrieveByCredentials([
//            'keycloak_id' => $userData[config('keycloak-web.keycloak_id_column')]
//        ]);

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