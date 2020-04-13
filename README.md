# External Module Management Tools

A REDCap external module providing enhancements for EM management.

**Feature and pull requests** (against _master_) are welcome!

## Requirements

- REDCAP 9.5.0 or newer (tested with REDCap 9.7.7).
- MySQL Simple Admin external module (for certain features).

## Installation

Automatic installation:

- Install this module from the REDCap External Module Repository and enable it.

Manual installation:

- Clone this repo into `<redcap-root>/modules/emm_tools_v<version-number>`.
- Go to _Control Center > Technical / Developer Tools > External Modules_ and enable 'External Module Management Tools'.

## Configuration and Effects

Enable the features you want in this module's **system configuration**. Make sure to **enable the module for all projects** (or for specific projects, e.g. during development). In any case, this module will be invisible to non-admin users.

Features provided are:

- **Module Manager Shortcut** - Adds a shortcut link to 'Control Center > External Modules' on the _Project Module Manager_ page.
  ![Screensnip: Module Manager Shortcut](images/module_manager_shortcut.png)
- **Module System Configuration Shortcut** - Adds shortcut links for individual modules on the _Project Module Manager_ page, linking to the _Module Manager_ with search set to the respective module's name.
  ![Screensnip: Module System Configuration Shortcut](images/reveal_module_shortcut.png)
- Both provide a 'Return to project' link is displayed on the _Module Manager_ page.
  ![Screensnip: Return to Project Shortcut](images/return_to_project.png)
- **Module config query in MySQL Simple Admin** - Adds shortcut links for individual modules on the _Project Module Manager_ page, which open the _MySQL Simple Admin_ external module in a new browser tab (automatically performing a query for the module's settings in the current project's context).

## Testing

Instructions for testing the module can be found [here](?prefix=emm_tools&page=tests/EMMToolsManualTest.md).

## Changelog

Version | Description
------- | --------------------
v1.1.2  | Minor bug fix.
v1.1.1  | Minor enhancements.<br>Add instructions for testing the module.
v1.1.0  | New feature: Query module config in MySQL Simple Admin
v1.0.1  | Bug fixes.
v1.0.0  | Initial release.
