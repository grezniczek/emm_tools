# External Module Developer Tools

A REDCap external module providing tools and enhancements with regard for EM development.

**Feature and pull requests** (against _master_) are welcome!

## Requirements

- REDCAP 12.5.9 or newer (Framework Version 11).

## Installation

Automatic installation:

- Install this module from the REDCap External Module Repository and enable it.

Manual installation:

- Clone this repo into `<redcap-root>/modules/emm_tools_v<version-number>`.
- Go to _Control Center > Technical / Developer Tools > External Modules_ and enable 'External Module Developer Tools'.

## Configuration and Effects

Make sure to **enable the module for all projects** (or for specific projects, e.g. during development). In any case, this module will be invisible to non-admin users. To use all features, at least _Access to all projects and data with maximum user privileges_ and _Access to Control Center dashboards_ are required.

Features provided are:

- **Module Manager Shortcut** - Adds a shortcut link to 'Control Center > External Modules' on the _Project Module Manager_ page.
  ![Screensnip: Module Manager Shortcut](images/module_manager_shortcut.png)
- **Module System Configuration Shortcut** - Adds shortcut links for individual modules on the _Project Module Manager_ page, linking to the _Module Manager_ with search set to the respective module's name.
  ![Screensnip: Module System Configuration Shortcut](images/reveal_module_shortcut.png)
- Both provide a 'Return to project' link is displayed on the _Module Manager_ page.
  ![Screensnip: Return to Project Shortcut](images/return_to_project.png)
- **Module config query in Database Query Tool** - Adds shortcut links for individual modules on the _Module Manager_ pages, which open the _Database Query Tool_ in a new browser tab (automatically performing a query for the module's settings in the current context).
- **Record data query link** - Adds a shortcut link for to the _Record Actions_ menu on the _Record Home Page_ that opens the _Database Query Tool_ in a new browser tab, automatically performing a query for the record in the _redcap_data_ table.
  ![Screensnip: Record Action Menu](images/record-actions.png)
- **Record log query link** - Adds a shortcut link for to the _Record Actions_ menu on the _Record Home Page_ that opens the _Database Query Tool_ in a new browser tab, automatically performing a query for the record in the appropriate _redcap_log_event_ table.
- **Project Links**  
  ![Screensnip: Project-Context Menu Links](images/project-menu-links.png)
  - **Project Object Inspector** - A plugin page that prints the Project object.
  ![Screensnip: Project Object Inspector](images/project-object.png)
  - **Show Field Annotations** - When turned on (via a link in the External Modules section of REDCap's main project-context menu), field annotations will be displayed on data entry forms and survey pages in the respective field's label. In case the field is embedded, the annotations will be appended to the embedding container.  
  Additionally, the Online Designer overview will have EMDT badges that show the field annotations when hovered over, and clicking on field names will copy them to the clipboard (when supported by the browser). 
  **Note**: Pages have to be reloaded after switching on/off field annotations.
  ![Screensnip: Field Annotations, Data Entry Form](images/field-annotations.png)
  ![Screensnip: Field Annotations, Designer](images/designer-annotations.png)
- **Purging of External Module settings** - Adds a link to the _External Module Manager_ pages (project and control center) that allows purging of a module's settings in that context. All data will be purged from the _redcap_external_module_settings_ table, except for `enabled` and `version`.

## Changelog

Version | Description
------- | --------------------
v1.9.2  | Bugfix (Field Annotations Toggle)
v1.9.1  | Bugfix + logging of settings deletion.
v1.9.0  | All features are now always on - no more configuration.<br>New feature to purge the project or system settings of any external module.
v1.8.0  | REDCap v12.3 Database Query Tool integration; Field Annotation enhancements.
v1.7.0  | Bugfix: Injection of field annotations in REDCap v12+.
v1.6.0  | Compatibility fixes with v12.0.7.<br>Major bug fix for querying record logs.
v1.5.3  | Bugfix: CSRF token exception in case of "remote control" of MySQL Simple Admin.
v1.5.2  | Fix of the v1.5.1 fix. Thanks @ Mark McEver!
v1.5.1  | Security fix: JS injection via a URL parameter.
v1.5.0  | Supports CSRF tokens in AJAX requests (Framework v8).<br>Prevents some false positives in PSALM testing.
v1.4.0  | Module renamed to _External Module **Developer** Tools_.<br>New feature: Show Field Annotations.<br>New feature: Project Object as JSON.
v1.3.3  | Bug fix: USERID check done right (avoids exceptions in some cases).
v1.3.2  | Bug fix: SUPER_USER check done right (avoids exceptions in some cases).
v1.3.1  | Bug fix: Project was not fully loaded before display.
v1.3.0  | New feature: Project Object Inspector
v1.2.1  | Bug fix: Record log querying could not be turned on/off separately; requires REDCap 10.1.0 (new granular admin privileges).
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
