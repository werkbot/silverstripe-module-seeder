<?php

namespace Werkbot\Seeder\SeederHistory;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLReadonlyField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/*
  Created each time a SeederBuildTask is ran
  This is labeled by the SeederBuildTask class name and date it was ran
  e.g.
  Generate Development Test Data - 2022-01-01

  This tracks all records created by the SeederBuildTask,
  so they can be torn down later.
 */
class SeedObject extends DataObject
{
  private static $table_name = 'SeedObject';

  private static $db = [
    'Title' => 'Varchar',
    'Summary' => 'HTMLText',
  ];

  public function getCMSFields()
  {
    return FieldList::create(
      ReadonlyField::create('Title'),
      HTMLReadonlyField::create('Summary')
    );
  }

  public function onAfterBuild()
  {
    parent::onAfterBuild();
    // Create Records Pivot Table if it does not exist
    if (!DB::get_conn()->getSchemaManager()->hasTable('SeedObject_Records')) {
      DB::query('CREATE TABLE `SeedObject_Records` (ID int primary key AUTO_INCREMENT, ClassName varchar(255), RecordID int, SeedObjectID int)');
    }
  }

  public function onAfterDelete()
  {
    parent::onBeforeDelete();
    // Delete all records associated with this seed
    $seedObjectRecords = DB::query('SELECT * FROM `SeedObject_Records` WHERE SeedObjectID = ' . $this->ID);
    foreach ($seedObjectRecords as $record) {
      $className = $record['ClassName'];
      $recordObject = $className::get()->byID($record['RecordID']);
      if ($recordObject) {
        if ($recordObject->hasExtension(Versioned::class)) {
          $recordObject->doUnpublish();
          $recordObject->doArchive();
        }
        $recordObject->delete();
      }
    }
  }

}

