@mod @mod_elediacheckin
Feature: Admin dashboard for the content distribution subsystem
  In order to audit which content bundle is live
  As a site administrator
  I need to see the sync log and the selected content source on the settings page

  @javascript
  Scenario: Admin opens the plugin settings page and sees the dashboard panel
    When I log in as "admin"
    And I navigate to "Plugins > Activity modules > eLeDia Check-In" in site administration
    Then I should see "Active content source"
    And I should see "Sync status"

  @javascript
  Scenario: Admin sees the block health widget when the companion block is installed
    When I log in as "admin"
    And I navigate to "Plugins > Activity modules > eLeDia Check-In" in site administration
    Then I should see "Check-In block"

  @javascript
  Scenario: Admin can change the content source and save
    When I log in as "admin"
    And I navigate to "Plugins > Activity modules > eLeDia Check-In" in site administration
    And I set the field "Active content source" to "Bundled default questions"
    And I press "Save changes"
    Then I should see "Changes saved"
