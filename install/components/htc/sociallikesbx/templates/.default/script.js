function set_cookie($name, $value, $expires) {
    var $date = new Date( new Date().getTime() + $expires * 1000 );
    document.cookie = $name + "=" + escape($value) + "; expires=" + $date.toGMTString() +  "; path=/";
}

function get_cookie($name) {
    var $cookieName = $name + "=";
    var $cookieLength = document.cookie.length;
    var $cookieBegin = 0;
    while ($cookieBegin < $cookieLength)
    {
        var $valueBegin = $cookieBegin + $cookieName.length;
        if (document.cookie.substring($cookieBegin, $valueBegin) == $cookieName)
        {
            var $valueEnd = document.cookie.indexOf (";", $valueBegin);
            if ($valueEnd == -1)
            {
                $valueEnd = $cookieLength;
            }
            return unescape(document.cookie.substring($valueBegin, $valueEnd));
        }
        $cookieBegin = document.cookie.indexOf(" ", $cookieBegin) + 1;
        if ($cookieBegin == 0)
        {
            break;
        }
    }
    return null;
}

function change_votes(data){
    var $json = JSON.parse(data);
    var $vk = document.getElementsByClassName('VKontakte-button')[0];
    $vk.getElementsByClassName('vote_number')[0].innerHTML = $json['NUMBER_VOTES']['VKONTAKTE'];

    var $fb = document.getElementsByClassName('Facebook-button')[0];
    $fb.getElementsByClassName('vote_number')[0].innerHTML = $json['NUMBER_VOTES']['FACEBOOK'];

    var $od = document.getElementsByClassName('Odnoklassniki-button')[0];
    $od.getElementsByClassName('vote_number')[0].innerHTML = $json['NUMBER_VOTES']['ODNOKLASSNIKI'];

    if ($json['AUTH_SERVICES'][$json['USER']['SOCIAL_NETWORK_AUTH_USER']]['USER_CAN_VOTE'] == 'Y')
    {
        document.getElementsByClassName('soc-vote-title')[0].innerHTML = 'Голосовать за работу';
    }
    else
    {
        document.getElementsByClassName('soc-vote-title')[0].innerHTML = 'Сегодня вы уже проголосовали';
        document.getElementsByClassName($json['SOCIAL_NETWORK_AUTH_USER'] + '-button')[0].classList.add('voted');
    }
}