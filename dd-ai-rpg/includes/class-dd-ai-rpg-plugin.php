<?php

if (!defined('ABSPATH')) {
    exit;
}

class DD_AI_RPG_Plugin
{
    public static function boot(): void
    {
        add_action('init', [self::class, 'register_shortcodes']);
        add_action('rest_api_init', [self::class, 'register_rest']);
    }

    public static function register_shortcodes(): void
    {
        $shortcode = new DD_AI_RPG_Shortcode();
        $shortcode->register();
    }

    public static function register_rest(): void
    {
        $repository = new DD_AI_RPG_State_Repository();
        $dice = new DD_AI_RPG_Dice();
        $rules = new DD_AI_RPG_Rules_Retriever();
        $prompts = new DD_AI_RPG_Prompt_Builder();
        $openai = new DD_AI_RPG_OpenAI_Client();

        $controller = new DD_AI_RPG_REST_Controller(
            $repository,
            $dice,
            $rules,
            $prompts,
            $openai
        );

        $controller->register_routes();
    }
}
