<?php
/**/
namespace Werkbot\Seeder;
/**/
use SilverStripe\Dev\BuildTask;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\YamlFixture;
use Symfony\Component\Yaml\Parser;
use SilverStripe\Core\Injector\Injector;
/**/
class SeederBuildTask extends BuildTask {
	/**/
	protected $title = 'Generate Development Test Data';
	protected $description = 'Generate development test data. Reduces the time it takes to test a newly installed site.';
	protected $enabled = true;
	/**/
	protected $fixtureFileName = 'DatabaseSeeder.yml';
	/**/
	public function run($request)
	{
		// Used to get contents of DatabaseSeeder.yml override
		$pathResolver = '';
		// Only run in a dev or test environment
		if(Environment::getEnv('SS_ENVIRONMENT_TYPE') == 'dev'
			|| Environment::getEnv('SS_ENVIRONMENT_TYPE') == 'test'
		) {
			// Check if fixture override exists
			if(file_exists('../app/seeds/' . $this->fixtureFileName)) {
				$fixtureFile = 'app/seeds/' . $this->fixtureFileName;
				$pathResolver = '../';
			// Check if default fixture exists
			} else if(file_exists(__DIR__ . '/../Fixtures/' . $this->fixtureFileName)) {
				$fixtureFile = __DIR__ . '/../Fixtures/' . $this->fixtureFileName;
			} else {
				echo 'No fixture file found. Create an "app/seeds/$fixtureFileName"';
				return;
			}
			// If running the parent SeederBuildTask
			if($this->fixtureFileName == 'DatabaseSeeder.yml'){
				$parser = new Parser();
	            $contents = file_get_contents($pathResolver . $fixtureFile);
	            $fixtureContent = $parser->parse($contents);
	            // Run enabled seeder classes
	            foreach($fixtureContent as $seederClass => $options){
	            	if($options['enabled']){
	            		echo 'Running ' . $seederClass . '. <br>';
	            		Injector::inst()->create($seederClass)->run($request);
	            		echo '<br>';
	            	}
	            }
			} else {
				// Run singular seeder
				$fixture = YamlFixture::create($fixtureFile);
				$fixture->writeInto(new SeederFixtureFactory());
			}
		} else {
			echo 'Must run in development or test environment';
		}
	}
} 