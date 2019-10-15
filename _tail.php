<div class="alert alert-light mt-5 mb-5" style="max-width: 40em; margin: auto">
    Wenn du dich nicht einloggen kannst oder anderweitig Probleme hast, wende dich bitte an den IT-Support.
    <code>/it-support [deine Nachricht]</code> <a class="btn btn-sm btn-info" href="http://cct-ev.slack.com" target="_blank">zu Slack <i class="fa fa-caret-right"></i></a>
</div>

</div>
<script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
<!--<script src="<?= $_SERVER['HOST_NAME'] ?>/js/jquery-3.4.1.min.js" type="javascript"></script>-->
<script>
    $(document).ready(function () {
        $("#passive").change(function () {
            if (this.checked) {
                //$("#passiveSinceWrapper").css('visibility', 'visible');
                $("#passiveSinceWrapper").show();
                $("#passiveUntilWrapper").show();
                $("#passiveSince").prop('required',true);

            } else {
                $("#passiveSinceWrapper").hide();
                $("#passiveUntilWrapper").hide();
                $("#passiveSince").prop('required',false);
            }
        });
    });
</script>
</body>
</html>
