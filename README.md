# External Module Management Tools

A REDCap external module providing enhancements for EM management.

**Feature and pull requests** (against _master_) are welcome!

## Requirements

- REDCAP 9.5.0 or newer (tested with REDCap 9.7.7, 9.8.2).
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
- **Module config query in MySQL Simple Admin** - Adds shortcut links for individual modules on the _Module Manager_ pages, which open the _MySQL Simple Admin_ external module in a new browser tab (automatically performing a query for the module's settings in the current context).
- **Record data query link** - Adds a shortcut link for to the _Record Actions_ menu on the _Record Home Page_ that opens the _MySQL Simple Admin_ external module in a new browser tab, automatically performing a query for the record in the _redcap_data_ table.
- **Record log query link** - Adds a shortcut link for to the _Record Actions_ menu on the _Record Home Page_ that opens the _MySQL Simple Admin_ external module in a new browser tab, automatically performing a query for the record in the appropriate _redcap_log_event_ table.

## Testing

Instructions for testing the module can be found [here](?prefix=emm_tools&page=tests/EMMToolsManualTest.md).

## Changelog

Version | Description
------- | --------------------
v1.2.0  | Adds a link to the record actions that queries the record's log with MySQL Simple Admin.
v1.1.6  | Finetuning of SQL sent to MySQL Simple Admin.
v1.1.5  | Adds a link to the record actions that queries the record with MySQL Simple Admin.
v1.1.4  | Module config query now supports system context.
v1.1.3  | Fix a bug that would emerge with the release of EM framework v5.
v1.1.2  | Minor bug fix.
v1.1.1  | Minor enhancements.<br>Add instructions for testing the module.
v1.1.0  | New feature: Query module config in MySQL Simple Admin.
v1.0.1  | Bug fixes.
v1.0.0  | Initial release.
