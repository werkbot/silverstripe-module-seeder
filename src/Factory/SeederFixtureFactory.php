<?php

namespace Werkbot\Seeder\Factory;

use SilverStripe\AssetAdmin\Controller\AssetAdmin;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\FixtureBlueprint;
use SilverStripe\Dev\FixtureFactory;
use SilverStripe\ORM\DB;

class SeederFixtureFactory extends FixtureFactory
{
  private $createObjectCallback;
  private $seedObject;

  public function __construct($createObjectCallback = null, $seedObject = null)
  {
    if ($createObjectCallback) {
      $this->createObjectCallback = $createObjectCallback;
    }
    if ($seedObject) {
      $this->seedObject = $seedObject;
    }
  }

  /**
   * Writes the fixture into the database using DataObjects
   *
   * @param string $name Name of the {@link FixtureBlueprint} to use,
   *                     usually a DataObject subclass.
   * @param string $identifier Unique identifier for this fixture type
   * @param array $data Map of properties. Overrides default data.
   * @return DataObject
   */
  public function createObject($name, $identifier, $data = null)
  {
    if (!isset($this->blueprints[$name])) {
      $this->blueprints[$name] = new FixtureBlueprint($name);
    }
    $blueprint = $this->blueprints[$name];
    $obj = $blueprint->createObject($identifier, $data, $this->fixtures);
    $class = $blueprint->getClass();

    if (!isset($this->fixtures[$class])) {
      $this->fixtures[$class] = [];
    }
    $this->fixtures[$class][$identifier] = $obj->ID;

    // For any images and videos, store the file
    if ($class == File::class || $class == Image::class) {
      $url = $data['URL'];
      // If the origin is the same, we can use an absolute path
      $baseUrl = Environment::getEnv('SS_BASE_URL') ?: Director::absoluteBaseURL();
      if ($this->hasSameOrigin($url, $baseUrl)) {
        $urlPath = $this->getRelativeUrlPath($url, $baseUrl);
        $decodedUrlPath = rawurldecode($urlPath);
        $url = __DIR__ . '/../../../../../public/' . ltrim($decodedUrlPath, '/');
        $folderPath = dirname(ltrim($decodedUrlPath, '/'));
        $folderPath = preg_replace('#^assets(?:/|$)#', '', $folderPath);
        $folder = $folderPath && $folderPath !== '.'
          ? Folder::find_or_make($folderPath)
          : null;
      } else {
        // Keep files downloaded from other origins separate from existing assets.
        $folder = Folder::find_or_make('SeederFiles');
      }

      $contents = @file_get_contents($url);
      $obj->setFromString($contents, $data['Name']);
      $obj->ParentID = $folder ? $folder->ID : 0;
      $obj->write();
      AssetAdmin::create()->generateThumbnails($obj);
    }

    if ($this->createObjectCallback) {
      $createObjectCallback = $this->createObjectCallback;
      $createObjectCallback($obj, $class, $data);
    }

    // Track the generated object and associate with the seed in a pivot table
    if ($this->seedObject) {
      $seedObject = $this->seedObject;
      if (DB::get_conn()->getSchemaManager()->hasTable('SeedObject_Records')) {
        DB::query("INSERT INTO `SeedObject_Records` (ClassName, RecordID, SeedObjectID) VALUES ('" . addslashes($obj->ClassName) . "', '" . $obj->ID . "', '" . $seedObject->ID . "')");
      }
      $seedObject->Summary .= $identifier . ' created. <br>';
      $seedObject->write();
    }

    $obj->publishRecursive();

    if (!Environment::isCli()) {
      echo '<li class="info">' . $identifier . ' created.</li>';
    } else {
      echo ' - ' . $identifier . ' created.' . PHP_EOL;
    }

    return $obj;
  }

  private function hasSameOrigin(string $url, string $baseUrl): bool
  {
    $urlParts = parse_url($url);
    $baseUrlParts = parse_url($baseUrl);

    if (!$urlParts || !$baseUrlParts || !isset($urlParts['scheme'], $urlParts['host'], $baseUrlParts['scheme'], $baseUrlParts['host'])) {
      return false;
    }

    $urlScheme = strtolower($urlParts['scheme']);
    $baseUrlScheme = strtolower($baseUrlParts['scheme']);
    $urlPort = $urlParts['port'] ?? ($urlScheme === 'https' ? 443 : 80);
    $baseUrlPort = $baseUrlParts['port'] ?? ($baseUrlScheme === 'https' ? 443 : 80);

    return $urlScheme === $baseUrlScheme
      && strtolower($urlParts['host']) === strtolower($baseUrlParts['host'])
      && $urlPort === $baseUrlPort;
  }

  private function getRelativeUrlPath(string $url, string $baseUrl): string
  {
    $urlPath = (string) parse_url($url, PHP_URL_PATH);
    $baseUrlPath = rtrim((string) parse_url($baseUrl, PHP_URL_PATH), '/');

    if ($baseUrlPath && ($urlPath === $baseUrlPath || strpos($urlPath, $baseUrlPath . '/') === 0)) {
      return substr($urlPath, strlen($baseUrlPath));
    }

    return $urlPath;
  }

}

