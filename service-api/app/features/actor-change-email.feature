@actor @changeEmail
Feature: Change email
  As a user
  I want to be able to change my account email address
  So that I receive emails from the service to my new email address

  Background:
    Given I am a user of the lpa application
    And I am currently signed in

  @acceptance @integration
  Scenario: The user cannot change their email address because their password is incorrect
    Given I am on the change email page
    When I request to change my email with an incorrect password
    Then I should be told that I could not change my email because my password is incorrect

  @acceptance @integration
  Scenario: The user cannot request to change their email address because the email chosen belongs to another user on the service
    Given I am on the change email page
    When I request to change my email to an email address that is taken by another user on the service
    Then I should be told that I could not change my email as their was a problem with the request

  @acceptance @integration
  Scenario: The user cannot request to change their email address because another user has requested to change their email to this and token has not expired
    Given I am on the change email page
    When I request to change my email to an email address that another user has requested to change their email to but their token has not expired
    Then I should be told that I could not change my email as their was a problem with the request

  @acceptance @integration
  Scenario: The user can request to change their email address that another user has requested to change their email to this but their token has expired
    Given I am on the change email page
    When I request to change my email to an email address that another user has requested to change their email to but their token has expired
    Then I should be sent an email to both my current and new email
    And I should be logged out and told that my request was successful
