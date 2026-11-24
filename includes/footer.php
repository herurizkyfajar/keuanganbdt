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
    // Overlay (backdrop + panel) — disembunyikan default, muncul saat FAB diklik
    const overlay = document.createElement('div');
    overlay.id = 'aiChatOverlay';
    overlay.className = 'ai-chat-overlay';
    overlay.innerHTML = `
      <div class="ai-chat-panel" id="aiChatPanel" role="dialog" aria-modal="true" aria-label="Asisten AI">
        <div class="ai-chat-header">
          <div class="ai-header-left">
            <div class="ai-avatar"><i class="bi bi-robot"></i></div>
            <div class="ai-header-info">
              <div class="ai-chat-title">Asisten AI</div>
              <div class="ai-chat-subtitle">Asisten pintar untuk membantu analisis data akuntansi Anda</div>
            </div>
          </div>
          <div class="ai-header-right">
            <span class="ai-status-badge"><span class="ai-status-dot"></span> <?= $aiStatusText ?></span>
            <button class="ai-close-btn" id="aiClose" aria-label="Tutup">&times;</button>
          </div>
        </div>

        <div class="ai-wave-top"></div>

        <div class="ai-chat-body" id="aiChatBody">
          <div class="ai-date-separator">Hari ini</div>

          <div class="ai-empty" id="aiEmpty">
            <i class="bi bi-chat-right-text"></i>
            <div>Mulai bertanya seputar data aplikasi</div>
          </div>
        </div>

        <div class="ai-wave-bottom"></div>

        <div class="ai-chat-footer">
          <div class="ai-input-wrapper">
            <i class="bi bi-star ai-input-icon"></i>
            <input type="text" id="aiInput" placeholder="Ketik pertanyaan Anda..." autocomplete="off">
            <button id="aiAttach" title="Lampiran"><i class="bi bi-paperclip"></i></button>
          </div>
          <button id="aiSend" title="Kirim"><i class="bi bi-send"></i></button>
        </div>
      </div>
    `;
    document.body.appendChild(overlay);

    // Tombol melayang (FAB) selalu tampil di kanan bawah
    const fab = document.createElement('button');
    fab.id = 'aiFab';
    fab.className = 'ai-fab';
    fab.title = 'Buka AI Chat';
    fab.setAttribute('aria-label', 'Buka AI Chat');
    fab.type = 'button';
    fab.innerHTML = `
      <img src="assets/img/ai-chat-button.png" alt="AI Chat" class="ai-fab-img">
      <span class="ai-badge" style="display:none;">!</span>
    `;
    document.body.appendChild(fab);

    const panel = document.getElementById('aiChatPanel');
    const unreadBadge = document.querySelector('.ai-badge');
    const closeBtn = document.getElementById('aiClose');
    const body = document.getElementById('aiChatBody');
    const input = document.getElementById('aiInput');
    const sendBtn = document.getElementById('aiSend');
    const empty = document.getElementById('aiEmpty');

    let unread = 0;
    let lastDateKey = null;

    function openChat() { overlay.classList.add('show'); panel.focus(); }
    function closeChat() { overlay.classList.remove('show'); unread = 0; unreadBadge.style.display = 'none'; }

    fab.addEventListener('click', openChat);
    closeBtn.addEventListener('click', closeChat);
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) closeChat();
    });

    function scrollBottom() { body.scrollTop = body.scrollHeight; }

    function addDateSeparator() {
      const now = new Date();
      const key = now.toDateString();
      if (key === lastDateKey) return;
      lastDateKey = key;
      const div = document.createElement('div');
      div.className = 'ai-date-separator';
      div.innerHTML = '<span>Hari ini</span>';
      body.appendChild(div);
      scrollBottom();
    }

    function addMessage(text, role, showBadge) {
      const now = new Date();
      const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
      addDateSeparator();
      const div = document.createElement('div');
      div.className = 'ai-msg ai-' + role;
      const isAI = role === 'ai';
      const avatar = isAI
        ? '<div class="ai-avatar-msg"><i class="bi bi-robot"></i></div>'
        : '';
      div.innerHTML = `
        ${avatar}
        <div class="ai-bubble">${text}</div>
        <div class="ai-meta">${isAI ? 'AI' : 'Anda'} • ${time}</div>
      `;
      if (!isAI) {
        // user message: move meta to right, bubble already row-reverse
        div.querySelector('.ai-meta').style.marginLeft = 'auto';
        div.querySelector('.ai-meta').style.textAlign = 'right';
      }
      body.appendChild(div);
      if (empty) empty.style.display = 'none';
      scrollBottom();
      if (showBadge && !overlay.classList.contains('show')) {
        unread++; unreadBadge.style.display = 'flex'; unreadBadge.textContent = unread;
      }
      return div;
    }

    function addTyping() {
      const div = document.createElement('div');
      div.className = 'ai-msg ai-ai';
      div.id = 'aiTyping';
      div.innerHTML = `
        <div class="ai-avatar-msg"><i class="bi bi-robot"></i></div>
        <div class="ai-typing"><span></span><span></span><span></span></div>
      `;
      body.appendChild(div);
      if (empty) empty.style.display = 'none';
      scrollBottom();
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

      const typing = addTyping();
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
  });
</script>
</body>
</html>