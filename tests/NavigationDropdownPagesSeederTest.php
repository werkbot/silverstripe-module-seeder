<?php
/**/
namespace Werkbot\Seeder;
/**/
use Page;
use SilverStripe\Dev\SapphireTest;
/*
    Run with:
    vendor/bin/phpunit vendor/werkbot/werkbot-seeder/tests/NavigationDropdownPagesSeederTest.php
*/
class NavigationDropdownPagesSeederTest extends SapphireTest
{
    protected static $fixture_file = '../src/Fixtures/NavigationDropdownPages.yml';

    public function testNavigationDropdownPagesFixture()
    {
        $expectedTitles = [
          'DefaultParentPage' => 'Parent Page',
          'DefaultChildPageOne' => 'Child Page One',
          'DefaultChildPageTwo' => 'Child Page Two',
          'DefaultChildPageThree' => 'Child Page Three',
        ];
        /**/
        foreach ($expectedTitles as $fixture => $page) {
            $obj = $this->objFromFixture(Page::class, $fixture);
            $this->assertEquals($page, $obj->Title);
        }
    }
}
