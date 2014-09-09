<?php
/**
 * Подключение классов необходимых для работы модуля
 */
\Bitrix\Main\Loader::registerAutoLoadClasses('htc.sociallikes', array(
    'Htc\\SocialLikes\\Constants' => 'classes/SocialLikesConstants.php',
    'Htc\\SocialLikes\\Helper' => 'classes/SocialLikesHelper.php',
    'Htc\\SocialLikes\\SocialInterface' => 'classes/services/SocialInterface.php',
    'Htc\\SocialLikes\\FacebookService' => 'classes/services/FacebookService.php',
    'Htc\\SocialLikes\\OdnoklassnikiService' => 'classes/services/OdnoklassnikiService.php',
    'Htc\\SocialLikes\\VkontakteService' => 'classes/services/VkontakteService.php',
));
