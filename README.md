# External Module Management Tools

A REDCap external module providing enhancements for EM management.

![#f03c15](https://placehold.it/30x08/f03c15/000000?text=+) **Feature and pull requests** (against _master_) are welcome!

## Requirements

- REDCAP 9.5.0 or newer (tested with REDCap 9.7.2).

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

## Changelog

Version | Description
------- | --------------------
v1.0.0  | Initial release.
