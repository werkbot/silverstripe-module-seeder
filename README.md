# Seeder Module
A development database seeder.
## Requirements
- Silverstripe ^4.0

Update composer.json:

	"repositories": [
		...
		{
	        "type": "vcs",
	        "url": "https://github.com/werkbot/silverstripe-module-seeder.git"
	    }
	],
	"require": [
		...
		"werkbot/werkbot-seeder": "*"
	]

Run `composer update`

Run `/dev/build` on your site.

You can now run seeder tasks in `/dev/Tasks` while in development mode.

### Overriding Seeders
- Create an `app/seeds` directory
- Override the seeder fixture https://docs.silverstripe.org/en/4/developer_guides/testing/fixtures/
- Example NavigationDropdownPages.yml:

		Page:
		  DefaultParentPage:
		    Title: 'Parent Page'
		    SettingNavigationType: 'default'
		  DefaultChildPageOne:
		    Title: 'Child Page One'
		    Parent: =>Page.DefaultParentPage

#### Available Seeders Fixtures
- NavigationDropdownPages.yml
- SocialLinks.yml