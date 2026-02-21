<?php

if (!defined('ABSPATH')) {
    exit;
}

class DD_AI_RPG_Shortcode
{
    public function register(): void
    {
        add_shortcode('dd_ai_rpg', [$this, 'render']);
    }

    public function render(): string
    {
        wp_enqueue_script('dd-ai-rpg-frontend', DD_AI_RPG_URL . 'assets/dd-ai-rpg.js', [], DD_AI_RPG_VERSION, true);

        $config = [
            'endpoint' => rest_url('dd-ai/v1/rpg'),
            'playerId' => is_user_logged_in() ? 'user_' . get_current_user_id() : 'guest_' . wp_generate_uuid4(),
            'campaignId' => 'default_campaign',
            'mode' => 'rules',
            'nonce' => wp_create_nonce('wp_rest'),
        ];

        wp_localize_script('dd-ai-rpg-frontend', 'ddAiRpgConfig', $config);

        ob_start();
        ?>
        <div class="dd-vchat" data-dd-vchat>
            <div class="dd-vchat__head">
                <div>
                    <div class="dd-vchat__title">RPG Vypravěč (Dračí hlídka)</div>
                    <div class="dd-vchat__sub">🎤 Nadiktuj akciu alebo ju napíš. Vypravěč odpovie a môže ju prečítať nahlas.</div>
                </div>
                <label class="dd-vchat__toggle" title="Automaticky čítať odpovede nahlas">
                    <input type="checkbox" data-dd-tts checked />
                    <span>🔊 Čítať</span>
                </label>
            </div>
            <div class="dd-vchat__msgs" data-dd-msgs>
                <div class="dd-vchat__msg dd-vchat__msg--bot">Vitaj, dobrodruh. Povedz, čo chceš urobiť ako prvé.</div>
            </div>
            <div class="dd-vchat__controls">
                <button class="dd-vchat__mic" type="button" data-dd-mic aria-label="Spustiť hlasový vstup">🎤</button>
                <form class="dd-vchat__form" data-dd-form>
                    <input class="dd-vchat__input" data-dd-input placeholder="Napíš svoju akciu..." autocomplete="off" />
                    <button class="dd-vchat__send" type="submit">Odoslať</button>
                </form>
                <button class="dd-vchat__stop" type="button" data-dd-stop aria-label="Zastaviť čítanie">⏹</button>
            </div>
            <div class="dd-vchat__fineprint">*Nezadávaj citlivé údaje. Mikrofón funguje najlepšie v Chrome/Edge.</div>
        </div>
        <style>
            .dd-vchat{--line:rgba(255,255,255,.14);--txt:rgba(255,255,255,.92);--muted:rgba(255,255,255,.72);--acc:#ff4da6;border:1px solid var(--line);border-radius:18px;overflow:hidden;background:radial-gradient(900px 420px at 30% 0%, rgba(255,77,166,.16), transparent 55%),radial-gradient(900px 420px at 100% 40%, rgba(56,189,248,.12), transparent 55%),linear-gradient(180deg, rgba(11,18,32,.96), rgba(11,18,32,.90));box-shadow:0 18px 50px rgba(0,0,0,.25);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;max-width:780px}.dd-vchat__head{padding:16px 16px 12px;border-bottom:1px solid var(--line);display:flex;align-items:flex-start;justify-content:space-between;gap:14px}.dd-vchat__title{font-weight:800;color:var(--txt);letter-spacing:-.02em;font-size:16px}.dd-vchat__sub{font-size:13px;color:var(--muted);margin-top:4px;line-height:1.35}.dd-vchat__toggle{display:flex;align-items:center;gap:10px;color:var(--muted);font-size:13px;user-select:none;padding:8px 10px;border:1px solid rgba(255,255,255,.14);border-radius:12px;background:rgba(255,255,255,.06)}.dd-vchat__toggle input{accent-color:var(--acc)}.dd-vchat__msgs{padding:14px;display:flex;flex-direction:column;gap:10px;min-height:220px;max-height:380px;overflow:auto}.dd-vchat__msg{max-width:85%;padding:10px 12px;border-radius:14px;border:1px solid rgba(255,255,255,.12);line-height:1.45;font-size:14px;color:var(--txt);white-space:pre-wrap;word-break:break-word}.dd-vchat__msg--bot{background:rgba(255,255,255,.06);border-top-left-radius:8px}.dd-vchat__msg--me{background:rgba(255,77,166,.14);border-color:rgba(255,77,166,.35);align-self:flex-end;border-top-right-radius:8px}.dd-vchat__msg--sys{background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.10);align-self:center;max-width:100%;color:var(--muted);font-size:12px}.dd-vchat__choice{background:rgba(56,189,248,.16);border-color:rgba(56,189,248,.45);cursor:pointer;text-align:left}.dd-vchat__controls{display:flex;gap:10px;padding:12px 12px 14px;border-top:1px solid var(--line);align-items:center}.dd-vchat__mic,.dd-vchat__stop{width:46px;height:46px;border-radius:14px;border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.10);color:var(--txt);cursor:pointer;flex-shrink:0}.dd-vchat__mic.is-on{border-color:rgba(255,77,166,.55);box-shadow:0 0 0 4px rgba(255,77,166,.18)}.dd-vchat__form{display:flex;gap:10px;flex:1}.dd-vchat__input{flex:1;padding:11px 12px;border-radius:12px;border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.06);color:var(--txt);outline:none}.dd-vchat__send{padding:11px 14px;border-radius:12px;border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.10);color:var(--txt);cursor:pointer;white-space:nowrap}.dd-vchat__fineprint{padding:0 14px 14px;color:var(--muted);font-size:12px}
        </style>
        <?php
        return (string) ob_get_clean();
    }
}
