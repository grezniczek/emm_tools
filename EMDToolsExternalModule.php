<?php namespace DE\RUB\EMDToolsExternalModule;

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

    function __construct() {
        parent::__construct();
        $this->fw = $this->framework;
    }

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
        $user_id = defined("USERID") ? USERID : null;
        $user = new User($this->fw, $user_id);

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
        if (!(($user->isSuperUser() && $user->canAccessAdminDashboards()) || $user->canAccessSystemConfig())) {
            return;
        }

        // Module Manager Shortcut
        if ($user->canAccessSystemConfig() || $user->canAccessAdminDashboards()) {
            if (PageInfo::IsProjectExternalModulesManager() && $this->getSystemSetting("module-manager-shortcut")) {
                $link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/manager/control_center.php?return-pid={$project_id}";
                ?>
                <script>
                    $(function(){
                        $('#external-modules-enable-modules-button').after('&nbsp;&nbsp;&nbsp;<a class="btn btn-light btn-sm" role="button" href="<?=$link?>"><i class="fas fa-sign-out-alt"></i> <?=$this->fw->tt("mmslink_label")?></a>')
                    })
                </script>
                <?php
            }
        }

        // Module Manager Reveal.
        if ($user->canAccessSystemConfig() || $user->canAccessAdminDashboards()) {
            if ($this->getSystemSetting("module-manager-reveal")) {
                if (PageInfo::IsProjectExternalModulesManager()) {
                    $link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/manager/control_center.php?return-pid={$project_id}&reveal-module=";
                    ?>
                    <script>
                        $(function(){
                            $('#external-modules-enabled tr[data-module]').each(function() {
                                var tr = $(this)
                                var moduleName = tr.attr('data-module')
                                var link = $('<a href="<?=$link?>' + moduleName + '" style="margin-right:1em;"><i class="fas fa-cog" style="margin-right:2px;"></i> <?=$this->fw->tt("reveallink_label")?></a>')
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
                    $moduleName = json_encode($_GET["reveal-module"]);
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
                                $link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/manager/project.php?pid=" . $returnPid;
                                ?>
                                $('#external-modules-enabled').siblings('h4').before('<div style="margin-bottom:7px;"><a class="btn btn-light btn-sm" role="button" href="<?=$link?>"><i class="fas fa-sign-out-alt"></i> <?=$this->fw->tt("returnlink_label", $returnPid)?></a></div>')
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
                        $link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/manager/project.php?pid=" . $returnPid;
                        ?>
                        <script>
                            $(function() {
                                $('#external-modules-enabled').siblings('h4').before('<div style="margin-bottom:7px;"><a class="btn btn-light btn-sm" role="button" href="<?=$link?>"><i class="fas fa-sign-out-alt"></i> <?=$this->fw->tt("returnlink_label", $returnPid)?></a></div>')
                            })
                        </script>
                        <?php
                    }
                }
            }
        }
        
        // MySQL Simple Admin Shortcuts
        if ($user->canAccessAdminDashboards()) {
            $mysqlSimpleAdminEnabled = $this->_isModuleEnabled("mysql_simple_admin");
            $mysqlSimpleAdminShowLinks =  $this->getSystemSetting("mysql-simple-admin-links");
            if ((PageInfo::IsProjectExternalModulesManager() || PageInfo::IsSystemExternalModulesManager() || PageInfo::IsMySQLSimpleAdmin()) &&
                $mysqlSimpleAdminEnabled && 
                $mysqlSimpleAdminShowLinks) {
                if (PageInfo::IsProjectExternalModulesManager()) {
                    $link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/?prefix=mysql_simple_admin&page=index&query-pid={$project_id}&module-prefix=";
                    ?>
                    <script>
                        $(function(){
                            $('#external-modules-enabled tr[data-module]').each(function() {
                                var tr = $(this)
                                var moduleName = tr.attr('data-module')
                                var link = $('<a target="_blank" href="<?=$link?>' + moduleName + '" style="margin-right:1em;"><i class="fas fa-database" style="margin-right:2px;"></i> <?=$this->fw->tt("mysqllink_label")?></a>')
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
                else if (PageInfo::IsSystemExternalModulesManager()) {
                    $link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/?prefix=mysql_simple_admin&page=index&query-pid=NULL&module-prefix=";
                    ?>
                    <script>
                        $(function(){
                            $('#external-modules-enabled tr[data-module]').each(function() {
                                var tr = $(this)
                                var moduleName = tr.attr('data-module')
                                var link = $('<a target="_blank" href="<?=$link?>' + moduleName + '" style="margin-right:1em;"><i class="fas fa-database" style="margin-right:2px;"></i> <?=$this->fw->tt("mysqllink_label")?></a>')
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
                else if (PageInfo::IsMySQLSimpleAdmin()) {
                    $prefix = $_GET["module-prefix"];
                    $record = $_GET["query-record"];
                    $mode = $_GET["query-for"];
                    $pid = (int)$_GET["query-pid"];
                    $pid_clause = $pid === "NULL" ? "project_id IS NULL" : "project_id = {$pid}";
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
        if ($user->isSuperUser() && $user->canAccessAdminDashboards()) {
            $mysqlSimpleAdminEnabled = $this->_isModuleEnabled("mysql_simple_admin");
            if (PageInfo::IsExistingRecordHomePage() && $this->getSystemSetting("mysql-simple-admin-query-record") && $mysqlSimpleAdminEnabled) {
                $record_id = urlencode(strip_tags(label_decode(urldecode($_GET['id']))));
                $data_link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/?prefix=mysql_simple_admin&page=index&query-pid={$project_id}&query-record={$record_id}&query-for=data";
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
            if (PageInfo::IsExistingRecordHomePage() && $this->getSystemSetting("mysql-simple-admin-query-record-log") && $mysqlSimpleAdminEnabled) {
                $record_id = urlencode(strip_tags(label_decode(urldecode($_GET['id']))));
                $logs_link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/?prefix=mysql_simple_admin&page=index&query-pid={$project_id}&query-record={$record_id}&query-for=logs";
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
        if ($project_id && $link["key"] == "project-object-inspector") {
            return (defined("SUPER_USER") && SUPER_USER && $this->getSystemSetting("enable-projectobject") == true) ? $link : null;
        }
        if ($project_id && $link["key"] == "toggle-field-annotations") {
            if (defined("SUPER_USER") && SUPER_USER && $this->getSystemSetting("enable-fieldannotations") == true) {
                $state = $this->getProjectSetting("show-fieldannotations") == true ? "on" : "off";
                $link["name"] = $this->tt("link_fieldannotations") . "<b id=\"emdt-fieldannotations-state\">" . $this->tt("link_fieldannotations_{$state}") . "</b>";
                return $link;
            }
        }
        return null;
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
        $num_annotations = 0;
        foreach ($Proj->forms[$form]["fields"] as $field => $_) {
            $annotations = $Proj->metadata[$field]["misc"];
            if (!empty($annotations)) {
                print "<div class=\"emdt-field-annotation\" data-target=\"{$field}\" style=\"display:none;font-weight:normal;padding:0.5em;margin-top:0.5em;background-color:#fafafa;\"><code style=\"white-space:pre;margin-top:0.5em;\">{$annotations}</code></div>\n";
                $num_annotations++;
            }
        }
        if ($num_annotations) {
            ?>
            <script>
                // EMD Tools - Append Field Annotations
                $(function() {
                    var designer = <?= json_encode($designer) ?>;
                    $('.emdt-field-annotation').each(function() {
                        var $annotation = $(this);
                        var field = $annotation.attr('data-target');
                        var $badge = $('<span class="badge badge-info" style="font-weight:normal;">EMDT</span>');
                        if (designer) {
                            $badge.attr('title', $annotation.text()).css('margin-left','1em');
                            $('#design-' + field + ' span.od-field-icons').append($badge);
                        }
                        else {
                            var embedded = $('[sq_id="' + field + '"]').hasClass('row-field-embedded');
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
                                $('#label-' + field).append($annotation);
                            }
                            $annotation.show();
                        }
                    })
                });
            </script>
            <?php
        }
    }

    function inspectProjectObject() {
        global $Proj, $lang;
        if (defined("SUPER_USER") && SUPER_USER && $this->getSystemSetting("enable-projectobject") == true) {

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

    public static function IsMySQLSimpleAdmin() {
        return $_GET["prefix"] == "mysql_simple_admin" && $_GET["page"] == "index";
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