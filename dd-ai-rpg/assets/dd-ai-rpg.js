(function () {
  function init(root) {
    if (!root || root.__ddRpgInited) return;
    root.__ddRpgInited = true;

    const cfg = window.ddAiRpgConfig || {};
    const endpoint = cfg.endpoint || '/wp-json/dd-ai/v1/rpg';
    const msgs = root.querySelector('[data-dd-msgs]');
    const form = root.querySelector('[data-dd-form]');
    const input = root.querySelector('[data-dd-input]');
    const micBtn = root.querySelector('[data-dd-mic]');
    const stopBtn = root.querySelector('[data-dd-stop]');
    const ttsToggle = root.querySelector('[data-dd-tts]');

    if (!msgs || !form || !input || !micBtn) return;

    const stopSpeaking = () => {
      try { window.speechSynthesis.cancel(); } catch (e) {}
    };

    const speak = (text) => {
      if (!ttsToggle || !ttsToggle.checked || !('speechSynthesis' in window)) return;
      stopSpeaking();
      const u = new SpeechSynthesisUtterance(text);
      u.lang = 'sk-SK';
      const voices = window.speechSynthesis.getVoices ? window.speechSynthesis.getVoices() : [];
      u.voice = voices.find(v => (v.lang || '').toLowerCase().startsWith('sk')) || voices[0] || null;
      window.speechSynthesis.speak(u);
    };

    const addMsg = (text, who) => {
      const d = document.createElement('div');
      d.className = 'dd-vchat__msg ' + (who === 'me' ? 'dd-vchat__msg--me' : who === 'sys' ? 'dd-vchat__msg--sys' : 'dd-vchat__msg--bot');
      d.textContent = text;
      msgs.appendChild(d);
      msgs.scrollTop = msgs.scrollHeight;
      return d;
    };

    const addChoices = (choices) => {
      (choices || []).forEach((choice) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'dd-vchat__msg dd-vchat__choice';
        btn.textContent = `${choice.id}: ${choice.text}`;
        btn.addEventListener('click', () => {
          sendMessage(choice.id, 'choice_id');
        });
        msgs.appendChild(btn);
      });
      msgs.scrollTop = msgs.scrollHeight;
    };

    const sendMessage = async (message, action = 'player_input') => {
      if (!message) return;

      addMsg(message, 'me');
      const thinking = addMsg('…', 'bot');
      input.disabled = true;

      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': cfg.nonce || ''
          },
          body: JSON.stringify({
            player_id: cfg.playerId || 'guest',
            campaign_id: cfg.campaignId || 'default_campaign',
            mode: cfg.mode || 'rules',
            action,
            message
          })
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || `HTTP ${res.status}`);

        thinking.textContent = data.narration || 'Vypravěč sa odmlčal.';
        if (Array.isArray(data.rolls) && data.rolls.length) {
          data.rolls.forEach((roll) => {
            addMsg(`🎲 ${roll.skill} DC ${roll.dc}: hod ${roll.result} (${roll.success ? 'úspech' : 'neúspech'})`, 'sys');
          });
        }
        addChoices(data.choices || []);
        speak(data.narration || '');
      } catch (err) {
        thinking.textContent = `Chyba: ${err.message || 'unknown'}`;
      } finally {
        input.disabled = false;
        input.focus();
      }
    };

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      stopSpeaking();
      const text = (input.value || '').trim();
      if (!text) return;
      input.value = '';
      sendMessage(text);
    });

    if (stopBtn) stopBtn.addEventListener('click', stopSpeaking);

    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) {
      addMsg('Pozn.: Tento prehliadač nepodporuje diktovanie.', 'sys');
      micBtn.disabled = true;
      return;
    }

    const rec = new SR();
    rec.lang = 'sk-SK';
    rec.interimResults = true;
    rec.continuous = false;

    let listening = false;
    const setMic = (on) => {
      listening = on;
      micBtn.classList.toggle('is-on', on);
      micBtn.textContent = on ? '⏺' : '🎤';
    };

    rec.onstart = () => setMic(true);
    rec.onend = () => setMic(false);
    rec.onerror = (e) => {
      setMic(false);
      addMsg(`Mikrofón chyba: ${e.error || 'unknown'}`, 'sys');
    };

    rec.onresult = (event) => {
      let finalText = '';
      for (let i = event.resultIndex; i < event.results.length; i++) {
        if (event.results[i].isFinal) {
          finalText += event.results[i][0].transcript;
        }
      }
      if (finalText.trim()) {
        sendMessage(finalText.trim());
      }
    };

    micBtn.addEventListener('click', async () => {
      try {
        stopSpeaking();
        if (listening) {
          rec.stop();
          return;
        }
        await navigator.mediaDevices.getUserMedia({ audio: true });
        rec.start();
      } catch (e) {
        setMic(false);
        addMsg('Nepodarilo sa spustiť mikrofón.', 'sys');
      }
    });
  }

  const boot = () => {
    document.querySelectorAll('[data-dd-vchat]').forEach(init);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
