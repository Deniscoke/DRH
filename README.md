# DD AI RPG Narrator (WordPress Plugin)

This repository contains a modular WordPress plugin implementing an AI-powered RPG narrator with:

- REST endpoint: `POST /wp-json/dd-ai/v1/rpg`
- Persistent campaign/player state
- Server-side dice handling
- AI narration and structured choices
- Voice-enabled frontend widget (shortcode: `[dd_ai_rpg]`)

## Install

1. Copy `dd-ai-rpg` into `wp-content/plugins/`.
2. Activate **DD AI RPG Narrator**.
3. Set `dd_ai_openai_api_key` and optional `dd_ai_openai_model` via options or settings integration.
4. Place shortcode `[dd_ai_rpg]` into Elementor HTML/shortcode widget.

## Request payload

```json
{
  "player_id": "string",
  "campaign_id": "string",
  "action": "player_input | choice_id | roll",
  "message": "string"
}
```

## Response payload

```json
{
  "narration": "string",
  "choices": [{ "id": "A", "text": "string" }],
  "state_patch": {
    "hp": 0,
    "mana": 0,
    "xp": 0,
    "inventory_add": [],
    "inventory_remove": [],
    "flags_add": [],
    "flags_remove": []
  },
  "rolls": []
}
```
