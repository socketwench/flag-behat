Feature: Test flag module

  @api
  Scenario: Test flag admin
    Given I am logged in as a user with the "administrator" role
    When I visit "admin/structure"
    Then I should see "Flags"

  @api
  Scenario: Test flag creation
    Given I am logged in as a user with the "administrator" role
    When I visit "admin/structure/flags/add"
    And I select the radio button "NodesNodes are a Drupal site's primary content." with the id "edit-type-node"
    And I press the "Continue" button
    And for "edit-title" I enter "Test Flag"
    And for "edit-name" I enter "test_flag"
    And I press the "Save flag" button
    Then I should see "Test Flag"

  @api
  Scenario: Test flag deletion
    Given I am logged in as a user with the "administrator" role
    When I visit "admin/structure/flags/manage/test_flag/delete"
    And I press the "edit-submit" button
    Then I should see "Flag Test Flag has been deleted."


  @api
  Scenario: Test Flag Use
    Given I have a flag "Stuff"
    And I am logged in as a user with the "administrator" role
    And I am viewing an "Article" node:
      | title | My article with fields! |
      | body  | A placeholder           |
    Then I should see "Flag this item"
