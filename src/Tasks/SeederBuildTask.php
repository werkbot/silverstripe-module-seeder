<?php
/**/
namespace Werkbot\Seeder;
/**/
use SilverStripe\Dev\BuildTask;
use SilverStripe\Dev\YamlFixture;
use SilverStripe\Core\Environment;
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
			// Get the directory of the current seeder build task
			$seederTaskDirectory = (new \ReflectionClass(get_class($this)))->getFilename();
			$seederTaskDirectory = explode('/', $seederTaskDirectory);
			// If the directory is not split by forward slashes, split by Windows back slashes
			if(count($seederTaskDirectory) == 1){
				$seederTaskDirectory = explode('\\', join($seederTaskDirectory));
			}
			array_pop($seederTaskDirectory);
			$seederTaskDirectory = join('/', $seederTaskDirectory);

			// Check in the site if a fixture override exists
			if(file_exists('../app/seeds/' . $this->fixtureFileName)) {
				$fixtureFile = 'app/seeds/' . $this->fixtureFileName;
				$pathResolver = '../';
			} else if(file_exists('../app/_config/' . $this->fixtureFileName)) {
				$fixtureFile = 'app/_config/' . $this->fixtureFileName;
				$pathResolver = '../';
			} else if(file_exists('../mysite/seeds/' . $this->fixtureFileName)) {
				$fixtureFile = 'mysite/seeds/' . $this->fixtureFileName;
				$pathResolver = '../';
			} else if(file_exists('../mysite/_config/' . $this->fixtureFileName)) {
				$fixtureFile = 'mysite/_config/' . $this->fixtureFileName;
				$pathResolver = '../';

			// Check in the current seeder's module if a fixture override exists
			} else if(file_exists($seederTaskDirectory . '/../../seeds/' . $this->fixtureFileName)) {
				$fixtureFile = $seederTaskDirectory . '/../../seeds/' . $this->fixtureFileName;
			} else if(file_exists($seederTaskDirectory . '/../../_config/' . $this->fixtureFileName)) {
				$fixtureFile = $seederTaskDirectory . '/../../_config/' . $this->fixtureFileName;

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
				if(method_exists($this, 'createObjectCallback')){
					$createObjectCallback = function($obj, $class, $data){
						call_user_func([$this, 'createObjectCallback'], $obj, $class, $data);
					};
					$fixture->writeInto(new SeederFixtureFactory($createObjectCallback));
				} else {
					$fixture->writeInto(new SeederFixtureFactory());
				}
			}
		} else {
			echo 'Must run in development or test environment';
		}
	}
}
