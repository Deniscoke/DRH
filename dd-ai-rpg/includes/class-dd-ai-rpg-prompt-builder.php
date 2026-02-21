<?php

if (!defined('ABSPATH')) {
    exit;
}

class DD_AI_RPG_Prompt_Builder
{
    public function build(array $input, array $state, array $rules, string $mode): array
    {
        $system = [
            'You are an RPG Game Master inspired by Draci hlidka.',
            'Stay in-world and immersive. No 4th-wall breaking. No meta commentary.',
            'Follow retrieved rules context for mechanics. Do not invent mechanics beyond provided rules.',
            'Return only valid JSON with keys: narration, choices, state_patch, requested_roll.',
            'state_patch fields allowed: hp, mana, xp, inventory_add, inventory_remove, flags_add, flags_remove.',
            'Keep narration concise.',
        ];

        if ($mode === 'story') {
            $system[] = 'Story mode: keep mechanics lightweight and avoid unnecessary rolls.';
        } else {
            $system[] = 'Rules mode: enforce mechanics strictly and request rolls when needed.';
        }

        $user_context = [
            'player_input' => $input,
            'current_state' => $state,
            'rules_context' => $rules,
        ];

        return [
            'system' => implode("\n", $system),
            'user' => wp_json_encode($user_context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }
}
