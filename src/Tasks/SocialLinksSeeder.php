<?php
/**/
namespace Werkbot\Seeder;
/**/
use SilverStripe\Dev\BuildTask;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\YamlFixture;
/**/
class SocialLinksSeeder extends BuildTask {
	/**/
	protected $title = 'Generate Default Social Links';
	protected $description = 'Generate default social links. This is an often repeated process when testing sites. This sets up default social links automatically.';
	protected $enabled = true;
	/**/
	public function run($request)
	{
		if(Environment::getEnv('SS_ENVIRONMENT_TYPE') == 'dev'){
			if(file_exists('seeds/SocialLinks.yml')){
				$fixtureFile = 'app/seeds/SocialLinks.yml';
			} else {
				$fixtureFile = __DIR__ . '/../Fixtures/SocialLinks.yml';
			}
			$fixture = YamlFixture::create($fixtureFile);
			$fixture->writeInto(new SeederFixtureFactory());
		} else {
			echo 'Must run in development environment';
		}
	}
} 