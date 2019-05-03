<?php
declare(strict_types=1);

namespace wearewondrous\PshToolbelt;

use Robo\Config\Config as RoboConfig;
use Robo\Robo;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ConfigFileReader {

  const SRC_PATH = 'vendor/wearewondrous/psh-toolbelt/src';

  /**
   * @var RoboConfig
   */
  protected $roboConfig;

  /**
   * @var string
   */
  private $rootDirectory;

  /**
   * SiteSettings constructor.
   *
   * @param string $rootDirectory
   */
  public function __construct(string $rootDirectory = null) {
    if(!$rootDirectory) {
      $rootDirectory = $this->getRootDir();
    }

    $this->rootDirectory = $rootDirectory;

    $this->roboConfig = $this->getRoboConfig();
  }

  /**
   * Get merged yml config.
   *
   * @return RoboConfig
   */
  public function getRoboConfig(): RoboConfig {
    return Robo::createConfiguration([
      $this->getProjectLocalConfigDistFilename(),
      $this->getProjectLocalConfigFilename(),
    ]);
  }

  /**
   * @return string
   */
  public function getRootDir(): string {
    return str_replace(self::SRC_PATH, '', __DIR__);
  }

  /**
   * @return array
   */
  public function getAcceptableProjectLocalConfigFilenames(): array {
    $acceptableFilenames = [
      'robo',
      'toolbelt'
    ];

    return array_map(function ($acceptableFilename) {
      return $acceptableFilename . '.yml';
    }, $acceptableFilenames);
  }

  /**
   * @return string
   */
  public function getProjectLocalConfigFilename() : string {
    $realProjectConfigFiles = array_filter($this->getAcceptableProjectLocalConfigFilenames(), function ($acceptableProjectLocalConfigFilename) {
      return file_exists( $this->rootDirectory . '/' .$acceptableProjectLocalConfigFilename);
    });

    if(!$realProjectConfigFiles) {
      throw new FileNotFoundException('No valid configuration files found');
    }

    return reset($realProjectConfigFiles);
  }

  /**
   * @return string
   */
  public function getProjectLocalConfigDistFilename() : string {
    return $this->getProjectLocalConfigFilename() . '.dist';
  }

  /**
   * Render a config split array for default, dev, and production info
   *
   * @return array
   */
  public function getConfigSplitFromRoboConfig() : array {
    return [
      'default' => [
        'machine_name' => implode('.', [
          'config_split.config_split',
          $this->roboConfig->get('drupal.config.splits.default.machine_name'),
        ]),
        'folder' => implode('/', [
          $this->rootDirectory,
          $this->roboConfig->get('platform.mounts.config'),
          $this->roboConfig->get('drupal.config.splits.default.folder'),
        ]),
      ],
      'prod' => [
        'machine_name' => implode('.', [
          'config_split.config_split',
          $this->roboConfig->get('drupal.config.splits.prod.machine_name'),
        ]),
        'folder' => implode('/', [
          $this->rootDirectory,
          $this->roboConfig->get('platform.mounts.config'),
          $this->roboConfig->get('drupal.config.splits.prod.folder'),
        ]),
      ],
      'dev' => [
        'machine_name' => implode('.', [
          'config_split.config_split',
          $this->roboConfig->get('drupal.config.splits.dev.machine_name'),
        ]),
        'folder' => implode('/', [
          $this->rootDirectory,
          $this->roboConfig->get('platform.mounts.config'),
          $this->roboConfig->get('drupal.config.splits.dev.folder'),
        ]),
      ],
    ];
  }
}
