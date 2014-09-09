function changingNumberVotes($dataJson) {

    var $data = JSON.parse($dataJson);

    for (var $network in $data['NUMBER_VOTES']) {
        var $networkBlock = document.getElementsByClassName('js-' + $network.toLowerCase())[0];
        $networkBlock.getElementsByClassName('js-number-vote')[0].innerHTML = $data['NUMBER_VOTES'][$network];
    }

    var $currentSocial = $data['AUTH_SERVICES'][$data['USER']['SOCIAL_NETWORK_AUTH_USER']];
    if ($currentSocial['USER_CAN_VOTE'] == 'N' || ($currentSocial['USER_CAN_VOTE'] == 'Y' && $currentSocial['IS_SELECTED_SOCIAL_NETWORK'] == 'Y')) {
        document.getElementsByClassName('js-user-can-vote')[0].style.display = 'none';
        document.getElementsByClassName('js-user-can-not-vote')[0].style.display = 'block';
        document.getElementsByClassName('js-' + $data['USER']['SOCIAL_NETWORK_AUTH_USER'].toLowerCase())[0].classList.add('voted');
    }
    else {
        document.getElementsByClassName('js-user-can-vote')[0].style.display = 'block';
        document.getElementsByClassName('js-user-can-not-vote')[0].style.display = 'none';
        document.getElementsByClassName('js-' + $data['USER']['SOCIAL_NETWORK_AUTH_USER'].toLowerCase())[0].classList.remove('voted');
    }
}

function toVote() {
    BX.ajax.post(
        window.location.href,
        {
            vote: 'Y'
        },
        changingNumberVotes
    );
}
