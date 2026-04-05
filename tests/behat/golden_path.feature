@mod @mod_elediacheckin
Feature: Teacher creates an eLeDia Check-in activity and draws a next question
  In order to run opening and closing rounds in class
  As a teacher
  I need to create a Check-in activity and move through the question pool

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | teacher1 | Teacher   | One      | teacher1@test.com |
      | student1 | Student   | One      | student1@test.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |

  @javascript
  Scenario: Teacher creates a Check-in activity with own questions
    When I am on the "Course 1" course page logged in as teacher1
    And I turn editing mode on
    And I add an "eLeDia Check-in" to section "1" and I fill the form with:
      | Name                | My Check-in                           |
      | Eigene Fragen       | Wie geht es dir?\nWas hast du gelernt? |
      | Modus eigene Fragen | Nur eigene Fragen                      |
    Then I should see "My Check-in" in the "region-main" "region"

  @javascript
  Scenario: Student opens the activity and sees a question from the pool
    Given the following "activity" exists:
      | activity         | elediacheckin              |
      | course           | C1                         |
      | name             | Daily Check-in             |
      | ownquestions     | Wie ist deine Energie?     |
      | ownquestionsmode | 1                          |
    When I am on the "Daily Check-in" "elediacheckin activity" page logged in as student1
    Then I should see "Wie ist deine Energie?"

  @javascript
  Scenario: Student can draw the next question
    Given the following "activity" exists:
      | activity         | elediacheckin                  |
      | course           | C1                             |
      | name             | Next Round                     |
      | ownquestions     | Frage A\nFrage B\nFrage C      |
      | ownquestionsmode | 1                              |
    When I am on the "Next Round" "elediacheckin activity" page logged in as student1
    And I click on "Nächste Frage" "button"
    Then I should see "Frage" in the "region-main" "region"
