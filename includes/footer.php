<?php
// includes/footer.php
$aiEnabled = false;
if (!empty($pdo) && !empty($_SESSION['business_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT is_enabled FROM business_ai_settings WHERE business_id = ? LIMIT 1");
        $stmt->execute([$_SESSION['business_id']]);
        $r = $stmt->fetch();
        $aiEnabled = $r && !empty($r['is_enabled']);
    } catch (PDOException $e) {
        // tabel belum ada; biarkan default off
    }
}
$aiStatusClass = $aiEnabled ? 'ok' : 'off';
$aiStatusText = $aiEnabled ? 'AKTIF' : 'OFF';
?>
    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Auto-hide alerts after 5 seconds
  setTimeout(function () {
    const alert = document.querySelector('.alert');
    if (alert) {
      alert.style.transition = 'opacity 0.4s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 400);
    }
  }, 5000);

  // Confirm delete helper
  window.confirmDelete = function (message) {
    return confirm(message || 'Yakin ingin menghapus data ini?');
  };

  // Currency formatter (IDR)
  window.formatCurrency = function (val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
  };

  // Plain number formatter (Indonesian style: 19.592.202,00)
  window.formatNumber = function (val) {
    return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val);
  };

  // =============== AI Chat Widget ===============
  (function () {
    const overlay = document.createElement('div');
    overlay.className = 'ai-chat-overlay';
    overlay.innerHTML = `
      <button class="ai-fab" id="aiFab" title="Buka Asisten AI" aria-label="Buka Asisten AI">
        <i class="bi bi-robot"></i>
        <span class="ai-badge" id="aiBadge" style="display:none;">!</span>
      </button>
      <div class="ai-chat-panel" id="aiChatPanel" role="dialog" aria-modal="true" aria-label="Asisten AI">
        <div class="ai-chat-header">
          <div class="ai-chat-title"><i class="bi bi-robot"></i> Asisten AI</div>
          <span class="ai-chat-status <?= $aiStatusClass ?>" id="aiChatStatus"><?= $aiStatusText ?></span>
        </div>
        <div class="ai-chat-body" id="aiChatBody">
          <div class="ai-empty" id="aiEmpty">
            <i class="bi bi-chat-right-text"></i>
            <div>Mulai bertanya seputar data aplikasi</div>
          </div>
        </div>
        <div class="ai-chat-footer">
          <input type="text" id="aiInput" placeholder="Ketik pertanyaan Anda..." autocomplete="off">
          <button id="aiSend" title="Kirim"><i class="bi bi-send"></i></button>
        </div>
        <button class="btn-close" style="position:absolute; top:8px; right:8px; z-index:1; background:none; border:none; font-size:1.5rem; color:var(--muted); cursor:pointer;" id="aiClose" aria-label="Tutup">&times;</button>
      </div>
    `;
    document.body.appendChild(overlay);

    const fab = document.getElementById('aiFab');
    const panel = document.getElementById('aiChatPanel');
    const badge = document.getElementById('aiBadge');
    const closeBtn = document.getElementById('aiClose');
    const body = document.getElementById('aiChatBody');
    const input = document.getElementById('aiInput');
    const sendBtn = document.getElementById('aiSend');
    const empty = document.getElementById('aiEmpty');
    const status = document.getElementById('aiChatStatus');

    let unread = 0;

    function openChat() { overlay.classList.add('show'); panel.focus(); }
    function closeChat() { overlay.classList.remove('show'); unread = 0; badge.style.display = 'none'; }

    fab.addEventListener('click', openChat);
    closeBtn.addEventListener('click', closeChat);
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) closeChat();
    });

    function scrollBottom() { body.scrollTop = body.scrollHeight; }

    function addMessage(text, role, showBadge) {
      const now = new Date();
      const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
      const div = document.createElement('div');
      div.className = 'ai-msg ai-' + role;
      div.innerHTML = `
        <div class="ai-bubble">${text}</div>
        <div class="ai-meta">${role === 'user' ? 'Anda' : 'AI'} • ${time}</div>
      `;
      body.appendChild(div);
      if (empty) empty.style.display = 'none';
      scrollBottom();
      if (showBadge && !overlay.classList.contains('show')) {
        unread++; badge.style.display = 'flex'; badge.textContent = unread;
      }
      return div;
    }

    function setLoading(loading) {
      input.disabled = loading;
      sendBtn.disabled = loading;
    }

    async function sendMessage() {
      const text = input.value.trim();
      if (!text) return;
      addMessage(text, 'user');
      input.value = '';
      setLoading(true);

      const typing = addMessage('...', 'ai');
      try {
        const res = await fetch('api/ai-chat.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ message: text })
        });
        const data = await res.json();
        typing.remove();
        if (data.reply) {
          addMessage(data.reply, 'ai', true);
        } else {
          addMessage('Maaf, tidak dapat mendapatkan balasan dari AI.', 'ai', true);
        }
      } catch (err) {
        typing.remove();
        addMessage('Gagal terhubung ke server AI. Coba lagi nanti.', 'ai', true);
      } finally {
        setLoading(false);
      }
    }

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });
  })();
</script>
</body>
</html>