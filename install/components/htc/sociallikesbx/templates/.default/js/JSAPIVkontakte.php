<div id="vk_api_transport"></div>
<script type="text/javascript">
    var $elementId = 'js-VKontakte-vote';

    window.vkAsyncInit = function() {
        VK.init({
            apiId: <?= $appId; ?>
        });
    };

    setTimeout(function() {
        var el = document.createElement("script");
        el.type = "text/javascript";
        el.src = "http://vkontakte.ru/js/api/openapi.js";
        el.async = true;
        document.getElementById("vk_api_transport").appendChild(el);
    }, 0);

    setTimeout(function() {
        if (get_cookie('IS_USER_WANTS_VOTE'))
        {
            set_cookie('IS_USER_WANTS_VOTE', '', -1);
            if (document.getElementById($elementId))
            {
                document.getElementById($elementId).onclick();
            }
        }
    }, 500);

    if (document.getElementById($elementId))
    {
        document.getElementById($elementId).onclick = function() {
            VK.Api.call(
                'wall.post',
                {
                    owner_id: <?= $userId; ?>,
                    message: '<?= $message; ?>',
                    attachments: '<?= $attachments; ?>'
                },
                function(result) {
                    if(result.response) {

                    }
                }
            );

            BX.ajax.post(
                window.location.href,
                {
                    //post_id: result.response.post_id,
                    vote: 'Y'
                },
                change_votes
            );
        }
    }

</script>