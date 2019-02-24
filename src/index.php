<?php

add_action("init", "wp_tracy_init_action", 2);

function wp_tracy_init_action()
{
    if (defined("DOING_AJAX") && DOING_AJAX) {
        return; // for IE compatibility WordPress media upload
    }

    $defaultPanelsClasses = [
        "WpTracy\\WpPanel",
        "WpTracy\\WpUserPanel",
        "WpTracy\\WpPostPanel",
        "WpTracy\\WpQueryPanel",
        "WpTracy\\WpQueriedObjectPanel",
        "WpTracy\\WpDbPanel",
        "WpTracy\\WpRolesPanel",
        "WpTracy\\WpRewritePanel",
        "WpTracy\\WpCurrentScreenPanel",
    ]; // in the correct order

    $settings = [
        "only-for-user-id" =>  null,
        "panels-classes" => $defaultPanelsClasses,
        "panels-filtering-allowed" =>  "on",
    ];
	$settings = apply_filters( 'mrqaidi_debugger_settings', $settings );
	$debugMode = Tracy\Debugger::PRODUCTION;
	$onlyForUserId = $settings["only-for-user-id"];
	if ($onlyForUserId > 0 && $onlyForUserId = get_current_user_id()) {
		$debugMode = Tracy\Debugger::DEVELOPMENT;
	}
	Tracy\Debugger::$showBar = true;
    Tracy\Debugger::enable($debugMode,$settings["log-path"]); // hooray, enabling debugging using Tracy

    $panelsClasses = $settings["panels-classes"];
    if (!is_array($panelsClasses)) {
        trigger_error("\"wp-tracy-user-settings->panels-classes\" option must be type of array.", E_USER_WARNING);
        exit;
    }

    // panels (custom) filtering
    if ($settings["panels-filtering-allowed"] === "on") {
        $panelsClasses = apply_filters("wp_tracy_panels_filter", $panelsClasses);
        if (!is_array($panelsClasses)) {
            trigger_error("\"wp_tracy_panels_filter\" must return type of array.", E_USER_WARNING);
            exit;
        }
    }

    // panels registration
    foreach ($panelsClasses as $className) {
        $panel = new $className;
        if ($panel instanceof Tracy\IBarPanel) {
            Tracy\Debugger::getBar()->addPanel(new $className);
        }
    }
}
