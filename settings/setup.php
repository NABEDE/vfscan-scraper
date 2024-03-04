<?php

class ScanMangasSettings
{

    private $fetchAdditionalData;
    public function __construct()
    {
        $this->vfscan_updateOptions();
    }
    private function vfscan_updateOptions()
    {

        if (isset($_POST['vfscanSubmitOptions'])) {

            if (isset($_POST['vfscanAutoUpdateOp']) == 'on') {
                $vfscanAutoUpdateOp = "true";
            } else {
                $vfscanAutoUpdateOp = "false";
            }

            if (isset($_POST['vfscanScanlAll']) == 'on') {
                $vfscanScanAll = "true";
            } else {
                $vfscanScanAll = "false";
            }

            $vfscan_schedule = $_POST['vfscanSchedule'];



            update_option('vfscan_schedule_option', $vfscan_schedule);
            update_option('vfscan_scanall_option', $vfscanScanAll);
            update_option('vfscan_autoupdate_option', $vfscanAutoUpdateOp);



            echo "<div class='notice notice-success is-dismissible'><p>Saved settings</p></div>";
        }
    }
}

$vfscanDefaultSettings = new ScanMangasSettings();
