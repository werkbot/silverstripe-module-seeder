# Silverstripe Seeder
A silverstripe database seeder that utilizes [Fixtures to generate DataObjects](https://docs.silverstripe.org/en/4/developer_guides/testing/fixtures/).

## Installation
```
composer require werkbot/werkbot-seeder
```

#### Requirements
- Silverstripe ^4.0

## Setup
- You will need to run `dev/build`

- You can now run seeder tasks in `dev/Tasks` while in development mode (`SS_ENVIRONMENT_TYPE="dev"`).

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
- Create a Custom Seeder Task:

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
- Create Fixture file for your Custom Seeder Task:

```
# app/seeds/CustomSeeder.yml

CustomDataObject:
  ArbitraryDataObjectNameOne:
    DataObjectProperty: 'Data Object Value'
  ArbitraryDataObjectNameTwo:
    DataObjectProperty: 'Data Object Value'
```

### Run All Enabled Seeders
Run all seeders with "Generate Development Test Data". Configure which seeders should run in DatabaseSeeder.yml:

```
# app/seeds/DatabaseSeeder.yml

Werkbot\Seeder\NavigationDropdownPagesSeeder:
  enabled: true
CustomSeeder:
  enabled: true
```
