<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class InCuca_Wordfence {
    public static function get_wordfence_summary() {
        // Verifique se o Wordfence está ativo
        if (!class_exists('wfConfig')) {
            return new WP_Error('wordfence_inactive', 'O Wordfence não está ativo.', array('status' => 404));
        }

        // Obter os dados dos últimos 30 dias
        $now = time();
        $thirty_days_ago = $now - (30 * 24 * 60 * 60);
        $attacks_prevented = wfConfig::get('attackData_blocked', 0, $thirty_days_ago, $now);

        $data = array(
            'attacks_prevented_last_30_days' => $attacks_prevented
        );

        return rest_ensure_response($data);
    }

    public static function get_logs() {
        // Verifique se o Wordfence está ativo
        if (!class_exists('wfActivityReport')) {
            return new WP_Error('wordfence_inactive', 'O Wordfence não está ativo.', array('status' => 404));
        }

        $logs = array();

        // Obter logs de segurança
        $activityReport = new wfActivityReport();

        $top_countries_blocked = array();
        $top_countries_blocked['24h'] = $activityReport->getTopCountriesBlocked(100, 1);
        $top_countries_blocked['7d'] = $activityReport->getTopCountriesBlocked(100, 7);
        $top_countries_blocked['30d'] = $activityReport->getTopCountriesBlocked(100, 30);
        $logs['top_countries_blocked'] = $top_countries_blocked;


        $top_ips_blocks = array();
        $top_ips_blocks['24h'] = $activityReport->getTopIPsBlocked(100, 1);
        $top_ips_blocks['7d'] = $activityReport->getTopIPsBlocked(100, 7);
        $top_ips_blocks['30d'] = $activityReport->getTopIPsBlocked(100, 30);
        $logs['top_ips_blocked'] = $top_ips_blocks;

        // Local Attack Data
		$local_blocks = array();
		$local_blocks[] = array(
            'title' => __('Complex', 'incuca-helper'),
            'type' => wfActivityReport::BLOCK_TYPE_COMPLEX,
			'24h' => (int) $activityReport->getBlockedCount(1, wfActivityReport::BLOCK_TYPE_COMPLEX),
			'7d' => (int) $activityReport->getBlockedCount(7, wfActivityReport::BLOCK_TYPE_COMPLEX),
			'30d' => (int) $activityReport->getBlockedCount(30, wfActivityReport::BLOCK_TYPE_COMPLEX),
		);
		$local_blocks[] = array(
            'title' => __('Brute Force', 'incuca-helper'),
            'type' => wfActivityReport::BLOCK_TYPE_BRUTE_FORCE,
			'24h' => (int) $activityReport->getBlockedCount(1, wfActivityReport::BLOCK_TYPE_BRUTE_FORCE),
			'7d' => (int) $activityReport->getBlockedCount(7, wfActivityReport::BLOCK_TYPE_BRUTE_FORCE),
			'30d' => (int) $activityReport->getBlockedCount(30, wfActivityReport::BLOCK_TYPE_BRUTE_FORCE),
		);
		$local_blocks[] = array(
            'title' => __('Blocklist', 'incuca-helper'),
            'type' => wfActivityReport::BLOCK_TYPE_BLACKLIST,
			'24h' => (int) $activityReport->getBlockedCount(1, wfActivityReport::BLOCK_TYPE_BLACKLIST),
			'7d' => (int) $activityReport->getBlockedCount(7, wfActivityReport::BLOCK_TYPE_BLACKLIST),
			'30d' => (int) $activityReport->getBlockedCount(30, wfActivityReport::BLOCK_TYPE_BLACKLIST),
		);
        $logs['local_blocks'] = $local_blocks;

        return rest_ensure_response($logs);
    }
}
?>
