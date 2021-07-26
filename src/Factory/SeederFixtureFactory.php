<?php
/**/
namespace Werkbot\Seeder;
/**/
use SilverStripe\Assets\Folder;
use SilverStripe\Dev\FixtureFactory;
use SilverStripe\Dev\FixtureBlueprint;
use SilverStripe\AssetAdmin\Controller\AssetAdmin;
/**/
class SeederFixtureFactory extends FixtureFactory {
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
        // Create a Folder for any seeder images generated
        $folder = Folder::find_or_make("SeederImages");
        //
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

        // For any images, lets store the image
        if($class == "SilverStripe\Assets\Image"){
          $contents = @file_get_contents($data['URL']);
          $obj->setFromString($contents, $data['Name']);
          $obj->ParentID = $folder->ID;
          $obj->write();
          AssetAdmin::create()->generateThumbnails($obj);
        // THE FOLLOWING CLASSES ARE WITHOUT NAMESPACES.
        // THIS WILL NEED TO BE UPDATED WHEN THE CALENDAR IS NAMESPACED
        // https://werkbotstudios.teamwork.com/#/tasks/23804616
        } else if($class == "Event"){
          if(isset($data['RepeatStartDaysInTheFuture'])){
              $obj->RepeatStartDate = date('Y-m-d H:i:s', strtotime('+' . $data['RepeatStartDaysInTheFuture'] . ' days'));
              $obj->RepeatEndDate = date('Y-m-d H:i:s', strtotime('+' . $data['RepeatEndDaysInTheFuture'] . ' days'));
              $obj->write();
          }
        } else if($class == "EventDate"){
          if(isset($data['DaysInTheFuture'])){
            $obj->StartDate = date('Y-m-d H:i:s', strtotime('+' . $data['DaysInTheFuture'] . ' days'));
          } else {
            $obj->StartDate = date('Y-m-d H:i:s');
          }
          $obj->write();
        } else if($class == "EventTime"){
          if(isset($data['TimeClass'])){
            if($data['TimeClass'] == 'Event'){
              $obj->write();
              $obj->Time()->RewriteDates();
            }
          }
        }

        $obj->publishRecursive();

        echo $identifier . ' created. <br>';

        return $obj;
    }
}
