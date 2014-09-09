<?php

namespace Htc\SocialLikes;

class OdnoklassnikiService implements SocialInterface {
    public $fullNameNetwork = 'Odnoklassniki';

    public $shortNameNetwork = 'OKuser';

    /**
     * Возвращает url для шаринга
     *
     * @param string $url
     * @return string
     */
    public function getShareUrl($url)
    {
        $queryData = [
            'st.cmd' => 'addShare',
            'st._surl' => $url,
        ];
        return 'http://www.odnoklassniki.ru/dk?'.http_build_query($queryData);
    }

    /**
     * Возвращает полное имя соц сети
     * @return string
     */
    public function getFullNameNetwork()
    {
        return $this->fullNameNetwork;
    }

    /**
     * Возвращает сокращенное имя соц сети
     * @return string
     */
    public function getShortNameNetwork()
    {
        return $this->shortNameNetwork;
    }
}

