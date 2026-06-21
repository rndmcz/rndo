<?php
/**
 * views/components/floating-chat.php
 * Expects $post to be set
 */
$chatContext = $post['chatbot_context'] ?? strip_tags($post['content'] ?? '');
$chatContext = mb_substr($chatContext, 0, 4000); // Limit context size
?>
<div id="floating-chat-widget"
     class="sticky bottom-0 w-full z-40 pb-4 pt-0 transition-all duration-700 opacity-0 translate-y-4 pointer-events-none bg-gradient-to-t from-white via-white/90 to-transparent backdrop-blur-[1px] flex justify-center"
     aria-live="polite"
     aria-label="AI chat assistant">

    <div id="gemini-input-wrapper"
         class="w-full relative bg-white border border-gray-200 rounded-[20px] flex items-end p-1 transition-all focus-within:border-gray-300 group pointer-events-auto">

        <div class="pl-3 pr-1.5 pb-2" aria-hidden="true">
            <i class="bi bi-stars text-gray-300 group-focus-within:text-indigo-500 transition-colors text-base"></i>
        </div>

        <label for="ai-input" class="sr-only">Ask about this article</label>
        <textarea id="ai-input" rows="1"
                  placeholder="Ask about this article..."
                  aria-label="Ask a question about this article"
                  class="flex-1 bg-transparent border-none outline-none text-gray-800 placeholder-gray-400 py-3 text-[14px] md:text-[15px] font-medium resize-none max-h-32 overflow-y-auto leading-tight"></textarea>

        <div class="pb-1 pt-1 pl-1 pr-1">
            <button id="ai-send-btn"
                    aria-label="Send question to AI"
                    class="bg-youtube-dark text-white p-2 rounded-full hover:bg-youtube-red transition-all flex items-center justify-center h-8 w-8 disabled:opacity-20 disabled:cursor-not-allowed shadow-sm active:scale-95">
                <svg id="send-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3.5 h-3.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"/>
                </svg>
                <div id="btn-spinner" class="hidden w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin" aria-hidden="true"></div>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const widget       = document.getElementById('floating-chat-widget');
    const inputField   = document.getElementById('ai-input');
    const sendBtn      = document.getElementById('ai-send-btn');
    const chatContainer= document.getElementById('chat-container');
    const chatTitle    = document.getElementById('chat-title');

    let history       = [];
    let generating    = false;
    const articleCtx  = <?= json_encode($chatContext) ?>;

    window.addEventListener('scroll', () => {
        const show = window.scrollY > 400;
        widget.classList.toggle('opacity-0',        !show);
        widget.classList.toggle('translate-y-4',    !show);
        widget.classList.toggle('pointer-events-none', !show);
    }, { passive: true });

    inputField?.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
        sendBtn.disabled = !this.value.trim();
    });

    async function ask() {
        const prompt = inputField.value.trim();
        if (!prompt || generating) return;

        generating = true;
        sendBtn.disabled = true;
        document.getElementById('send-icon').classList.add('hidden');
        document.getElementById('btn-spinner').classList.remove('hidden');
        if (chatTitle) chatTitle.classList.remove('hidden');

        appendUserMsg(prompt);
        inputField.value = '';
        inputField.style.height = 'auto';

        const loadId = appendLoading();
        scrollDown();

        try {
            const res  = await fetch('/api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt, context: articleCtx, history })
            });
            const data = await res.json();
            document.getElementById(loadId)?.remove();

            if (data.answer) {
                const aiId = 'ai-' + Date.now();
                appendAISlot(aiId);
                await stream(data.answer, aiId);
                history.push({ role: 'user',      content: prompt });
                history.push({ role: 'assistant', content: data.answer });
            }
        } catch {
            document.getElementById(loadId)?.remove();
            appendAISlot('err-' + Date.now());
            document.getElementById('err-' + Date.now())?.insertAdjacentText('beforeend', 'Connection error. Please try again.');
        } finally {
            generating = false;
            sendBtn.disabled = false;
            document.getElementById('send-icon').classList.remove('hidden');
            document.getElementById('btn-spinner').classList.add('hidden');
            scrollDown();
        }
    }

    async function stream(text, id) {
        const el    = document.getElementById(id);
        const words = text.split(' ');
        let cur = '';
        el.classList.add('typing-active');
        for (let i = 0; i < words.length; i++) {
            cur += (i === 0 ? '' : ' ') + words[i];
            if (typeof marked !== 'undefined') el.innerHTML = marked.parse(cur);
            else el.textContent = cur;
            await new Promise(r => setTimeout(r, 35));
            if (i % 3 === 0) scrollDown();
        }
        el.classList.remove('typing-active');
    }

    function appendUserMsg(text) {
        chatContainer.insertAdjacentHTML('beforeend',
            `<div class="flex justify-end w-full mt-4">
               <div class="bg-gray-100 text-gray-800 px-4 py-2.5 rounded-2xl rounded-tr-none max-w-[85%] text-[14px] font-medium shadow-sm">${escHtml(text)}</div>
             </div>`);
    }
    function appendAISlot(id) {
        chatContainer.insertAdjacentHTML('beforeend',
            `<div class="flex items-start gap-4 w-full mt-8">
               <div class="mt-1"><i class="bi bi-stars text-indigo-500 text-xl" aria-hidden="true"></i></div>
               <div id="${id}" class="ai-response-content prose prose-sm max-w-none text-[15px] font-medium leading-relaxed pt-0.5"></div>
             </div>`);
    }
    function appendLoading() {
        const id = 'load-' + Date.now();
        chatContainer.insertAdjacentHTML('beforeend',
            `<div id="${id}" class="flex items-start gap-4 w-full mt-8">
               <div class="mt-1"><i class="bi bi-stars ai-pulse text-xl" aria-hidden="true"></i></div>
               <div class="flex-1 pt-2">
                 <div class="shimmer-line w-[30%]"></div>
                 <div class="shimmer-line w-[85%]"></div>
                 <div class="shimmer-line w-[60%]"></div>
               </div>
             </div>`);
        return id;
    }
    function scrollDown() {
        chatContainer.lastElementChild?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function escHtml(t) {
        const d = document.createElement('div');
        d.textContent = t;
        return d.innerHTML;
    }

    sendBtn.addEventListener('click', ask);
    inputField.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); ask(); }
    });
});
</script>
