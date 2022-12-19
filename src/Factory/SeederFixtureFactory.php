<?php
/**/
namespace Werkbot\Seeder;
/**/
use SilverStripe\AssetAdmin\Controller\AssetAdmin;
use SilverStripe\Core\Environment;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\Dev\FixtureBlueprint;
use SilverStripe\Dev\FixtureFactory;
use SilverStripe\ORM\DB;
/**/
class SeederFixtureFactory extends FixtureFactory
{
    /**/
    private $createObjectCallback;
    private $seedObject;
    /**/
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
        // Create a Folder for any seeder images generated
        $folder = Folder::find_or_make("SeederFiles");
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

        // For any images and videos, store the file
        if ($class == File::class || $class == Image::class) {
            $contents = @file_get_contents($data['URL']);
            $obj->setFromString($contents, $data['Name']);
            $obj->ParentID = $folder->ID;
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
}