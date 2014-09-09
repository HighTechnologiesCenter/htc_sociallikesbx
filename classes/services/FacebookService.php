<?php
namespace Htc\SocialLikes;


class FacebookService implements SocialInterface
{
    public $fullNameNetwork = 'Facebook';

    public $shortNameNetwork = 'FB_';

    /**
     * @param string $url
     * @return string
     */
    public function getShareUrl($url)
    {
        $queryData = array(
            'u' => $url,
        );
        return 'https://www.facebook.com/sharer/sharer.php?' . http_build_query($queryData);
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