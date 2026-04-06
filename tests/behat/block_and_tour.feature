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
  Scenario: Bundled teacher tour is installed in the system
    When I log in as "admin"
    And I visit "/admin/tool/usertours/index.php"
    Then I should see "Check-in for teachers"

  @javascript
  Scenario: Teacher activity view renders for enrolled teacher
    When I am on the "Block Test Check-in" "elediacheckin activity" page logged in as teacher1
    Then I should see "Block-Frage"
