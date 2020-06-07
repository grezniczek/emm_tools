<?php

namespace RUB\EMMToolsExternalModule;

use ExternalModules\AbstractExternalModule;
use InvalidArgumentException;

/**
 * Provides enhancements to the External Module Management pages.
 */
class EMMToolsExternalModule extends AbstractExternalModule {


    function redcap_every_page_top($project_id = null) {

        $fw = $this->framework; // Shortcut to the EM framework.

        // Hide this module from normal users and exit the hook if not a super-user.
        if (!SUPER_USER) {
            if (PageInfo::IsProjectExternalModulesManager()) {
                ?>
                <script>
                    $(function() {
                        $('tr[data-module="<?=$this->PREFIX?>"]').remove();
                    })
                </script>
                <?php
            }
            return;
        } 


        // Module Manager Shortcut
        if (PageInfo::IsProjectExternalModulesManager() && $this->getSystemSetting("module-manager-shortcut")) {
            $link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/manager/control_center.php?return-pid={$project_id}";
            ?>
            <script>
                $(function(){
                    $('#external-modules-enable-modules-button').after('&nbsp;&nbsp;&nbsp;<a class="btn btn-light btn-sm" role="button" href="<?=$link?>"><i class="fas fa-sign-out-alt"></i> <?=$fw->tt("mmslink_label")?></a>')
                })
            </script>
            <?php
        }

        // Module Manager Reveal.
        if ($this->getSystemSetting("module-manager-reveal")) {
            if (PageInfo::IsProjectExternalModulesManager()) {
                $link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/manager/control_center.php?return-pid={$project_id}&reveal-module=";
                ?>
                <script>
                    $(function(){
                        $('#external-modules-enabled tr[data-module]').each(function() {
                            var tr = $(this)
                            var moduleName = tr.attr('data-module')
                            var link = $('<a href="<?=$link?>' + moduleName + '" style="margin-right:1em;"><i class="fas fa-cog" style="margin-right:2px;"></i> <?=$fw->tt("reveallink_label")?></a>')
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
                $moduleName = $_GET["reveal-module"];
                $returnPid = PageInfo::SanitizeProjectID($_GET["return-pid"]);
                $triggerTimeout = $this->getSystemSetting("module-manager-reveal-timeout");
                if (!is_numeric($triggerTimeout)) $triggerTimeout = 50;
                $triggerTimeout = abs($triggerTimeout);
                ?>
                <script>
                    $(function() {
                        var titleDiv = $('tr[data-module="<?=$moduleName?>"] td div.external-modules-title').first()
                        var title = titleDiv.text().trim().split(' - v')[0]
                        var search = $('#enabled-modules-search')
                        setTimeout(function() {
                        search.val(title)
                        search.trigger('keyup')
                        }, <?=$triggerTimeout?>)
                        <?php if ($returnPid != null) {
                            $link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/manager/project.php?pid=" . $returnPid;
                            ?>
                            $('#download-new-mod-form').before('<div style="margin-top:7px;"><a class="btn btn-light btn-sm" role="button" href="<?=$link?>"><i class="fas fa-sign-out-alt"></i> <?=$fw->tt("returnlink_label", $returnPid)?></a></div>')
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
                            $('#download-new-mod-form').before('<div style="margin-top:7px;"><a class="btn btn-light btn-sm" role="button" href="<?=$link?>"><i class="fas fa-sign-out-alt"></i> <?=$fw->tt("returnlink_label", $returnPid)?></a></div>')
                        })
                    </script>
                    <?php
                }
            }
        }


        // MySQL Simple Admin Shortcuts
        if ((PageInfo::IsProjectExternalModulesManager() || PageInfo::IsSystemExternalModulesManager() || PageInfo::IsMySQLSimpleAdmin()) &&
            $this->getSystemSetting("mysql-simple-admin-links") && 
            $this->_isModuleEnabled("mysql_simple_admin")) {
            if (PageInfo::IsProjectExternalModulesManager()) {
                $link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/?prefix=mysql_simple_admin&page=index&query-pid={$project_id}&module-prefix=";
                ?>
                <script>
                    $(function(){
                        $('#external-modules-enabled tr[data-module]').each(function() {
                            var tr = $(this)
                            var moduleName = tr.attr('data-module')
                            var link = $('<a target="_blank" href="<?=$link?>' + moduleName + '" style="margin-right:1em;"><i class="fas fa-database" style="margin-right:2px;"></i> <?=$fw->tt("mysqllink_label")?></a>')
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
                            var link = $('<a target="_blank" href="<?=$link?>' + moduleName + '" style="margin-right:1em;"><i class="fas fa-database" style="margin-right:2px;"></i> <?=$fw->tt("mysqllink_label")?></a>')
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
                    $result = $fw->query("
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
                        $query = "SELECT * FROM {$log_event_table}\n WHERE `project_id` = 49 AND `pk` = '{$record}'\n ORDER BY `log_event_id` DESC";
                    }
                    $execute = !empty($record);
                }
                if ($execute) {
                ?>
                <script>
                    $(function() {
                        $('#query').val(<?=json_encode($query)?>)
                        $('#form button').click()
                    })
                </script>
                <?php
                }
            }
        }

        // Query for record data.
        if (PageInfo::IsExistingRecordHomePage() && $this->getSystemSetting("mysql-simple-admin-query-record") && $this->_isModuleEnabled("mysql_simple_admin")) {
            $data_link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/?prefix=mysql_simple_admin&page=index&query-pid={$project_id}&query-record={$_GET['id']}&query-for=data";
            $logs_link = (PageInfo::IsDevelopmentFramework($this) ? APP_PATH_WEBROOT_PARENT . "external_modules" : APP_PATH_WEBROOT . "ExternalModules") . "/?prefix=mysql_simple_admin&page=index&query-pid={$project_id}&query-record={$_GET['id']}&query-for=logs";
            ?>
            <script>
                $(function(){
                    var $ul = $('#recordActionDropdown')
                    $ul.append('<li class="ui-menu-item"><a href="<?=$data_link?>" target="_blank" style="display:block;" tabindex="-1" role="menuitem" class="ui-menu-item-wrapper"><span style="vertical-align:middle;color:#065499;"><i class="fas fa-database"></i> <?=$fw->tt("mysqllink_record_data")?></span></a></li>')
                    $ul.append('<li class="ui-menu-item"><a href="<?=$logs_link?>" target="_blank" style="display:block;" tabindex="-1" role="menuitem" class="ui-menu-item-wrapper"><span style="vertical-align:middle;color:#065499;"><i class="fas fa-database" style="color:red;"></i> <?=$fw->tt("mysqllink_record_logs")?></span></a></li>')
                })
            </script>
            <?php
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
        return (strpos(PAGE, "ExternalModules/manager/control_center.php") !== false) || (strpos(PAGE, "external_modules/manager/control_center.php") !== false);
    }

    public static function IsProjectExternalModulesManager() {
        return (strpos(PAGE, "ExternalModules/manager/project.php") !== false) || (strpos(PAGE, "external_modules/manager/project.php") !== false);
    }

    public static function IsDevelopmentFramework($module) {
        return strpos($module->framework->getUrl("dummy.php"), "/external_modules/?prefix=") !== false;
    }

    public static function IsMySQLSimpleAdmin() {
        return (strpos(PAGE, "ExternalModules/?prefix=mysql_simple_admin&page=index") !== false) || (strpos(PAGE, "external_modules/?prefix=mysql_simple_admin&page=index") !== false);
    }

    public static function HasGETParameter($name) {
        return isset($_GET[$name]);
    }

    public static function SanitizeProjectID($pid) {
        $clean = is_numeric($pid) ? $pid * 1 : null;
        return is_int($clean) ? $clean : null;
    }

}