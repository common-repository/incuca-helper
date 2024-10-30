<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class InCuca_Helpers
{
    public static function incuca_sanitize_version($version)
    {
        $version = trim(preg_replace('/[^a-zA-Z0-9_\-.]+/', '', $version));

        return $version;
    }

    public static function incuca_get_plugin_data($plugin_data, $file_path, $active_plugins)
    {

        $plugin_slug = null;

        // Extract it from the file path.
        $folder_name = explode('/', $file_path);

        // If not, use the TextDomain key.
        if (isset($folder_name[0])) {
            $plugin_slug = wp_kses(trim((string) $folder_name[0]), 'strip');
        }
        unset($folder_name);

        $text_domain = wp_kses( (string) $plugin_data['TextDomain'], 'strip' );

        // If the TextDomain key is empty, extract it from the file path.
        if (is_null($plugin_slug)) {
            $plugin_slug = $text_domain;
        }

        // Get the plugin slug and version from the plugin data.
        $plugin_version = wp_kses((string) $plugin_data['Version'], 'strip');

        return array(
            'name' => $plugin_data['Name'],
            'text_domain' => $text_domain,
            'slug' => $plugin_slug,
            'version' => $plugin_version,
            'active' => in_array($file_path, $active_plugins)
        );
    }
}
