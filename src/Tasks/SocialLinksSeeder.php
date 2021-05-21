<?php
/**/
namespace Werkbot\Seeder;
/**/
use SilverStripe\Dev\BuildTask;
/**/
class SocialLinksSeeder extends BuildTask {
	/**/
	protected $title = 'Generate Default Social Links';
	protected $description = 'Generate default social links. This is an often repeated process when testing sites. This sets up default social links automatically.';
	protected $enabled = true;
	/**/
	public function run($request)
	{
	}
} 