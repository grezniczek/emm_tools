// External Module Developer Tools
// Dr. Günther Rezniczek, Ruhr-Universität Bochum, Marien Hospital Herne
// @ts-check
;(function() {

    // @ts-ignore
    const EMDT = window.DE_RUB_EMDTools ?? {
        init: initialize,
        purgeSettings: purgeSettings
    };
    // @ts-ignore
    window.DE_RUB_EMDTools = EMDT;

    var config = {
        debug: true
    };
    var JSMO = {};
    
    function initialize(config_data, jsmo_obj) {
        config = config_data;
        JSMO = jsmo_obj;
        log('Initialized', config);
    }

    function purgeSettings(moduleName, pid) {
        if (confirm(JSMO.tt('mysqlpurge_confirm_msg', moduleName))) {
            log('Purging module "' + moduleName + '" in the ' + (pid == null ? 'system context' : ('context of project ' + pid)));
            JSMO.ajax('purge-settings', {
                module: moduleName,
                pid: pid
            })
            .then(() => log('Purge successful.')); // No catch - EM throws and exceptions are reported by the framework
        }
    }

    //#region Debug Logging
    
    /**
     * Logs a message to the console when in debug mode
     */
     function log() {
        if (!config.debug) return;
        var ln = '??';
        try {
            var line = ((new Error).stack ?? '').split('\n')[2];
            var parts = line.split(':');
            ln = parts[parts.length - 2];
        }
        catch(err) { }
        log_print(ln, 'log', arguments);
    }
    /**
     * Logs a warning to the console when in debug mode
     */
    function warn() {
        if (!config.debug) return;
        var ln = '??';
        try {
            var line = ((new Error).stack ?? '').split('\n')[2];
            var parts = line.split(':');
            ln = parts[parts.length - 2];
        }
        catch(err) { }
        log_print(ln, 'warn', arguments);
    }
    
    /**
     * Logs an error to the console when in debug mode
     */
    function error() {
        var ln = '??';
        try {
            var line = ((new Error).stack ?? '').split('\n')[2];
            var parts = line.split(':');
            ln = parts[parts.length - 2];
        }
        catch(err) { }
        log_print(ln, 'error', arguments);;
    }
    
    /**
     * Prints to the console
     * @param {string} ln Line number where log was called from
     * @param {'log'|'warn'|'error'} mode
     * @param {IArguments} args
     */
    function log_print(ln, mode, args) {
        var prompt = 'EMDT ' + config.version + ' [' + ln + ']';
        switch(args.length) {
            case 1:
                console[mode](prompt, args[0]);
                break;
            case 2:
                console[mode](prompt, args[0], args[1]);
                break;
            case 3:
                console[mode](prompt, args[0], args[1], args[2]);
                break;
            case 4:
                console[mode](prompt, args[0], args[1], args[2], args[3]);
                break;
            case 5:
                console[mode](prompt, args[0], args[1], args[2], args[3], args[4]);
                break;
            case 6:
                console[mode](prompt, args[0], args[1], args[2], args[3], args[4], args[5]);
                break;
            default:
                console[mode](prompt, args);
                break;
        }
    }
    
    //#endregion
    
    })();