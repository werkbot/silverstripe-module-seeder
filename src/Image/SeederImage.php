<?php
/**/
namespace Werkbot\Seeder;
/**/
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Folder;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\DataExtension;
use SilverStripe\AssetAdmin\Controller\AssetAdmin;
/**/
class SeederImage extends DataExtension{
  /*
    Require Default Records
    On dev/build these images and folder are added to the site
  */
  public function requireDefaultRecords(){
    parent::requireDefaultRecords();

		// Only run in a dev environment
		if(Environment::getEnv('SS_ENVIRONMENT_TYPE') == 'dev') {
      $folder = Folder::find_or_make("SeederImages");
      $folder->write();

      $imgs = [
        "seeder-1920x279.jpg",
        "seeder-500x500.jpg",
        "seeder-100x100.jpg",
      ];

      foreach($imgs as $img){

        $ExistsInDatabase = Image::get()->filter([
          "Name" => $img,
          "ParentID" => $folder->ID,
        ])->First();

        if(!$ExistsInDatabase){
          $contents = @file_get_contents(__DIR__."/images/".$img);
          if($contents !== FALSE){
            $image = Image::create();
            $image->setFromString($contents, $img);
            $image->ParentID = $folder->ID;
            $image->write();
            AssetAdmin::create()->generateThumbnails($image);
            $image->publishSingle();
          }
        }
      }
    }
  }
}
