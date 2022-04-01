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
class SeederBuildTask extends BuildTask
{
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
        // Only run in a dev or test environment
        if (Environment::getEnv('SS_ENVIRONMENT_TYPE') == 'dev'
            || Environment::getEnv('SS_ENVIRONMENT_TYPE') == 'test'
        ) {
            // Get site root. Set to the current directory if running through command line.
            $siteRoot = $_SERVER['DOCUMENT_ROOT'] ? $_SERVER['DOCUMENT_ROOT'] . '/..' : getcwd();

            // Get the directory of the current seeder build task
            $seederTaskDirectory = (new \ReflectionClass(get_class($this)))->getFilename();

            // Split path to seeder task into individual directories. Used to find the seeder project directory.
            $seederTaskDirectories = explode('/', $seederTaskDirectory);
            // If the directory is not split by forward slashes, split by Windows back slashes
            if (count($seederTaskDirectories) == 1) {
                $seederTaskDirectories = explode('\\', join($seederTaskDirectories));
            }
            array_pop($seederTaskDirectories);

            // Find the root directory of the project the seeder task lives in. This could be the site or a module.
            $seederTaskProjectDirectories = [];
            foreach ($seederTaskDirectories as $directoryIndex => $directoryName) {
                array_push($seederTaskProjectDirectories, $directoryName);
                if (preg_match('/app|mysite|vendor/', $directoryName)) {
                    array_push($seederTaskProjectDirectories, $seederTaskDirectories[$directoryIndex + 1]);
                    if ($directoryName == 'vendor') {
                        array_push($seederTaskProjectDirectories, $seederTaskDirectories[$directoryIndex + 2]);
                    }
                    break;
                }
            }
            $seederTaskProjectDirectory = join('/', $seederTaskProjectDirectories);

            // Check in the site if a fixture override exists
            if (file_exists($siteRoot . '/app/seeds/' . $this->fixtureFileName)) {
                $fixtureFile = $siteRoot . '/app/seeds/' . $this->fixtureFileName;
            } else if (file_exists($siteRoot . '/mysite/seeds/' . $this->fixtureFileName)) {
                $fixtureFile = $siteRoot . '/mysite/seeds/' . $this->fixtureFileName;

            // Check if module fixture exists
            } else if (file_exists($seederTaskProjectDirectory . '/seeds/' . $this->fixtureFileName)) {
                $fixtureFile = $seederTaskProjectDirectory . '/seeds/' . $this->fixtureFileName;

            // Check if default fixture exists
            } else if (file_exists($seederTaskProjectDirectory . '/src/Fixtures/' . $this->fixtureFileName)) {
                $fixtureFile = $seederTaskProjectDirectory . '/src/Fixtures/' . $this->fixtureFileName;
            } else {
                echo 'No fixture file found. Create an "app/seeds/$fixtureFileName"';
                return;
            }

            // If running the parent SeederBuildTask
            if ($this->fixtureFileName == 'DatabaseSeeder.yml') {
                $parser = new Parser();
                $contents = file_get_contents($fixtureFile);
                $fixtureContent = $parser->parse($contents);
                // Run enabled seeder classes
                foreach ($fixtureContent as $seederClass => $options) {
                    if ($options['enabled']) {
                        echo 'Running ' . $seederClass . '. <br>';
                        Injector::inst()->create($seederClass)->run($request);
                        echo '<br>';
                    }
                }
            } else {
                // Run singular seeder
                $fixture = YamlFixture::create($fixtureFile);
                if (method_exists($this, 'createObjectCallback')) {
                    $createObjectCallback = function ($obj, $class, $data) {
                        call_user_func([$this, 'createObjectCallback'], $obj, $class, $data);
                    };
                    $fixture->writeInto(new SeederFixtureFactory($createObjectCallback));
                } else {
                    $fixture->writeInto(new SeederFixtureFactory(null));
                }
            }
        } else {
            echo 'Must run in development or test environment';
        }
    }
}
