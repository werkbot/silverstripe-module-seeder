<?php
/**/
namespace Werkbot\Seeder;
/**/
use SilverStripe\Dev\BuildTask;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\YamlFixture;
/**/
class NavigationDropdownPagesSeeder extends BuildTask {
	/**/
	protected $title = 'Generate Navigation Dropdown Pages';
	protected $description = 'Generate navigation dropdown pages. This is an often repeated process when testing sites. This sets up navigation dropdown pages automatically.';
	protected $enabled = true;
	/**/
	public function run($request)
	{
		if(Environment::getEnv('SS_ENVIRONMENT_TYPE') == 'dev'){
			if(file_exists('seeds/NavigationDropdownPages.yml')){
				$fixtureFile = 'app/seeds/NavigationDropdownPages.yml';
			} else {
				$fixtureFile = __DIR__ . '/../Fixtures/NavigationDropdownPages.yml';
			}
			$fixture = YamlFixture::create($fixtureFile);
			$fixture->writeInto(new SeederFixtureFactory());
		} else {
			echo 'Must run in development environment';
		}
	}
} 