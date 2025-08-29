<?php

namespace Werkbot\Seeder\Settings;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;

class SeederSettings extends DataObject
{
  private static $table_name = 'SeederSettings';

  private static $db = [
    'Enabled' => 'Boolean',
  ];

  public function getCMSFields()
  {
    return FieldList::create(
      FieldGroup::create(
        'Enable seeder?',
        CheckboxField::create('Enabled', 'Enable')
      )
    );
  }

  /**
   * Get the current sites SeederSettings, and creates a new one through
   * {@link createSeederSettings()} if none is found.
   *
   * @return SeederSettings
   */
  public static function currentSeederSettings(): SeederSettings
  {
    $seederSettings = DataObject::get_one(SeederSettings::class);
    if (!$seederSettings) {
      $seederSettings = SeederSettings::createSeederSettings();
    }

    static::singleton()->extend('updateCurrentSeederSettings', $seederSettings);

    return $seederSettings;
  }

  /**
   * Setup a default SeederSettings record if none exists.
   */
  public function requireDefaultRecords()
  {
    parent::requireDefaultRecords();

    $seederSettings = DataObject::get_one(SeederSettings::class);

    if (!$seederSettings) {
      SeederSettings::createSeederSettings();

      DB::alteration_message('Added seeder settings', 'created');
    }
  }

  /**
   * Create SeederSettings
   *
   * @return SeederSettings
   */
  public static function createSeederSettings(): SeederSettings
  {
    $seederSettings = SeederSettings::create();
    $seederSettings->write();

    return $seederSettings;
  }

}

