<?php

namespace App\Enum;

class KeyCloakConfig
{
    const Key_Cloak_Base_Url = 'http://localhost:8080';
    const Key_Cloak_Grant_Type = 'password';
    const Key_Cloak_Rest_Client = 'rest-client';
    const Key_Cloak_Client_Secret = '8cc68536-52a8-4cc1-a3fc-8448bed05b2d';
    const Key_Cloak_AuthToken_Url = "/auth/realms/master/protocol/openid-connect/token";
}
