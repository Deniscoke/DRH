<?php
/**
 * Plugin Name: DD AI RPG Narrator
 * Description: AI-powered RPG narrator for WordPress with persistent state, dice mechanics, and voice-ready frontend.
 * Version: 0.1.0
 * Author: DD
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DD_AI_RPG_VERSION', '0.1.0');
define('DD_AI_RPG_DIR', plugin_dir_path(__FILE__));
define('DD_AI_RPG_URL', plugin_dir_url(__FILE__));

require_once DD_AI_RPG_DIR . 'includes/class-dd-ai-rpg-plugin.php';
require_once DD_AI_RPG_DIR . 'includes/class-dd-ai-rpg-rest-controller.php';
require_once DD_AI_RPG_DIR . 'includes/class-dd-ai-rpg-state-repository.php';
require_once DD_AI_RPG_DIR . 'includes/class-dd-ai-rpg-dice.php';
require_once DD_AI_RPG_DIR . 'includes/class-dd-ai-rpg-openai-client.php';
require_once DD_AI_RPG_DIR . 'includes/class-dd-ai-rpg-rules-retriever.php';
require_once DD_AI_RPG_DIR . 'includes/class-dd-ai-rpg-prompt-builder.php';
require_once DD_AI_RPG_DIR . 'includes/class-dd-ai-rpg-shortcode.php';

DD_AI_RPG_Plugin::boot();
