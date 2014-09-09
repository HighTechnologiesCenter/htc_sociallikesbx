<?php

namespace Htc\SocialLikes;


interface SocialInterface
{
	/**
	 * Возвращает URL, по которому необходимо перенаправить пользователя для шаринга ссылки
	 * @param string $url
	 * @return string
	 */
	public function getShareUrl($url);

    /**
     * Возвращает полное имя соц сети
     * @return string
     */
    public function getFullNameNetwork();

    /**
     * Возвращает сокращенное имя соц сети
     * @return string
     */
    public function getShortNameNetwork();
} 