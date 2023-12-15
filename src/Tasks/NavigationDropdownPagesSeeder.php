<?php

namespace Werkbot\Seeder\Tasks;

class NavigationDropdownPagesSeeder extends SeederBuildTask
{
  protected $title = 'Generate Navigation Dropdown Pages';
  protected $description = 'Generate navigation dropdown pages. This is an often repeated process when testing sites. This sets up navigation dropdown pages automatically.';
  protected $enabled = true;

  protected $fixtureFileName = 'NavigationDropdownPages.yml';
}

