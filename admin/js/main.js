/**
 * NEETSTACK NEURAL ENGINE v2.0
 * Features:
 *  – Real word-by-word typewriter via SSE event stream
 *  – Live SEO score analyzer (client-side + server-side)
 *  – Auto-slug generator from title
 *  – Meta char counter
 *  – Keyword density counter
 *  – Readability helpers
 *  – JSON-LD schema preview
 *  – AI action modes: generate | rewrite | expand | seo_check
 *  – Copy-to-clipboard for schema
 *  – Tooltip tips inline
 */

// ═══════════════════════════════════════════════════════════════════════════════
// UTILITIES
// ═══════════════════════════════════════════════════════════════════════════════

function $(id) { return document.getElementById(id); }

/** Sleep helper */
const sleep = ms => new Promise(r => setTimeout(r, ms));

/** Set the SEO score ring & label */
function setScore(score, breakdown, tips) {
    const capped = Math.min(100, Math.max(0, Math.round(score)));
    $('vScore').textContent = capped;
    $('seoScoreHidden').value = capped;

    const offset = 283 - (283 * capped / 100);
    $('ringFill').setAttribute('stroke-dashoffset', offset);

    // Color the ring
    let color = '#000';
    if (capped >= 90) color = '#16a34a';
    else if (capped >= 75) color = '#65a30d';
    else if (capped >= 60) color = '#d97706';
    else if (capped < 60)  color = '#dc2626';
    $('ringFill').setAttribute('stroke', color);

    // Render breakdown if provided
    if (breakdown && $('seoBreakdown')) {
        renderSeoBreakdown(breakdown, tips || []);
    }
}

/** Render the detailed SEO breakdown panel */
function renderSeoBreakdown(breakdown, tips) {
    const panel = $('seoBreakdown');
    if (!panel) return;
    panel.innerHTML = '';

    const labels = {
        title_keyword:  'Keyword in Title',
        title_length:   'Title Length',
        meta_keyword:   'Keyword in Meta',
        meta_length:    'Meta Length',
        content_length: 'Content Length',
        keyword_density:'Keyword Density',
        headings:       'H2/H3 Structure',
        keyword_in_h2:  'Keyword in H2',
        schema:         'Schema Markup',
        slug:           'URL Slug',
        lists:          'Lists & Tables',
        faq:            'FAQ Section',
        chatbot:        'AI Context',
        readability:    'Readability',
        first_para_kw:  'Keyword First Para',
        blockquote:     'Blockquote',
    };

    const maxes = {
        title_keyword:10, title_length:5, meta_keyword:8, meta_length:5,
        content_length:15, keyword_density:15, headings:10, keyword_in_h2:5,
        schema:12, slug:5, lists:5, faq:5, chatbot:5, readability:5,
        first_para_kw:5, blockquote:3,
    };

    for (const [key, val] of Object.entries(breakdown)) {
        const max = maxes[key] || 10;
        const pct = Math.round((val / max) * 100);
        const barColor = pct >= 80 ? '#16a34a' : pct >= 50 ? '#d97706' : '#dc2626';
        const row = document.createElement('div');
        row.className = 'seo-row';
        row.innerHTML = `
            <div class="seo-row-label">${labels[key] || key}</div>
            <div class="seo-row-bar-wrap">
                <div class="seo-row-bar" style="width:${pct}%;background:${barColor}"></div>
            </div>
            <div class="seo-row-score" style="color:${barColor}">${val}/${max}</div>
        `;
        panel.appendChild(row);
    }

    if (tips.length > 0 && $('seoTips')) {
        $('seoTips').innerHTML = tips.map(t =>
            `<div class="seo-tip">${t}</div>`
        ).join('');
    }
}

/** Type text word-by-word into a textarea, with scroll tracking */
async function typeIntoField(elementId, text, speed = 8) {
    const el = $(elementId);
    if (!el) return;
    el.value = '';
    const chunks = text.split(/(\s+)/); // split on whitespace, keep separators
    for (const chunk of chunks) {
        el.value += chunk;
        if (el.tagName === 'TEXTAREA') el.scrollTop = el.scrollHeight;
        if (chunk.trim().length > 0) await sleep(speed);
    }
    el.dispatchEvent(new Event('input'));
}

/** Auto-generate slug from text */
function slugify(text) {
    return text
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .substring(0, 80);
}

/** Count words in a string */
function wordCount(str) {
    return str.trim() ? str.trim().split(/\s+/).length : 0;
}

/** Keyword density % */
function kwDensity(content, keyword) {
    if (!keyword || !content) return 0;
    const plain = content.replace(/<[^>]+>/g, ' ').toLowerCase();
    const total = wordCount(plain);
    if (total === 0) return 0;
    const kw = keyword.toLowerCase();
    const matches = (plain.match(new RegExp(kw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g')) || []).length;
    return ((matches / total) * 100).toFixed(1);
}

// ═══════════════════════════════════════════════════════════════════════════════
// UI INIT
// ═══════════════════════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {

    // ── Meta description char counter ──────────────────────────────────────────
    const mDesc = $('mDesc');
    const metaCharCount = $('metaCharCount');
    if (mDesc && metaCharCount) {
        const updateMeta = () => {
            const len = mDesc.value.length;
            metaCharCount.textContent = `${len}/160`;
            metaCharCount.style.color = len > 160 ? '#dc2626' : len >= 130 ? '#16a34a' : '#9ca3af';
        };
        mDesc.addEventListener('input', updateMeta);
        updateMeta();
    }

    // ── Color dot live preview ─────────────────────────────────────────────────
    const pColor = $('pColor');
    const colorDot = $('colorDot');
    if (pColor && colorDot) {
        pColor.addEventListener('input', () => {
            const v = pColor.value;
            if (/^#[0-9A-Fa-f]{6}$/.test(v)) colorDot.style.background = v;
        });
    }

    // ── Auto-slug from title ───────────────────────────────────────────────────
    const postTitle = $('postTitle');
    const pSlug = $('pSlug');
    if (postTitle && pSlug) {
        postTitle.addEventListener('input', () => {
            if (!pSlug.dataset.manual) {
                pSlug.value = slugify(postTitle.value);
            }
        });
        pSlug.addEventListener('input', () => { pSlug.dataset.manual = '1'; });
    }

    // ── Live keyword density display ───────────────────────────────────────────
    const fKeyword = $('fKeyword');
    const postContent = $('postContent');
    const densityLabel = $('kwDensity');
    const updateDensity = () => {
        if (!densityLabel || !fKeyword || !postContent) return;
        const d = kwDensity(postContent.value, fKeyword.value);
        densityLabel.textContent = `Density: ${d}%`;
        densityLabel.style.color = (d >= 0.5 && d <= 2.5) ? '#16a34a' : '#dc2626';
    };
    if (fKeyword) fKeyword.addEventListener('input', updateDensity);
    if (postContent) postContent.addEventListener('input', () => {
        updateDensity();
        updateWordCount();
    });

    // ── Word count display ────────────────────────────────────────────────────
    const wordCountLabel = $('wordCountLabel');
    const updateWordCount = () => {
        if (!wordCountLabel || !postContent) return;
        const plain = postContent.value.replace(/<[^>]+>/g, ' ');
        const wc = wordCount(plain);
        wordCountLabel.textContent = `${wc} words`;
        wordCountLabel.style.color = wc >= 1500 ? '#16a34a' : wc >= 800 ? '#d97706' : '#dc2626';
    };
    updateWordCount();

    // ── Schema copy button ────────────────────────────────────────────────────
    const copySchemaBtn = $('copySchemaBtn');
    const sMarkup = $('sMarkup');
    if (copySchemaBtn && sMarkup) {
        copySchemaBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(sMarkup.value).then(() => {
                copySchemaBtn.textContent = '✓ Copied!';
                setTimeout(() => { copySchemaBtn.textContent = 'Copy'; }, 2000);
            });
        });
    }

    // ── Run SEO Check Button ──────────────────────────────────────────────────
    const seoCheckBtn = $('seoCheckBtn');
    if (seoCheckBtn) {
        seoCheckBtn.addEventListener('click', runSeoCheck);
    }

    // Initial SEO score from PHP
    const initScore = parseInt($('vScore')?.textContent || '0');
    if (initScore > 0) {
        setScore(initScore);
    }
});

// ═══════════════════════════════════════════════════════════════════════════════
// SEO ANALYZER (Server-side)
// ═══════════════════════════════════════════════════════════════════════════════

async function runSeoCheck() {
    const btn = $('seoCheckBtn');
    if (btn) { btn.textContent = 'Analyzing...'; btn.disabled = true; }

    try {
        const payload = {
            title:   $('postTitle')?.value  || '',
            meta:    $('metaTitleHidden')?.value || '',
            desc:    $('mDesc')?.value      || '',
            content: $('postContent')?.value || '',
            keyword: $('fKeyword')?.value   || '',
            slug:    $('pSlug')?.value      || '',
            schema:  $('sMarkup')?.value    || '',
            chatbot: $('chatbotContextVisible')?.value || '',
        };

        const res  = await fetch('/admin/ai/seo-check.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.error) throw new Error(data.error);

        setScore(data.score, data.breakdown, data.tips);

        // Update stats labels
        if ($('statWords'))   $('statWords').textContent   = data.stats.words;
        if ($('statH2'))      $('statH2').textContent      = data.stats.h2_count;
        if ($('statTitleLen'))$('statTitleLen').textContent = data.stats.title_len + ' chars';

    } catch (err) {
        console.error('SEO Check failed:', err);
        showError('SEO analysis failed: ' + err.message);
    } finally {
        if (btn) { btn.textContent = '⟳ Re-analyze SEO'; btn.disabled = false; }
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// AI AUTOPILOT — Main Generator
// ═══════════════════════════════════════════════════════════════════════════════

window.omniAutoPilot = async function(action = 'generate') {
    const btn        = $('aiBtn');
    const statusBox  = $('aiStatus');
    const statusText = $('aiStatusText');
    const errBox     = $('jsErrorBox');
    const liveStream = $('liveStreamPreview');

    const cmd        = $('aiCommand')?.value.trim();
    const content    = $('postContent')?.value.trim();
    const keyword    = $('fKeyword')?.value.trim();
    const category   = $('pCat')?.value;
    const modelSel   = $('activeModel');

    // Validation
    if (!modelSel?.value) {
        alert('⚠️ Please select an AI model from the sidebar first.');
        return;
    }
    if (!cmd && action === 'generate') {
        alert('⚠️ Please enter instructions in the "Context Instruction" field.');
        return;
    }

    const model    = modelSel.value;
    const provider = modelSel.options[modelSel.selectedIndex].dataset.provider;

    // UI: start state
    if (btn)       { btn.disabled = true; btn.textContent = '[ Generating... ]'; }
    if (statusBox) { statusBox.classList.remove('hidden'); statusBox.classList.add('flex'); }
    if (errBox)    errBox.classList.add('hidden');
    if (liveStream){ liveStream.textContent = ''; liveStream.classList.remove('hidden'); }

    const setStatus = msg => { if (statusText) statusText.textContent = msg; };
    setStatus('Establishing neural link...');

    let fullBuffer = '';
    let tokenCount = 0;

    try {
        // ── Open SSE connection to proxy ──────────────────────────────────────
        const res = await fetch('/admin/ai/proxy.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ command: cmd, content, model, provider, keyword, category, action }),
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);

        const reader   = res.body.getReader();
        const decoder  = new TextDecoder();
        let   rawBuf   = '';
        let   evType   = '';

        // ── Read SSE stream ───────────────────────────────────────────────────
        while (true) {
            const { value, done } = await reader.read();
            if (done) break;

            rawBuf += decoder.decode(value, { stream: true });
            const lines = rawBuf.split('\n');
            rawBuf = lines.pop(); // keep incomplete last line

            for (const line of lines) {
                if (line.startsWith('event: ')) {
                    evType = line.slice(7).trim();
                    continue;
                }

                if (!line.startsWith('data: ')) continue;
                const payload = line.slice(6).trim();
                if (!payload) continue;

                let parsed;
                try { parsed = JSON.parse(payload); } catch (e) {
                    console.warn('[AI SSE] payload parse failed, skipping', e.message, payload);
                    continue;
                }

                if (evType === 'status') {
                    setStatus(parsed.msg);

                } else if (evType === 'token') {
                    fullBuffer += parsed.t || '';
                    tokenCount++;
                    if (liveStream) {
                        liveStream.textContent = fullBuffer.slice(-500);
                    }
                    setStatus(`⚡ Streaming... ${tokenCount} tokens received`);

                } else if (evType === 'result') {
                    await applyResultToEditor(parsed, setStatus);

                } else if (evType === 'error') {
                    throw new Error(parsed.message || 'Unknown AI error');

                } else if (evType === 'done') {
                    if (!fullBuffer.includes('"title"')) return;
                }

                evType = '';
            }
        }

        // ── Fallback: parse fullBuffer if 'result' event was missed ──────────
        if (fullBuffer && fullBuffer.includes('"title"')) {
            setStatus('Parsing AI output...');
            const jsonText = extractJsonObject(fullBuffer);
            if (!jsonText) {
                throw new Error('Could not locate valid JSON in AI output. Please retry.');
            }

            const data = tryParseAIJson(jsonText);
            if (!data) {
                throw new Error('Could not parse AI JSON after automatic repair attempts. Please retry with a shorter prompt or different model.');
            }

            await applyResultToEditor(data, setStatus);
        }

        setStatus('✓ Complete! Running SEO analysis...');
        setTimeout(runSeoCheck, 500);
        setTimeout(() => {
            statusBox?.classList.add('hidden');
            statusBox?.classList.remove('flex');
            liveStream?.classList.add('hidden');
        }, 3000);

    } catch (err) {
        console.error('[NEETSTACK] AI Error:', err);
        statusBox?.classList.add('hidden');
        liveStream?.classList.add('hidden');
        showError(err.message);
    } finally {
        if (btn) { btn.disabled = false; btn.textContent = '[ AutoPilot_AI ]'; }
    }
};

function extractJsonObject(text) {
    const start = text.indexOf('{');
    const end   = text.lastIndexOf('}');
    if (start === -1 || end === -1 || end <= start) return null;
    return text.substring(start, end + 1);
}

function cleanAiJsonString(raw) {
    let value = raw;
    value = value.replace(/```(?:json)?/g, '');
    value = value.replace(/[ -]/g, '');
    value = value.replace(/,\s*([\}\]])/g, '$1');
    value = value.replace(/\r\n/g, '\n');
    return value;
}

function balanceJsonString(raw) {
    const openBraces   = (raw.match(/{/g) || []).length;
    const closeBraces  = (raw.match(/}/g) || []).length;
    const openBrackets = (raw.match(/\[/g) || []).length;
    const closeBrackets= (raw.match(/\]/g) || []).length;
    let value = raw;
    if (closeBraces < openBraces) {
        value += '}'.repeat(openBraces - closeBraces);
    }
    if (closeBrackets < openBrackets) {
        value += ']'.repeat(openBrackets - closeBrackets);
    }
    return value;
}

function tryParseAIJson(raw) {
    try {
        return JSON.parse(raw);
    } catch (firstError) {
        const cleaned = cleanAiJsonString(raw);
        try {
            return JSON.parse(cleaned);
        } catch (secondError) {
            const balanced = balanceJsonString(cleaned);
            try {
                return JSON.parse(balanced);
            } catch (thirdError) {
                return null;
            }
        }
    }
}

function tryParseAIJson(raw) {
    try {
        return JSON.parse(raw);
    } catch (firstError) {
        const cleaned = cleanAiJsonString(raw);
        try {
            return JSON.parse(cleaned);
        } catch (secondError) {
            const balanced = balanceJsonString(cleaned);
            try {
                return JSON.parse(balanced);
            } catch (thirdError) {
                return null;
            }
        }
    }
}

/** Apply the parsed AI JSON to the editor fields */
async function applyResultToEditor(data, setStatus) {
    setStatus('Writing headline...');
    if ($('postTitle') && data.title)
        await typeIntoField('postTitle', data.title, 12);

    setStatus('Writing article content word by word...');
    if ($('postContent') && data.content)
        await typeIntoField('postContent', data.content, 5);

    setStatus('Filling metadata...');
    if ($('pSlug')           && data.slug)           { $('pSlug').value = data.slug; $('pSlug').dataset.manual = '1'; }
    if ($('fKeyword')        && data.keyword)        $('fKeyword').value = data.keyword;
    if ($('mDesc')           && data.desc)           { $('mDesc').value = data.desc; $('mDesc').dispatchEvent(new Event('input')); }
    if ($('sMarkup')         && data.schema)         $('sMarkup').value = typeof data.schema === 'object' ? JSON.stringify(data.schema, null, 2) : data.schema;
    if ($('chatbotContextVisible') && data.chatbot_context) {
        $('chatbotContextVisible').value = data.chatbot_context;
        $('chatbotContextVisible').dispatchEvent(new Event('input'));
    }
    if ($('chatbotContextHidden') && data.chatbot_context) {
        $('chatbotContextHidden').value = data.chatbot_context;
    }
    if ($('metaTitleHidden') && data.meta_title)     $('metaTitleHidden').value = data.meta_title;

    if ($('pColor') && data.color) {
        $('pColor').value = data.color;
        if ($('colorDot')) $('colorDot').style.background = data.color;
    }

    if (data.seo_score) {
        setScore(data.seo_score, data.seo_breakdown || null, []);
    }

    // Fill LSI keywords display
    if (data.lsi_keywords && $('lsiKeywords')) {
        $('lsiKeywords').innerHTML = data.lsi_keywords.map(k =>
            `<span class="lsi-tag">${k}</span>`
        ).join('');
    }

    setStatus('✓ All fields populated!');
}

/** Show an error in the error box */
function showError(msg) {
    const errBox = $('jsErrorBox');
    if (errBox) {
        errBox.classList.remove('hidden');
        errBox.innerHTML = `<strong>[ ⚠ NEURAL_LINK_FAILED ]</strong><br>${msg}`;
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// EXPOSE GLOBALS
// ═══════════════════════════════════════════════════════════════════════════════
window.setScore     = setScore;
window.runSeoCheck  = runSeoCheck;
window.slugify      = slugify;
