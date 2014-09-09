<?php

namespace Htc\SocialLikes;

class VkontakteService implements SocialInterface
{
    public $fullNameNetwork = 'VKontakte';

    public $shortNameNetwork = 'VKuser';

    /**
     * Возвращает url для шаринга
     *
     * @param string $url
     * @return string
     */
    public function getShareUrl($url)
    {
		$queryData = array(
			'url' => $url,
        );
		return 'https://vk.com/share.php?' . http_build_query($queryData);
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
