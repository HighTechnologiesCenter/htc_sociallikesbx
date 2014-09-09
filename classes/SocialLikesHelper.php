<?php

namespace Htc\SocialLikes;

use Htc\SocialLikes\Constants as Constants;
use Htc\SocialLikes\VkontakteService as VkontakteService;
use Htc\SocialLikes\OdnoklassnikiService as OdnoklassnikiService;
use Htc\SocialLikes\FacebookService as FacebookService;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Helper {

    /**
     * Возвращает массив соц сетей
     *
     * @return array
     */
    public static function getSocialNetworks ()
    {
        $vkService = new VkontakteService();
        $fbService = new FacebookService();
        $okService = new OdnoklassnikiService();

        return array(
            $vkService->getFullNameNetwork() => Loc::getMessage('VKONTAKTE'),
            $fbService->getFullNameNetwork() => Loc::getMessage('FACEBOOK'),
            $okService->getFullNameNetwork() => Loc::getMessage('ODNOKLASSNIKI')
        );
    }

    /**
     * Возвращает наименование соц сети через которую авторизован пользователь
     *
     * @param $userLogin
     * @return string
     */
    public static function getAuthUserNetwork($userLogin)
    {
        $result = '';
        $vkService = new VkontakteService();
        $fbService = new FacebookService();
        $okService = new OdnoklassnikiService();

        if (substr_count($userLogin, $vkService->getShortNameNetwork()) > 0) {
            $result = $vkService->getFullNameNetwork();
        }
        elseif (substr_count($userLogin, $fbService->getShortNameNetwork()) > 0) {
            $result = $fbService->getFullNameNetwork();
        }
        elseif (substr_count($userLogin, $okService->getShortNameNetwork()) > 0) {
            $result = $okService->getFullNameNetwork();
        }

        return $result;
    }

    /**
     * Возвращает объект сервиса
     * @param $networkName
     * @return bool|FacebookService|OdnoklassnikiService|VkontakteService
     */
    public static function getNetworkService($networkName)
    {

        $vkService = new VkontakteService();
        $fbService = new FacebookService();
        $okService = new OdnoklassnikiService();

        $networkName = $networkName;
        $result = false;

        switch ($networkName) {
            case $vkService->getFullNameNetwork():
                $result = $vkService;
                break;
            case $fbService->getFullNameNetwork():
                $result = $fbService;
                break;
            case $okService->getFullNameNetwork():
                $result = $okService;
                break;
        }

        return $result;
    }
} 