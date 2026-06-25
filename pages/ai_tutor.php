<?php
require_once '../includes/config.php';
// Restrict access to logged in users
if(!isUserLoggedIn()) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Tutor — TOEFLMaster</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* Markdown Content Styles */
.ai-msg-content p { margin-bottom: 10px; }
.ai-msg-content p:last-child { margin-bottom: 0; }
.ai-msg-content strong { font-weight: 700; color: #0F172A; }
.ai-msg-content ul, .ai-msg-content ol { margin-left: 20px; margin-bottom: 10px; }
.ai-msg-content li { margin-bottom: 4px; }
.ai-msg-content code { background: #E2E8F0; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 0.85em; }
</style>
</head>
<body style="background:#F8FAFC;">
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div style="padding-top:70px;">
  <!-- Hero / Header AI -->
  <div style="background:linear-gradient(135deg,#0F172A,#1E293B);padding:60px 5% 40px;text-align:center;" data-aos="fade-down">
    <div style="width:70px;height:70px;background:linear-gradient(135deg,#2563EB,#3B82F6);border-radius:20px;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;color:white;margin-bottom:16px;">
      <i class="fas fa-robot"></i>
    </div>
    <h1 style="color:white;font-size:2rem;margin-bottom:10px;">TOEFL AI Tutor</h1>
    <p style="color:#94A3B8;max-width:500px;margin:0 auto;font-size:0.95rem;">Asisten cerdas Anda untuk belajar TOEFL. Tanyakan materi, minta penjelasan grammar, atau latih pemahaman reading Anda di sini.</p>
  </div>

  <div style="max-width:900px;margin:40px auto;padding:0 5% 80px;" data-aos="fade-up">
    <!-- Chat Interface Container -->
    <div style="background:white;border-radius:20px;border:1px solid #E2E8F0;min-height:500px;max-height:70vh;display:flex;flex-direction:column;box-shadow:0 10px 30px rgba(0,0,0,0.02);">
      
      <!-- Chat Header -->
      <div style="padding:20px 24px;border-bottom:1px solid #E2E8F0;display:flex;align-items:center;gap:14px;background:white;border-radius:20px 20px 0 0;z-index:10;">
        <div style="width:40px;height:40px;background:#EFF6FF;color:#2563EB;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
          <i class="fas fa-robot"></i>
        </div>
        <div>
          <h2 style="font-size:1rem;margin:0;color:#0F172A;">AI Tutor Assistant</h2>
          <div style="font-size:0.8rem;color:#10B981;font-weight:600;"><i class="fas fa-circle" style="font-size:0.5rem;margin-right:4px;"></i>Online</div>
        </div>
      </div>

      <!-- Chat Body -->
      <div id="chatBody" style="flex:1;padding:24px;background:#F8FAFC;overflow-y:auto;display:flex;flex-direction:column;gap:16px;">
        
        <!-- AI Initial Message -->
        <div style="display:flex;gap:12px;align-items:flex-start;">
          <div style="width:36px;height:36px;background:#2563EB;color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
            <i class="fas fa-robot"></i>
          </div>
          <div style="background:white;padding:14px 18px;border-radius:0 16px 16px 16px;border:1px solid #E2E8F0;max-width:80%;box-shadow:0 2px 4px rgba(0,0,0,0.02);">
            <p style="margin:0;font-size:0.9rem;color:#334155;line-height:1.6;">Halo <strong><?= sanitize($_SESSION['username'] ?? 'Student') ?></strong>! Saya adalah AI Tutor Anda. Ada pertanyaan spesifik tentang materi TOEFL? Atau butuh penjelasan ulang tentang <em>Subject-Verb Agreement</em>?</p>
          </div>
        </div>

      </div>

      <!-- Chat Input -->
      <div style="padding:20px 24px;border-top:1px solid #E2E8F0;background:white;border-radius:0 0 20px 20px;">
        <form id="chatForm" style="display:flex;gap:12px;">
          <input type="text" id="chatInput" class="form-control" placeholder="Ketik pertanyaan Anda tentang TOEFL di sini..." style="flex:1;background:#F1F5F9;border:1px solid #E2E8F0;border-radius:100px;padding:12px 20px;font-size:0.9rem;" autocomplete="off" required>
          <button type="submit" id="chatSubmit" class="btn btn-primary" style="width:46px;height:46px;border-radius:50%;padding:0;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-paper-plane"></i>
          </button>
        </form>
      </div>
      
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatSubmit = document.getElementById('chatSubmit');
    const chatBody = document.getElementById('chatBody');

    // Simple markdown to HTML parser for AI responses
    function parseMarkdown(text) {
        let html = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>'); // bold
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>'); // italic
        html = html.replace(/`(.*?)`/g, '<code>$1</code>'); // inline code
        html = html.replace(/\n\n/g, '</p><p>'); // paragraphs
        html = html.replace(/\n/g, '<br>'); // line breaks
        return '<p>' + html + '</p>';
    }

    function scrollToBottom() {
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function appendUserMessage(text) {
        const msgDiv = document.createElement('div');
        msgDiv.style.cssText = 'display:flex;gap:12px;align-items:flex-start;flex-direction:row-reverse;';
        msgDiv.innerHTML = `
          <div style="width:36px;height:36px;background:#64748B;color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
            <i class="fas fa-user"></i>
          </div>
          <div style="background:#2563EB;color:white;padding:14px 18px;border-radius:16px 0 16px 16px;max-width:80%;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
            <p style="margin:0;font-size:0.9rem;line-height:1.6;">${text}</p>
          </div>
        `;
        chatBody.appendChild(msgDiv);
        scrollToBottom();
    }

    function appendAIMessage(text, isError = false) {
        const msgDiv = document.createElement('div');
        msgDiv.style.cssText = 'display:flex;gap:12px;align-items:flex-start;';
        
        const bgColor = isError ? '#FEE2E2' : 'white';
        const textColor = isError ? '#991B1B' : '#334155';
        const borderColor = isError ? '#F87171' : '#E2E8F0';
        
        msgDiv.innerHTML = `
          <div style="width:36px;height:36px;background:${isError?'#EF4444':'#2563EB'};color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
            <i class="fas ${isError?'fa-exclamation-triangle':'fa-robot'}"></i>
          </div>
          <div class="ai-msg-content" style="background:${bgColor};color:${textColor};padding:14px 18px;border-radius:0 16px 16px 16px;border:1px solid ${borderColor};max-width:80%;box-shadow:0 2px 4px rgba(0,0,0,0.02);font-size:0.9rem;line-height:1.6;">
            ${parseMarkdown(text)}
          </div>
        `;
        chatBody.appendChild(msgDiv);
        scrollToBottom();
    }

    function appendLoading() {
        const msgDiv = document.createElement('div');
        msgDiv.id = 'loadingIndicator';
        msgDiv.style.cssText = 'display:flex;gap:12px;align-items:center;';
        msgDiv.innerHTML = `
          <div style="width:36px;height:36px;background:#E2E8F0;color:#94A3B8;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
            <i class="fas fa-robot"></i>
          </div>
          <div style="background:white;padding:14px 18px;border-radius:0 16px 16px 16px;border:1px solid #E2E8F0;color:#94A3B8;font-size:0.85rem;font-style:italic;">
            Sedang berpikir... <i class="fas fa-circle-notch fa-spin"></i>
          </div>
        `;
        chatBody.appendChild(msgDiv);
        scrollToBottom();
    }

    function removeLoading() {
        const loading = document.getElementById('loadingIndicator');
        if (loading) loading.remove();
    }

    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const msg = chatInput.value.trim();
        if (!msg) return;

        // Display user msg
        appendUserMessage(msg);
        chatInput.value = '';
        
        // Disable input while waiting
        chatInput.disabled = true;
        chatSubmit.disabled = true;
        chatSubmit.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';

        appendLoading();

        try {
            const response = await fetch('<?= SITE_URL ?>/ajax/chat_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msg })
            });
            
            removeLoading();
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            
            if (data.status === 'success') {
                appendAIMessage(data.message);
            } else {
                appendAIMessage(data.message, true);
            }
        } catch (error) {
            removeLoading();
            appendAIMessage("Maaf, terjadi kesalahan pada jaringan atau server. Silakan coba lagi.", true);
            console.error('Error:', error);
        } finally {
            chatInput.disabled = false;
            chatSubmit.disabled = false;
            chatSubmit.innerHTML = '<i class="fas fa-paper-plane"></i>';
            chatInput.focus();
        }
    });
});
</script>
</body>
</html>
