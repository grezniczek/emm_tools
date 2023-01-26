<?php namespace DE\RUB\EMDToolsExternalModule;

use Exception;
use ExternalModules\AbstractExternalModule;
use InvalidArgumentException;

require_once "classes/User.php";

/**
 * Provides enhancements to the External Module Management pages.
 */
class EMDToolsExternalModule extends AbstractExternalModule {

    /**
     * EM Framework (tooling support)
     * @var \ExternalModules\Framework
     */
    private $fw;

    private $js_injected = false;

    function __construct() {
        parent::__construct();
        $this->fw = $this->framework;
    }

    #region Hooks

    function redcap_data_entry_form ($project_id, $record = NULL, $instrument, $event_id, $group_id = NULL, $repeat_instance = 1) {
        if ($this->getProjectSetting("enable-fieldannotations") == true && $this->getProjectSetting("show-fieldannotations") == true) {
            $this->insertFieldAnnotations($instrument);
        }
    }

    function redcap_survey_page ($project_id, $record = NULL, $instrument, $event_id, $group_id = NULL, $survey_hash, $response_id = NULL, $repeat_instance = 1) {
        if ($this->getProjectSetting("enable-fieldannotations") == true && $this->getProjectSetting("show-fieldannotations") == true) {
            $this->insertFieldAnnotations($instrument);
        }
    }

    function redcap_every_page_top($project_id = null) {
        $user = new User($this->fw, defined("USERID") ? USERID : null);

        if ($project_id != null && 
            $this->getProjectSetting("enable-fieldannotations") == true && 
            $this->getProjectSetting("show-fieldannotations") == true &&
            PageInfo::IsDesigner()
           ) {
            $this->insertFieldAnnotations($_GET["page"], true);
        }

        // Hide this module from users who cannot install EMs.
        if (!($user->canAccessExternalModuleInstall())) {
            if (PageInfo::IsProjectExternalModulesManager()) {
                ?>
                <script>
                    $(function() {
                        $('tr[data-module="<?=$this->PREFIX?>"]').remove();
                    })
                </script>
                <?php
            }
        }

        // At least super user who can access admin dashboard rights or a system config user are required for this module to do anything useful.
        // Thus, quit here if this condition is not met.
        if (!($user->isSuperUser() || $user->canAccessSystemConfig())) {
            return;
        }

        // Module Manager Shortcut
        if ($user->canAccessSystemConfig() || $user->canAccessAdminDashboards()) {
            if (PageInfo::IsProjectExternalModulesManager()) {
                $query_link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/manager/control_center.php?return-pid={$project_id}";
                ?>
                <script>
                    $(function(){
                        $('#external-modules-enable-modules-button').after('&nbsp;&nbsp;&nbsp;<a class="btn btn-light btn-sm" role="button" href="<?=$query_link?>"><i class="fas fa-sign-out-alt"></i> <?=$this->fw->tt("mmslink_label")?></a>')
                    })
                </script>
                <?php
            }
        }

        // Module Manager Reveal.
        if ($user->canAccessSystemConfig() || $user->canAccessAdminDashboards()) {
            if (PageInfo::IsProjectExternalModulesManager()) {
                $query_link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/manager/control_center.php?return-pid={$project_id}&reveal-module=";
                ?>
                <script>
                    $(function(){
                        $('#external-modules-enabled tr[data-module]').each(function() {
                            var tr = $(this)
                            var moduleName = tr.attr('data-module')
                            var link = $('<a href="<?=$query_link?>' + moduleName + '" style="margin-right:1em;"><i class="fas fa-cog" style="margin-right:2px;"></i> <?=$this->fw->tt("reveallink_label")?></a>')
                            var td = tr.find('td').first();
                            if (td.find('div.external-modules-byline').length) {
                                var div = td.find('div.external-modules-byline').first()
                                div.append(link)
                            }
                            else {
                                var div = $('<div class="external-modules-byline"></div>')
                                div.append(link)
                                link.css('display', 'block')
                                link.css('margin-top', '7px')
                                td.append(div)
                            }
                        })
                    })
                </script>
                <?php
            }
            else if (PageInfo::IsSystemExternalModulesManager() && PageInfo::HasGETParameter("reveal-module")) {
                $moduleName = json_encode(htmlentities($_GET["reveal-module"], ENT_QUOTES));
                $returnPid = PageInfo::SanitizeProjectID($_GET["return-pid"]);
                $triggerTimeout = $this->getSystemSetting("module-manager-reveal-timeout");
                if (!is_numeric($triggerTimeout)) $triggerTimeout = 50;
                $triggerTimeout = abs($triggerTimeout);
                ?>
                <script>
                    $(function() {
                        try {
                            var titleDiv = $('tr[data-module="' + <?=$moduleName?> + '"] td div.external-modules-title').first()
                            var title = titleDiv.text().trim().split(' - v')[0]
                            var search = $('#enabled-modules-search')
                            setTimeout(function() {
                                search.val(title)
                                search.trigger('keyup')
                            }, <?=$triggerTimeout?>)
                        }
                        catch {
                            console.error('EMDT: Failed to find module \'' + <?=$moduleName?> + '\'')
                        }
                        <?php if ($returnPid != null) {
                            $query_link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/manager/project.php?pid=" . $returnPid;
                            ?>
                            $('#external-modules-enabled').siblings('h4').before('<div style="margin-bottom:7px;"><a class="btn btn-light btn-sm" role="button" href="<?=$query_link?>"><i class="fas fa-sign-out-alt"></i> <?=$this->fw->tt("returnlink_label", $returnPid)?></a></div>')
                            <?php
                        }
                        ?>
                    })
                </script>
                <?php
            }
            else if (PageInfo::IsSystemExternalModulesManager() && PageInfo::HasGETParameter("return-pid")) {
                $returnPid = PageInfo::SanitizeProjectID($_GET["return-pid"]);
                if ($returnPid != null) {
                    $query_link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/manager/project.php?pid=" . $returnPid;
                    ?>
                    <script>
                        $(function() {
                            $('#external-modules-enabled').siblings('h4').before('<div style="margin-bottom:7px;"><a class="btn btn-light btn-sm" role="button" href="<?=$query_link?>"><i class="fas fa-sign-out-alt"></i> <?=$this->fw->tt("returnlink_label", $returnPid)?></a></div>')
                        })
                    </script>
                    <?php
                }
            }
        }
        
        // Database Query Tool Shortcuts
        if ($user->isSuperUser()) {
            if (PageInfo::IsProjectExternalModulesManager() || PageInfo::IsSystemExternalModulesManager() || PageInfo::IsDatabaseQueryTool()) {
                if (PageInfo::IsProjectExternalModulesManager()) {
                    $this->inject_js();
                    $query_link = APP_PATH_WEBROOT . "ControlCenter/database_query_tool.php?query-pid={$project_id}&module-prefix=";
                    ?>
                    <script>
                        $(function(){
                            $('#external-modules-enabled tr[data-module]').each(function() {
                                const tr = $(this);
                                const moduleName = tr.attr('data-module');
                                const queryLink = $('<a target="_blank" href="<?=$query_link?>' + moduleName + '" style="margin-right:1em;"></a>');
                                queryLink.html('<i class="fas fa-database" style="margin-right:2px;"></i> <?=js_escape($this->fw->tt("mysqllink_label"))?>');
                                const purgeLink = $('<a href="javascript:"></a>');
                                purgeLink.html('<i class="fas fa-database text-danger" style="margin-right:2px;"></i> <?=js_escape($this->fw->tt("mysqlpurge_project_label"))?>');
                                purgeLink.on('click', () => DE_RUB_EMDTools.purgeSettings(moduleName, <?=$project_id?>));
                                const td = tr.find('td').first();
                                if (td.find('div.external-modules-byline').length) {
                                    const div = td.find('div.external-modules-byline').first()
                                    div.append(queryLink)
                                    div.append(purgeLink)
                                }
                                else {
                                    const div = $('<div class="external-modules-byline"></div>')
                                    div.append(queryLink)
                                    div.append(purgeLink)
                                    queryLink.css('display', 'block')
                                    queryLink.css('margin-top', '7px')
                                    td.append(div)
                                }
                            })
                        })
                    </script>
                    <?php
                }
                else if (PageInfo::IsSystemExternalModulesManager()) {
                    $this->inject_js();
                    $query_link = APP_PATH_WEBROOT . "ControlCenter/database_query_tool.php?query-pid=0&module-prefix=";
                    ?>
                    <script>
                        $(function(){
                            $('#external-modules-enabled tr[data-module]').each(function() {
                                const tr = $(this);
                                const moduleName = tr.attr('data-module');
                                const queryLink = $('<a target="_blank" href="<?=$query_link?>' + moduleName + '" style="margin-right:1em;"><i class="fas fa-database" style="margin-right:2px;"></i></a>');
                                queryLink.html('<i class="fas fa-database" style="margin-right:2px;"></i> <?=js_escape($this->fw->tt("mysqllink_label"))?>')
                                const purgeLink = $('<a href="javascript:"></a>');
                                purgeLink.html('<i class="fas fa-database text-danger" style="margin-right:2px;"></i> <?=js_escape($this->fw->tt("mysqlpurge_cc_label"))?>');
                                purgeLink.on('click', () => DE_RUB_EMDTools.purgeSettings(moduleName, null));
                                const td = tr.find('td').first();
                                if (td.find('div.external-modules-byline').length) {
                                    const div = td.find('div.external-modules-byline').first();
                                    div.append(queryLink);
                                    div.append(purgeLink);
                                }
                                else {
                                    const div = $('<div class="external-modules-byline"></div>');
                                    div.append(queryLink);
                                    div.append(purgeLink);
                                    queryLink.css('display', 'block');
                                    queryLink.css('margin-top', '7px');
                                    td.append(div);
                                }
                            })
                        })
                    </script>
                    <?php
                }
                else if (PageInfo::IsDatabaseQueryTool()) {
                    $prefix = $_GET["module-prefix"];
                    $record = $_GET["query-record"];
                    $mode = $_GET["query-for"] == "data" ? "data" : "logs";
                    $pid = PageInfo::SanitizeProjectID($_GET["query-pid"]);
                    $pid_clause = $pid === 0 ? "project_id IS NULL" : "project_id = {$pid}";
                    $execute = false;
                    if ($prefix) {
                        $result = $this->fw->query("
                            select external_module_id 
                            from redcap_external_modules 
                            where directory_prefix = ?",
                            [ $prefix ]);
                        $module_id = ($result->fetch_assoc())["external_module_id"];
                        $query = "SELECT * FROM redcap_external_module_settings\n" . 
                                "WHERE external_module_id = {$module_id} -- {$prefix}\n" . 
                                "AND {$pid_clause}";
                        $execute = $module_id !== null;
                    }
                    else if ($record && $pid > 0) {
                        $record = db_escape($record);
                        if ($mode == "data") {
                            $query = "SELECT * FROM redcap_data\n WHERE `project_id` = {$pid} AND `record` = '{$record}'";
                        }
                        else if ($mode == "logs") {
                            $log_event_table = \REDCap::getLogEventTable($pid);
                            $query = "SELECT * FROM {$log_event_table}\n WHERE `project_id` = {$pid} AND `pk` = '{$record}'\n ORDER BY `log_event_id` DESC";
                        }
                        $execute = !empty($record);
                    }
                    if ($execute) {
                        // Insert EM Framework CSRF token. Note: Need to set for both names! Not clear why this is needed.
                        $token = $this->getCSRFToken();
                        ?>
                        <script>
                            $(function() {
                                $('#query').val(<?=json_encode($query)?>)
                                $('#form').append('<input type="hidden" name="redcap_external_module_csrf_token" value="<?=$token?>">')
                                $('#form').append('<input type="hidden" name="redcap_csrf_token" value="<?=$token?>">')
                                $('#form').submit()
                            })
                        </script>
                        <?php
                    }
                }
            }
        }

        // Query for record data.
        if ($user->isSuperUser()) {
            if (PageInfo::IsExistingRecordHomePage()) {
                $record_id = urlencode(strip_tags(label_decode(urldecode($_GET['id']))));
                $data_link = APP_PATH_WEBROOT . "ControlCenter/database_query_tool.php?query-pid={$project_id}&query-record={$record_id}&query-for=data";
                ?>
                <script>
                    $(function(){
                        var $ul = $('#recordActionDropdown')
                        $ul.append('<li class="ui-menu-item"><a href="<?=$data_link?>" target="_blank" style="display:block;" tabindex="-1" role="menuitem" class="ui-menu-item-wrapper"><span style="vertical-align:middle;color:#065499;"><i class="fas fa-database"></i> <?=$this->fw->tt("mysqllink_record_data")?></span></a></li>')
                    })
                </script>
                <?php
            }
            // Query for record logs.
            if (PageInfo::IsExistingRecordHomePage()) {
                $record_id = urlencode(strip_tags(label_decode(urldecode($_GET['id']))));
                $logs_link = APP_PATH_WEBROOT . "ControlCenter/database_query_tool.php?query-pid={$project_id}&query-record={$record_id}&query-for=logs";
                ?>
                <script>
                    $(function(){
                        var $ul = $('#recordActionDropdown')
                        $ul.append('<li class="ui-menu-item"><a href="<?=$logs_link?>" target="_blank" style="display:block;" tabindex="-1" role="menuitem" class="ui-menu-item-wrapper"><span style="vertical-align:middle;color:#065499;"><i class="fas fa-database" style="color:red;"></i> <?=$this->fw->tt("mysqllink_record_logs")?></span></a></li>')
                    })
                </script>
                <?php
            }
        }

        // Toggle Field Annotations
        if ($user->isSuperUser() && $project_id != null && $this->getProjectSetting("enable-fieldannotations") == true) {
            ?>
            <script>
                function EMDTToggleShowFieldAnnotations() {
                    var $state = $('#emdt-fieldannotations-state')
                    if ($state.attr('working') == '1') return
                    var state = $state.text()
                    $state.html('<i class="fas fa-spinner fa-spin"></i>').attr('working','1')
                    $.ajax({
                        url: '<?= $this->getUrl("toggle-fieldannotations.php") ?>',
                        data: { redcap_csrf_token: '<?= $this->getCSRFToken() ?>' },
                        method: 'POST',
                        success: function(data, textStatus, jqXHR) {
                            console.log('AJAX done: ', data, jqXHR)
                            state = data
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error('EMDT - Failed to toggle Show Field Annotations state: ' + errorThrown)
                        },
                        complete: function() {
                            $state.text(state)
                            $state.attr('working', '0')
                        } 
                    })
                }
            </script>
            <?php
        }
    }

    function redcap_module_link_check_display($project_id, $link) {
        $user = new User($this->fw, defined("USERID") ? USERID : null);
        $is_su = $user->isSuperUser();
        if ($project_id && $link["key"] == "project-object-inspector") {
            return $is_su ? $link : null;
        }
        if ($project_id && $link["key"] == "toggle-field-annotations") {
            if ($is_su) {
                $state = $this->getProjectSetting("show-fieldannotations") == true ? "on" : "off";
                $link["name"] = $this->tt("link_fieldannotations") . "<b id=\"emdt-fieldannotations-state\">" . $this->tt("link_fieldannotations_{$state}") . "</b>";
                return $link;
            }
        }
        return null;
    }

    function redcap_module_ajax($action, $payload, $project_id, $record, $instrument, $event_id, $repeat_instance, $survey_hash, $response_id, $survey_queue_hash, $page, $page_full, $user_id, $group_id) {
        $user = new User($this->fw, $user_id);
        switch($action) {
            case "purge-settings": {
                if ($user->isSuperUser()) {
                    $this->purge_settings($payload["module"], $payload["pid"]);
                }
                else {
                    throw new Exception("Insufficient rights.");
                }
            }
            break;
        }
        return null;
    }

    #endregion


    private function purge_settings($module_name, $pid) {
        // Get module id from name
        $result = $this->query("SELECT `external_module_id` FROM `redcap_external_modules` WHERE `directory_prefix` = ?", [
            $module_name
        ]);
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $module_id = $row["external_module_id"];
            if ($pid == null) {
                $delete_query = $this->fw->createQuery()->add(
                    "DELETE FROM `redcap_external_module_settings` 
                     WHERE `external_module_id` = ? AND ISNULL(`project_id`) AND `key` NOT IN ('version','enabled')",
                    [
                        $module_id
                    ]
                );
            }
            else {
                $delete_query = $this->fw->createQuery()->add(
                    "DELETE FROM `redcap_external_module_settings` 
                     WHERE `external_module_id` = ? AND `project_id` = ? AND `key` NOT IN ('version','enabled')",
                    [
                        $module_id,
                        $pid
                    ]
                );
            }
        }
        $sql = $delete_query->getSQL();
        $deleted = $delete_query->execute();
        if ($deleted) {
            $msg = "Deleted " . ($pid == null ? "system " : "") . "settings for module '$module_name' " . ($pid == null ? "" : "in project $pid ") . "using the External Module Developer Tools EM";
            \REDCap::logEvent($msg, "", $sql, null, null, $pid);
            $this->fw->log($msg);
        }
    }

    /**
     * Adds and initializes the JS support file
     */
    private function inject_js() {
        // Only do this once
        if ($this->js_injected) return;
        $config = [
            "debug" => $this->getSystemSetting("debug-mode") == true,
            "version" => $this->VERSION,
        ];
        $this->initializeJavascriptModuleObject();
        $this->fw->tt_transferToJavascriptModuleObject(["mysqlpurge_confirm_msg"]);
        $jsmo_name = $this->getJavascriptModuleObjectName();

        #region Scripts and HTML
        ?>
        <script src="<?php print $this->getUrl('js/emdt.js'); ?>"></script>
        <script>
            $(function() {
                DE_RUB_EMDTools.init(<?=json_encode($config)?>, <?=$jsmo_name?>);
            });
        </script>
        <?php
        $this->js_injected = true;
    }

    function toggleFieldAnnotation() {
        $state = $this->getProjectSetting("show-fieldannotations") == true;
        $state = !$state;
        $this->setProjectSetting("show-fieldannotations", $state);
        $state = $state ? "on" : "off";
        return $this->tt("link_fieldannotations_{$state}");
    }

    function insertFieldAnnotations($form, $designer = false) {
        global $Proj;
        foreach ($Proj->forms[$form]["fields"] as $field => $_) {
            $annotations = $Proj->metadata[$field]["misc"];
            print "<div class=\"emdt-field-annotation\" data-target=\"{$field}\" style=\"display:none;font-weight:normal;padding:0.5em;margin-top:0.5em;background-color:#fafafa;\"><code style=\"white-space:pre;margin-top:0.5em;\">{$annotations}</code></div>\n";
        }
        ?>
        <style>
            .copy-field-name { 
                cursor: hand !important; 
            }
        </style>
        <script>
            /**
             * Copies a string to the clipboard (fallback method for older browsers)
             * @param {string} text
             */
            function EMMTools_fallbackCopyTextToClipboard(text) {
                var textArea = document.createElement("textarea");
                textArea.value = text;
                // Avoid scrolling to bottom
                textArea.style.top = "0";
                textArea.style.left = "0";
                textArea.style.position = "fixed";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                } catch {
                    error('Failed to copy text to clipboard.')
                }
                document.body.removeChild(textArea);
            }
            /**
             * Copies a string to the clipboard (supported in modern browsers)
             * @param {string} text
             * @returns
             */
            function EMMTools_copyTextToClipboard(text) {
                if (!navigator.clipboard) {
                    EMMTools_fallbackCopyTextToClipboard(text);
                    return;
                }
                navigator.clipboard.writeText(text).catch(function() {
                    error('Failed to copy text to clipboard.')
                })
            }
            // EMM Tools - Append Field Annotations
            function EMMTools_init() {
                var designer = <?= json_encode($designer) ?>;
                if (designer) {
                    $('span[data-kind="variable-name"]').each(function() {
                        const $this = $(this)
                        $this.addClass('copy-field-name text-info')
                        $this.on('mousedown', function(e) {
                            e.stopImmediatePropagation()
                        })
                        const field = $this.text()
                        $this.on('click', function() {
                            EMMTools_copyTextToClipboard(field);
                            $this.css('background-color', 'red')
                            setTimeout(function() {
                                $this.css('background-color', 'transparent')
                            }, 200)
                            return false;
                        })
                        $annotation = $('.emdt-field-annotation[data-target="' + field + '"]')
                        if ($annotation.length) {
                            const $badge = $('<span class="badge badge-info" style="font-weight:normal;">EMDT</span>');
                            $badge.attr('title', $annotation.text()).css('margin-left','1em');
                            $('#design-' + field + ' span.od-field-icons').append($badge);
                        }
                    })
                }
                else {
                    $('.emdt-field-annotation').each(function() {
                        const $annotation = $(this);
                        const field = $annotation.attr('data-target');
                        const $badge = $('<span class="badge badge-info" style="font-weight:normal;">EMDT</span>');
                        const embedded = $('[sq_id="' + field + '"]').hasClass('row-field-embedded');
                        $badge.css('margin-bottom','0.5em');
                        $annotation.prepend('<br>');
                        $annotation.prepend($badge);
                        $badge.after('<small><i> &ndash; ' + field + '</i></small>');
                        if (embedded) {
                            $badge.removeClass('badge-info').addClass('badge-warning');
                            var $embed = $('span.rc-field-embed[var="' + field + '"]')
                            $embed.parents('tr[sq_id]').find('td').not('.questionnum').first().append($annotation);
                            $badge.css('cursor', 'crosshair');
                            $badge.on('mouseenter', function() {
                                $embed.css('outline', 'red dotted 2px');
                            });
                            $badge.on('mouseleave', function() {
                                $embed.css('outline','none');
                            });
                            $badge.on('click', function() {
                                $embed.find('input').focus();
                            });
                        }
                        else {
                            $('div[data-mlm-field="' + field + '"]').after($annotation);
                        }
                        $annotation.show();
                    })
                }
            }
            $(function() {
                if (<?=json_encode($designer)?>) {
                    const EMMTools_reloadDesignTable = reloadDesignTable
                    reloadDesignTable = function(form_name, js) {
                        EMMTools_reloadDesignTable(form_name, js)
                        setTimeout(function() {
                            EMMTools_init()
                        }, 50)
                    }
                }
                EMMTools_init()
            });
        </script>
        <?php
    }

    function inspectProjectObject() {
        global $Proj, $lang;
        $user = new User($this->fw, defined("USERID") ? USERID : null);
        if ($user->isSuperUser()) {

            $script_url = $this->getUrl("js/json-viewer.js");
            print "<script src=\"{$script_url}\"></script>\n";
            // Fully(?) populate data
            $Proj->loadEvents();
            $Proj->loadEventsForms();
            $Proj->loadMetadata();
            $Proj->loadProjectValues();
            $Proj->loadSurveys();
            $Proj->getUniqueEventNames();
            $Proj->getUniqueGroupNames();
            $Proj->getGroups();

            ?>
            <style>
                #projectobject-tabContent {
                    margin-top:0.5em;
                    max-width:800px;
                }
                pre {
                    border: none;
                    background: none;
                    font-size: 12px;
                }
                h4 {
                    font-size: 1.2em;
                    font-weight: bold;
                    margin-bottom: 1em;
                }
                .emm-badge {
                    font-weight: normal;
                }
            </style>
            <h4><?=$this->tt("projectobjectinspector_title")?></h4>
            <nav>
                <div class="nav nav-tabs" id="projectobject-tab" role="tablist">
                    <a class="nav-item nav-link active" id="emm-json-tab" data-toggle="tab" href="#emm-json" role="tab" aria-controls="emm-json" aria-selected="false">JSON</a>
                    <a class="nav-item nav-link" id="printr-tab" data-toggle="tab" href="#printr" role="tab" aria-controls="printr" aria-selected="true">print_r</a>
                    <a class="nav-item nav-link" id="vardump-tab" data-toggle="tab" href="#vardump" role="tab" aria-controls="vardump" aria-selected="false">var_dump</a>
                </div>
            </nav>
            <div class="tab-content" id="projectobject-tabContent">
                <div class="tab-pane fade" id="printr" role="tabpanel" aria-labelledby="printr-tab">
                    <pre><?php print_r($Proj); ?></pre>
                </div>
                <div class="tab-pane fade" id="vardump" role="tabpanel" aria-labelledby="vardump-tab">
                    <pre><?php var_dump($Proj); ?></pre>
                </div>
                <div class="tab-pane fade show active" id="emm-json" role="tabpanel" aria-labelledby="emm-json-tab">
                    <div id="json-menu">
                        <a href="javascript:emdtJsonCollapseAll();">Collapse all</a> | 
                        <a href="javascript:emdtJsonExpandAll();">Expand all</a>
                    </div>
                    <div id="json"></div>
                </div>
            </div>
            <script>
                function emdtJsonCollapseAll() {
                    $('a.list-link').not('.collapsed').each(function(){
                        this.click();
                    })
                }
                function emdtJsonExpandAll() {
                    $('a.list-link.collapsed').each(function(){
                        this.click()
                    })
                }

                $(function(){
                    var jsonViewer = new JSONViewer();
                    var json = <?= json_encode($Proj) ?>;
                    document.querySelector("#json").appendChild(jsonViewer.getContainer());
                    jsonViewer.showJSON(json, -1, 2);
                });
            </script>
            <?php
        }
        else {
            print $lang["global_05"];
        }
    }

    /**
     * Checks whether a module is enabled for a project or on the system.
     *
     * @param string $prefix A unique module prefix.
     * @param string $pid A project id (optional).
     * @return mixed False if the module is not enabled, otherwise the enabled version of the module (string).
     * @throws InvalidArgumentException
     **/
    public function _isModuleEnabled($prefix, $pid = null) {
        if (method_exists($this->framework, "isModuleEnabled")) {
            return $this->framework->isModuleEnabled($prefix, $pid);
        }
        else {
            if (empty($prefix)) {
                throw new InvalidArgumentException("Prefix must not be empty.");
            }
            if ($pid !== null && !is_int($pid) && ($pid * 1 < 1)) {
                throw new InvalidArgumentException("Invalid value for pid");
            }
            $enabled = \ExternalModules\ExternalModules::getEnabledModules($pid);
            return array_key_exists($prefix, $enabled) ? $enabled[$prefix] : false;
        }
    }
}

class PageInfo {
    public static function IsRecordHomePage() {
        return (strpos(PAGE, "DataEntry/record_home.php") !== false);
    }

    public static function IsExistingRecordHomePage() {
        return (strpos(PAGE, "DataEntry/record_home.php") !== false) && !isset($_GET["auto"]);
    }

    public static function IsSystemExternalModulesManager() {
        return (strpos(PAGE, "manager/control_center.php") !== false);
    }

    public static function IsProjectExternalModulesManager() {
        return (strpos(PAGE, "manager/project.php") !== false);
    }

    public static function IsDevelopmentFramework($module) {
        return strpos($module->framework->getUrl("dummy.php"), "/external_modules/?prefix=") !== false;
    }

    public static function IsDatabaseQueryTool() {
        return strpos(PAGE_FULL, "ControlCenter/database_query_tool.php") !== false;
    }

    public static function IsDesigner() {
        return (strpos(PAGE, "Design/online_designer.php") === 0);
    }

    public static function IsDataEntry() {
        return (strpos(PAGE, "DataEntry/index.php") === 0);
    }

    public static function IsSurvey() {
        return (strpos(PAGE, "surveys/index.php") === 0);
    }

    public static function HasGETParameter($name) {
        return isset($_GET[$name]);
    }

    public static function SanitizeProjectID($pid) {
        $clean = is_numeric($pid) ? $pid * 1 : null;
        return is_int($clean) ? $clean : null;
    }

}