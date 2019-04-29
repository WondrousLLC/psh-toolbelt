<?php

namespace wearewondrous\PshToolbelt\Commands;

use Boedah\Robo\Task\Drush\loadTasks;
use Platformsh\ConfigReader\Config;
use Robo\Robo;
use Robo\Tasks;
use wearewondrous\PshToolbelt\SiteSettings;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
abstract class BaseCommands extends Tasks {

  use loadTasks;

  const FILE_DELIMITER = '--';

  const DATETIME_FORMAT = 'c';

  const DATE_FORMAT = 'Y-m-d';

  const VCS_MASTER = 'master';

  const DB_DUMP_SUFFIX = '--dump';

  const FILES_DUMP_SUFFIX = '--files-%s';

  protected $drushAlias = '';

  /**
   * @var Platformsh\ConfigReader\Config
   */
  protected $pshConfig;

  /**
   * Command initialization.
   *
   * @hook init
   */
  public function initEnvironmentVars(): void {
    Robo::Config()->replace(SiteSettings::getRoboConfig()->export());
    $this->stopOnFail();  // halt, if native cli commands fail
    $this->drushAlias = implode('', [
      '@',
      Robo::Config()->get('drush.alias_group'),
      '.',
      Robo::Config()->get('drush.alias'),
    ]);

    $this->pshConfig = new Config();

    if (!$this->pshConfig->isValidPlatform()) {
      return;
    }

    Robo::Config()->setDefault('drush.path', 'drush');
  }

  /**
   * @param string $variable
   *
   * @return string|null
   */
  protected function getEnv(string $variable): ?string {
    return $this->pshConfig->variable($variable, getenv($variable));
  }
}
