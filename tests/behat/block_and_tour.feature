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

  Scenario: Bundled teacher tour is installed in the system
    # The custom step imports all bundled tours and verifies the teacher tour
    # exists in tool_usertours_tours with the correct name. No browser
    # interaction is needed — the assertion lives in the step itself.
    Given the elediacheckin bundled tours are installed

  @javascript
  Scenario: Teacher activity view renders for enrolled teacher
    When I am on the "Block Test Check-in" "elediacheckin activity" page logged in as teacher1
    Then I should see "Block-Frage"
