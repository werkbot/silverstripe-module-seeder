<?php
/**/
namespace Werkbot\Seeder;
/**/
use SilverStripe\Dev\BuildTask;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\YamlFixture;
/**/
class SeederBuildTask extends BuildTask {
	/**/
	protected $title = 'Generate Development Test Data';
	protected $description = 'Generate development test data. Reduces the time it takes to test a newly installed site.';
	protected $enabled = true;
	/**/
	protected $fixtureFileName;
	/**/
	public function run($request)
	{
		if(Environment::getEnv('SS_ENVIRONMENT_TYPE') == 'dev') {
			if(file_exists('../app/seeds/' . $this->fixtureFileName)){
				$fixtureFile = 'app/seeds/' . $this->fixtureFileName;
			} else if(file_exists(__DIR__ . '/../Fixtures/' . $this->fixtureFileName)) {
				$fixtureFile = __DIR__ . '/../Fixtures/' . $this->fixtureFileName;
			} else {
				echo 'No fixture file found. Create an "app/seeds/$fixtureFileName"';
				return;
			}
			$fixture = YamlFixture::create($fixtureFile);
			$fixture->writeInto(new SeederFixtureFactory());
		} else {
			echo 'Must run in development environment';
		}
	}
} 