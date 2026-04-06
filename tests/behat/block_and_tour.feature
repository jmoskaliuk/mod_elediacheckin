@mod @mod_elediacheckin
Feature: Companion block and user tour integration
  In order to expose Check-in content in dashboards
  As a teacher and site administrator
  I need the eLeDia Check-in block to render and the user tour to trigger on the settings page

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | teacher1 | Teacher   | One      | teacher1@test.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activity" exists:
      | activity         | elediacheckin             |
      | course           | C1                        |
      | name             | Block Test Check-in       |
      | ownquestions     | Block-Frage               |
      | ownquestionsmode | 1                         |

  @javascript
  Scenario: Teacher adds the Check-in block to a course
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Check-in" block
    Then "Check-in" "block" should exist

  @javascript
  Scenario: Admin settings user tour auto-starts on first visit
    When I log in as "admin"
    And I navigate to "Plugins > Activity modules > eLeDia Check-In" in site administration
    Then I should see "Plugin settings at a glance"

  @javascript
  Scenario: Teacher activity user tour auto-starts on first visit to view.php
    When I am on the "Block Test Check-in" "elediacheckin activity" page logged in as teacher1
    Then I should see "Welcome to Check-in"
