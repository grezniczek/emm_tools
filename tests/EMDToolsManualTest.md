# External Module Developer Tools - Manual Testing Procedure

Version 2 - 2022-05-09

## Prerequisites

- REDCap v12.3.0 or newer.
- _External Module Developer Tools_ is enabled on the system.
- A test project with a non-super user account having project design rights.

## Test Procedure

1. Using an admin account, configure the module **on the system level**:
   - Turn on _Enable module on all projects by default_.
   - Turn on **all options** (except _Debug mode_).
   - Leave the _Timeout_ empty.
1. As a super user, go to the test project's _External Modules_ page and verify the following:
   - Next to the _Enable a module_ button there is an additional button labeled _Control Center > Module Manager_.
   - The _Reveal in Control Center_ link is shown for each currently endabled module (there should at least be the _External Module Management Tools_).
   - The _Query in Database Query Tool_ link is shown for each currently enabled module.
1. Click the _Control Center > Module Manager_ button and verify the following:
   - The link took you to the _External Modules - Module Manager_ page.
   - Above the _Modules Currently Available on this System_ list there is a button labeled _Return to project X_ (where X is the project id of the test project).
1. Click the _Return to project_ button and verify the following:
   - You have been returned to the _External Modules - Project Module Manager_ page of the test project.
1. Click on the _Reveal in Control Center_ link of the _External Module Developer Tools_ entry and verify the following:
   - The link took you to the _External Modules - Module Manager_ page.
   - "External Module Developer Tools" has been pre-entered in the search box.
   - Only the _External Module Developer Tools_ entry is shown (or any other module matching the search text).
   - Above the _Modules Currently Available on this System_ list there is a button labeled _Return to project X_ (where X is the project id of the test project).
1. Return to the test project by clicking the _Return to project_ button.
1. Click on the _Query in Database Query Tool_ link of the _External Module Developer Tools_ entry and verify the following:
   - You have been taken to the _Database Query Tool_ page.
   - A query matching the following has been executed:  
     `select * from redcap_external_module_settings where external_module_id = ? and project_id = ?`
1. Access the test project using the non-super user account with project design rights.
1. Click the _External Modules_ link in the main menu and verify the following:
   - The _External Module Developer Tools_ external module is **not** showing in the list.
   - The button or links to the Control Center pages or the _Database Query Tool_ are not shown.

Done.

## Reporting Errors

Before reporting errors:

- Make sure there is no interference with any other external module by turning off all others and repeating the tests.
- Check if you are using the latest version of the module. If not, see if updating fixes the issue.

To report an issue:

- Please report errors by opening an issue on [GitHub](https://github.com/grezniczek/emm_tools/issues) or on the community site (please tag @gunther.rezniczek).
- Include essential details about your REDCap installation such as **version** and platform (operating system, PHP version).
- If the problem occurs only in conjunction with another external module, please provide its details (you may also want to report the issue to that module's authors).
