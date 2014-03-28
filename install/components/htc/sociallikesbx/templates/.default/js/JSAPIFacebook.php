<div id="fb-root"></div>
<script src="http://connect.facebook.net/ru_RU/all.js"></script>
<script type="text/javascript">
    var $elementId = 'js-Facebook-vote';

    FB.init({
        appId : <?= $appId; ?>,
        channelUrl : 'http://' + window.document.location.hostname + '/channel.php',
        oauth : false,
        status : true,
        cookie : true,
        xfbml : true
    });

    setTimeout(function() {
        if (get_cookie('IS_USER_WANTS_VOTE'))
        {
            set_cookie('IS_USER_WANTS_VOTE', '', -1);
            if (document.getElementById($elementId))
            {
                document.getElementById($elementId).onclick();
            }
        }
    }, 200);

    function postFB()
    {
        FB.api(
            '/me/feed',
            'post',
            {
                message: '<?= $message; ?>',
                description: '<?= $message; ?>',
                link: '<?= $linkUrl; ?>',
                name: '<?= $linkName; ?>',
                picture: '<?= $pictureUrl; ?>'
            },
            function(response) {
                if (!response || response.error) {
                    postFB();
                    return false;
                }
            }
        );
    }

    if (document.getElementById($elementId))
    {
        document.getElementById($elementId).onclick = function() {
            postFB();

            BX.ajax.post(
                window.location.href,
                {
                    //post_id: response.id,
                    vote: 'Y'
                },
                change_votes
            );
        }
    }

</script>