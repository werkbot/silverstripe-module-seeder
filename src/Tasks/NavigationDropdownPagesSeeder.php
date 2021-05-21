<?php
/**/
namespace Werkbot\Seeder;
/**/
use SilverStripe\Dev\BuildTask;
/**/
class NavigationDropdownPagesSeeder extends BuildTask {
	/**/
	protected $title = 'Generate Navigation Dropdown Pages';
	protected $description = 'Generate navigation dropdown pages. This is an often repeated process when testing sites. This sets up navigation dropdown pages automatically.';
	protected $enabled = true;
	/**/
	public function run($request)
	{
	}
} 