<?php
/**
 * Created by PhpStorm.
 * User: nilskoppelmann
 * Date: 05.09.18
 * Time: 01:06
 */

require_once __DIR__ . '/../config.php';

class Kas
{

    private $kas_user;
    private $kas_pass;
    private $session_lifetime           = 600;
    private $session_update_lifetime    = 'Y';

    private $kas_auth_endpoint;
    private $kas_api_endpoint;

    private $credentialToken;
    private $soapLogon;

    private $users;

    public function __construct()
    {

        global $config;

        $this->kas_user = $config['kas']['user'];
        $this->kas_pass = $config['kas']['pass'];

        $this->kas_auth_endpoint = $config['kas']['auth_endpoint'];
        $this->kas_api_endpoint = $config['kas']['api_endpoint'];

        try {
            $this->soapLogon = new SoapClient($this->kas_auth_endpoint);
            $this->credentialToken = $this->soapLogon->KasAuth(json_encode(array(
                'KasUser' => $this->kas_user,
                'KasAuthType' => 'sha1',
                'KasPassword' => sha1($this->kas_pass),
                'SessionLifeTime' => $this->session_lifetime,
                'SessionUpdateLifeTime' => $this->session_update_lifetime
            )));
        }
        catch (SoapFault $fault) {
            trigger_error("Fehlernummer: {$fault->faultcode},
                    Fehlermeldung: {$fault->faultstring},
                    Verursacher: {$fault->faultactor},
                    Details: {$fault->detail}", E_USER_ERROR);
        }

    }

    public function makeRequest($apiFunction, $params = array())
    {

        try
        {
            $soapRequest = new SoapClient($this->kas_api_endpoint);
            $req = $soapRequest->KasApi(json_encode(array(
                'KasUser' => $this->kas_user,
                'KasAuthType' => 'session',
                'KasAuthData' => $this->credentialToken,
                'KasRequestType' => $apiFunction,
                'KasRequestParams' => $params
            )));
        }

        catch (SoapFault $fault)
        {
            trigger_error(" Fehlernummer: {$fault->faultcode},
                    Fehlermeldung: {$fault->faultstring},
                    Verursacher: {$fault->faultactor},
                    Details: {$fault->detail}", E_USER_ERROR);
        }

        return $req;

    }

    public function getAllUsers()
    {

        if (sizeof($this->users) <= 0) {
            $this->users = $this->makeRequest('get_mailaccounts')['Response']['ReturnInfo'];
        }

        return $this->users;

    }

    public function findUser($email)
    {

        $users = $this->getAllUsers();

        foreach ($users as $k => $user) {

            if ($user['mail_adresses'] == $email) {

                $match = $user;

            }

        }

        return $match;

    }

    public function changePwd($email, $newPassword)
    {

        if ($user = $this->findUser($email)) {

            $params = array(
                'mail_login' => $user['mail_login'],
                'mail_new_password' => $newPassword
            );

            echo $user['mail_login'];

            $response = $this->makeRequest('update_mailaccount', $params);

            print_r($response);

            return $response;

        }

        return false;

    }

}