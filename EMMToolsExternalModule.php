<?php

namespace RUB\EMMToolsExternalModule;

use ExternalModules\AbstractExternalModule;

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
        // Module Manager Shortcut.
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
                            var link = $('<a href="<?=$link?>' + moduleName + '"><i class="fas fa-cog" style="margin-right:2px;"></i> <?=$fw->tt("reveallink_label")?></a>')
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
    }


    
}

class PageInfo {
    public static function IsSystemExternalModulesManager() {
        return (strpos(PAGE, "ExternalModules/manager/control_center.php") !== false) || (strpos(PAGE, "external_modules/manager/control_center.php") !== false);
    }

    public static function IsProjectExternalModulesManager() {
        return (strpos(PAGE, "ExternalModules/manager/project.php") !== false) || (strpos(PAGE, "external_modules/manager/project.php") !== false);
    }

    public static function IsDevelopmentFramework($module) {
        return strpos($module->framework->getUrl("dummy.php"), "/external_modules/?prefix=") !== false;
    }

    public static function HasGETParameter($name) {
        return isset($_GET[$name]);
    }

    public static function SanitizeProjectID($pid) {
        $clean = is_numeric($pid) ? $pid * 1 : null;
        return is_int($clean) ? $clean : null;
    }

}