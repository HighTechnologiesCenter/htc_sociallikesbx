<script type="text/javascript">
    var $elementId = 'js-Odnoklassniki-vote';

    setTimeout(function() {
        if (get_cookie('IS_USER_WANTS_VOTE'))
        {
            set_cookie('IS_USER_WANTS_VOTE', '', -1);
            if (document.getElementById($elementId))
            {
                document.getElementById($elementId).onclick();
            }
        }
    }, 100);

    if (document.getElementById($elementId))
    {
        document.getElementById($elementId).onclick = function() {
            BX.util.popup('http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1&st.comments=' + encodeURIComponent('<?= $message?>') + '&st._surl=' + encodeURIComponent('<?= $linkUrl?>'), 580, 400);
            BX.ajax.post(
                window.location.href,
                {
                    vote: 'Y'
                },
                change_votes
            );
        }
    }

</script>