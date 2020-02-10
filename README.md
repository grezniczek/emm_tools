# External Module Management Tools

A REDCap external module providing enhancements for EM management.

## Requirements

- REDCAP 9.5.0 or newer (tested with REDCap 9.7.2).

## Installation

Automatic installation:

- Install this module from the REDCap External Module Repository and enable it.

Manual installation:

- Clone this repo into `<redcap-root>/modules/emm_tools_v<version-number>`.
- Go to _Control Center > Technical / Developer Tools > External Modules_ and enable 'External Module Management Tools'.

## Configuration and Effects

Enable the features you want in this module's **system configuration**. Features provided are:

- **Module Manager Shortcut** - Adds a shortcut link to 'Control Center > External Modules' on the _Project Module Manager_ page.
- **Module System Configuration Shortcut** - Adds shortcut links for individual modules on the _Project Module Manager_ page, linking to the _Module Manager_ with search set to the respective module's name. Furthermore, a 'Return to project' link is displayed on the _Module Manager_ page.

## Changelog

Version | Description
------- | --------------------
v1.0.0  | Initial release.
