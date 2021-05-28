<?php
/**/
namespace Werkbot\Seeder;
/**/
class SocialLinksSeeder extends SeederBuildTask {
	/**/
	protected $title = 'Generate Default Social Links';
	protected $description = 'Generate default social links. This is an often repeated process when testing sites. This sets up default social links automatically.';
	protected $enabled = true;
	/**/
	protected $fixtureFileName = 'SocialLinks.yml';
} 