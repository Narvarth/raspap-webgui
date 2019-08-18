<?php

include_once('includes/status_messages.php');

/**
 *
 * Find the version of the Raspberry Pi
 * Currently only used for the system information page but may useful elsewhere
 *
 */

function RPiVersion()
{
    // Lookup table from http://www.raspberrypi-spy.co.uk/2012/09/checking-your-raspberry-pi-board-version/
    $revisions = array(
    '0002' => 'Model B Revision 1.0',
    '0003' => 'Model B Revision 1.0 + ECN0001',
    '0004' => 'Model B Revision 2.0 (256 MB)',
    '0005' => 'Model B Revision 2.0 (256 MB)',
    '0006' => 'Model B Revision 2.0 (256 MB)',
    '0007' => 'Model A',
    '0008' => 'Model A',
    '0009' => 'Model A',
    '000d' => 'Model B Revision 2.0 (512 MB)',
    '000e' => 'Model B Revision 2.0 (512 MB)',
    '000f' => 'Model B Revision 2.0 (512 MB)',
    '0010' => 'Model B+',
    '0013' => 'Model B+',
    '0011' => 'Compute Module',
    '0012' => 'Model A+',
    'a01041' => 'a01041',
    'a21041' => 'a21041',
    '900092' => 'PiZero 1.2',
    '900093' => 'PiZero 1.3',
    '9000c1' => 'PiZero W',
    'a02082' => 'Pi 3 Model B',
    'a22082' => 'Pi 3 Model B',
    'a32082' => 'Pi 3 Model B',
    'a52082' => 'Pi 3 Model B',
    'a020d3' => 'Pi 3 Model B+',
    'a220a0' => 'Compute Module 3',
    'a020a0' => 'Compute Module 3',
    'a02100' => 'Compute Module 3+',
    'c03111' => 'Model 4B v1.1'
    );

    $cpuinfo_array = '';
    exec('cat /proc/cpuinfo', $cpuinfo_array);
    $rev = trim(array_pop(explode(':', array_pop(preg_grep("/^Revision/", $cpuinfo_array)))));
    if (array_key_exists($rev, $revisions)) {
        return $revisions[$rev];
    } else {
        return 'Unknown Pi';
    }
}

/**
 *
 *
 */
function DisplaySystem()
{

    $status = new StatusMessages();

    if (isset($_POST['SaveLanguage'])) {
        if (isset($_POST['locale'])) {
            $_SESSION['locale'] = $_POST['locale'];
            $status->addMessage('Language setting saved', 'success');
        }
    }

    // define locales
    $arrLocales = array(
    'en_GB.UTF-8' => 'English',
    'de_DE.UTF-8' => 'Deutsch',
    'fr_FR.UTF-8' => 'Français',
    'it_IT.UTF-8' => 'Italiano',
    'pt_BR.UTF-8' => 'Português',
    'sv_SE.UTF-8' => 'Svenska',
    'nl_NL.UTF-8' => 'Nederlands',
    'zh_CN.UTF-8' => '简体中文 (Chinese simplified)',
    'cs_CZ.UTF-8' => 'Čeština',
    'ru_RU.UTF-8' => 'Русский',
    'es_MX.UTF-8' => 'Español',
    'fi_FI.UTF-8' => 'Finnish',
    'si_LK.UTF-8' => 'Sinhala',
    'tr_TR.UTF-8' => 'Türkçe'
    );

    require_once "lib/system.php";
    $system = new System();

    $hostname = $system->hostname();
    $uptime   = $system->uptime();
    $cores    = $system->processorCount();

    // mem used
    $memused  = $system->usedMemory();
    $memused_status = "primary";
    if ($memused > 90) {
        $memused_status = "danger";
    } elseif ($memused > 75) {
        $memused_status = "warning";
    } elseif ($memused >  0) {
        $memused_status = "success";
    }

    // cpu load
    $cpuload = $system->systemLoadPercentage();
    if ($cpuload > 90) {
        $cpuload_status = "danger";
    } elseif ($cpuload > 75) {
        $cpuload_status = "warning";
    } elseif ($cpuload >  0) {
        $cpuload_status = "success";
    }

    if (isset($_POST['system_reboot'])) {
        $status->addMessage("System Rebooting Now!", "warning", false);
        $result = shell_exec("sudo /sbin/reboot");
    }
    if (isset($_POST['system_shutdown'])) {
        $status->addMessage("System Shutting Down Now!", "warning", false);
        $result = shell_exec("sudo /sbin/shutdown -h now");
    }

    ?>
  <div class="row">
  <div class="col-lg-12">
  <div class="panel panel-primary">
  <div class="panel-heading"><i class="fa fa-cube fa-fw"></i> <?php echo _("System"); ?></div>
  <div class="panel-body">
  <?php $status->showMessages(); ?>
  <form role="form" action="?page=system_info" method="POST">
  <?php echo CSRFTokenFieldTag() ?>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active systemtab"><a href="#system" aria-controls="system" role="tab" data-toggle="tab"><?php echo _("System"); ?></a></li>
    <li role="presentation" class="languagetab"><a href="#language" aria-controls="language" role="tab" data-toggle="tab"><?php echo _("Language"); ?></a></li>
    <li role="presentation" class="consoletab"><a href="#console" aria-controls="console" role="tab" data-toggle="tab"><?php echo _("Console"); ?></a></li>
  </ul>

  <div class="systemtabcontent tab-content">
    <div role="tabpanel" class="tab-pane active" id="system">
      <div class="row">
        <div class="col-lg-6">
          <h4><?php echo _("System Information"); ?></h4>
          <div class="info-item"><?php echo _("Hostname"); ?></div> <?php echo htmlspecialchars($hostname, ENT_QUOTES); ?></br>
          <div class="info-item"><?php echo _("Pi Revision"); ?></div> <?php echo htmlspecialchars(RPiVersion(), ENT_QUOTES); ?></br>
          <div class="info-item"><?php echo _("Uptime"); ?></div>   <?php echo htmlspecialchars($uptime, ENT_QUOTES); ?></br></br>
          <div class="info-item"><?php echo _("Memory Used"); ?></div>
          <div class="progress">
          <div class="progress-bar progress-bar-<?php echo htmlspecialchars($memused_status, ENT_QUOTES); ?> progress-bar-striped active"
          role="progressbar"
          aria-valuenow="<?php echo htmlspecialchars($memused, ENT_QUOTES); ?>" aria-valuemin="0" aria-valuemax="100"
          style="width: <?php echo htmlspecialchars($memused, ENT_QUOTES); ?>%;"><?php echo htmlspecialchars($memused, ENT_QUOTES); ?>%
          </div>
          </div>
          <div class="info-item"><?php echo _("CPU Load"); ?></div>
          <div class="progress">
          <div class="progress-bar progress-bar-<?php echo htmlspecialchars($cpuload_status, ENT_QUOTES); ?> progress-bar-striped active"
          role="progressbar"
          aria-valuenow="<?php echo htmlspecialchars($cpuload, ENT_QUOTES); ?>" aria-valuemin="0" aria-valuemax="100"
          style="width: <?php echo htmlspecialchars($cpuload, ENT_QUOTES); ?>%;"><?php echo htmlspecialchars($cpuload, ENT_QUOTES); ?>%
          </div>
          </div>

          <form action="?page=system_info" method="POST">
          <?php echo CSRFTokenFieldTag() ?>
          <input type="submit" class="btn btn-warning" name="system_reboot"   value="<?php echo _("Reboot"); ?>" />
          <input type="submit" class="btn btn-warning" name="system_shutdown" value="<?php echo _("Shutdown"); ?>" />
          <input type="button" class="btn btn-outline btn-primary" value="<?php echo _("Refresh"); ?>" onclick="document.location.reload(true)" />
          </form>
        </div>
      </div>
    </div>

    <div role="tabpanel" class="tab-pane" id="language">
      <h4><?php echo _("Language settings") ;?></h4>
      <div class="row">
        <div class="form-group col-md-4">
          <label for="code"><?php echo _("Select a language"); ?></label>
            <?php SelectorOptions('locale', $arrLocales, $_SESSION['locale']); ?>
        </div>
      </div>
      <input type="submit" class="btn btn-outline btn-primary" name="SaveLanguage" value="<?php echo _("Save settings"); ?>" />
      <input type="button" class="btn btn-outline btn-primary" value="<?php echo _("Refresh"); ?>" onclick="document.location.reload(true)" />
    </div>

    <div role="tabpanel" class="tab-pane" id="console">
      <div class="row">
        <div class="col-lg-12"> 
          <iframe src="includes/webconsole.php" class="webconsole"></iframe>
        </div>
      </div>
    </div>

  </div><!-- /.systemtabcontent -->

  </form>
  </div><!-- /.panel-body -->
  <div class="panel-footer"></div>
  </div><!-- /.panel-primary -->
  </div><!-- /.col-lg-12 -->
  </div><!-- /.row -->
    <?php
}

