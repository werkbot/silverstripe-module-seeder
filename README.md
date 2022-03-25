# Silverstripe Seeder
A silverstripe database seeder that utilizes [Fixtures to generate DataObjects](https://docs.silverstripe.org/en/4/developer_guides/testing/fixtures/).

## Installation
```
composer require werkbot/werkbot-seeder
```

#### Requirements
- Silverstripe ^4.0

## Setup
- You will need to run `/dev/build`

- You can now run seeder tasks in `/dev/Tasks` while in development mode (`SS_ENVIRONMENT_TYPE="dev"`).

## Usage

### Overriding Seeders
- Create an `app/seeds` directory.
- Override the seeder fixture file:

```
# app/seeds/NavigationDropdownPages.yml

Page:
  DefaultParentPage:
    Title: 'Parent Page'
    SettingNavigationType: 'default'
  DefaultChildPageOne:
    Title: 'Child Page One'
    Parent: =>Page.DefaultParentPage
```

#### Default Seeder Fixtures
- DatabaseSeeder.yml
- NavigationDropdownPages.yml
		    
### Custom Seeders
- Create a Custom SeederBuildTask:

```
<?php
/**/
use Werkbot\Seeder\SeederBuildTask;
/**/
class CustomSeeder extends SeederBuildTask {
	/**/
	protected $title = 'Generate Custom Data';
	protected $description = '';
	protected $enabled = true;
	/**/
	protected $fixtureFileName = 'CustomSeeder.yml';
}
```
- Create Fixture file for your Custom SeederBuildTask:

```
# app/seeds/CustomSeeder.yml

CustomDataObject:
  ArbitraryDataObjectNameOne:
    DataObjectProperty: 'Data Object Value'
  ArbitraryDataObjectNameTwo:
    DataObjectProperty: 'Data Object Value'
```

### Run All Enabled Seeders
Run all enabled seeders with `/dev/tasks/Werkbot-Seeder-SeederBuildTask`. Configure which seeders should run in DatabaseSeeder.yml:

```
# app/seeds/DatabaseSeeder.yml

Werkbot\Seeder\NavigationDropdownPagesSeeder:
  enabled: true
CustomSeeder:
  enabled: true
```

### Hook into Generated Objects
Sometimes, you might want to manipulate the generated DataObjects with PHP. A `createObjectCallback` method can be provided in your SeederBuildTask. This runs everytime a DataObject is generated. The generated DataObject instance (`$obj`), the DataObject's class name (`$class`), and the properties passed in by your Fixture file (`$data`) are available within your `createObjectCallback`.

#### The SeederBuildTask
```
<?php
/**/
use Werkbot\Seeder\SeederBuildTask;
/**/
class CalendarPageSeeder extends SeederBuildTask {
	/**/
	protected $title = 'Generate Calendar Page Seeder';
	protected $description = 'Generate a calendar page with events.';
	protected $enabled = true;
	/**/
	protected $fixtureFileName = 'CalendarPageSeeder.yml';
	/**/
	public function createObjectCallback($obj, $class, $data)
	{
		if($class == EventDate::class){
			/*
				Dynamically generate dates with PHP:

				"DaysInTheFuture" is not a property of "EventDate",
				but it is used to dynamically generate the "StartDate" property.
			*/
			if(isset($data['DaysInTheFuture'])){
				$obj->StartDate = date('Y-m-d H:i:s', strtotime('+' . $data['DaysInTheFuture'] . ' days'));
			} else {
				$obj->StartDate = date('Y-m-d H:i:s');
			}
			$obj->write();
		}
	}
} 
```
#### The Fixture File
```
EventDate:
  EventDateOne:
    DaysInTheFuture: 10
```
