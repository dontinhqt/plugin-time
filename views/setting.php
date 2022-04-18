<div class="wrap">
    <h2>Timer Settings</h2>
    <form method="post" action="options.php">
        <?php
            settings_fields('timerSettingGroup');
            do_settings_sections('timer-setting-url');





            submit_button();
        ?>
    </form>
</div>