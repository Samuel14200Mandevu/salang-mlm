function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function scrollThreadToBottom(thread) {
    if (!thread) {
        return;
    }
    thread.scrollTop = thread.scrollHeight;
}

function buildBotBubbleHtml(reply) {
    let html =
        '<div class="member-assistant-msg member-assistant-msg--bot" data-assistant-msg="bot">' +
        '<div class="member-assistant-msg__avatar" aria-hidden="true">S</div>' +
        '<div class="member-assistant-msg__bubble">';

    if (reply.title) {
        html += '<p class="member-assistant-msg__title">' + escapeHtml(reply.title) + '</p>';
    }
    html += '<p class="member-assistant-msg__text">' + escapeHtml(reply.body || '') + '</p>';

    if (reply.links && reply.links.length) {
        html += '<div class="member-assistant-msg__actions">';
        reply.links.forEach(function (link) {
            html +=
                '<a href="' +
                escapeHtml(link.url) +
                '" class="member-assistant-msg__action">' +
                escapeHtml(link.label) +
                '</a>';
        });
        html += '</div>';
    }

    if (reply.related && reply.related.length) {
        html += '<p class="member-assistant-msg__related-label">Voir aussi</p><ul class="member-assistant-msg__related">';
        reply.related.forEach(function (item) {
            if (item.url) {
                html +=
                    '<li><a href="' +
                    escapeHtml(item.url) +
                    '">' +
                    escapeHtml(item.title) +
                    '</a></li>';
            } else {
                html += '<li>' + escapeHtml(item.title) + '</li>';
            }
        });
        html += '</ul>';
    }

    html += '</div></div>';
    return html;
}

function appendUserMessage(thread, text) {
    const el = document.createElement('div');
    el.className = 'member-assistant-msg member-assistant-msg--user';
    el.setAttribute('data-assistant-msg', 'user');
    el.innerHTML =
        '<div class="member-assistant-msg__bubble"><p class="member-assistant-msg__text">' +
        escapeHtml(text) +
        '</p></div>';
    thread.appendChild(el);
    scrollThreadToBottom(thread);
}

function appendTyping(thread) {
    const el = document.createElement('div');
    el.className = 'member-assistant-msg member-assistant-msg--bot member-assistant-msg--typing';
    el.setAttribute('data-assistant-typing', '');
    el.innerHTML =
        '<div class="member-assistant-msg__avatar" aria-hidden="true">S</div>' +
        '<div class="member-assistant-msg__bubble member-assistant-msg__bubble--typing">' +
        '<span></span><span></span><span></span></div>';
    thread.appendChild(el);
    scrollThreadToBottom(thread);
    return el;
}

function removeTyping(thread) {
    thread.querySelectorAll('[data-assistant-typing]').forEach(function (node) {
        node.remove();
    });
}

function initAssistantChat(chat) {
    if (!chat || chat.dataset.assistantInit === '1') {
        return;
    }
    chat.dataset.assistantInit = '1';

    const thread = chat.querySelector('[data-assistant-thread]');
    const form = chat.querySelector('[data-assistant-form]');
    const input = chat.querySelector('[data-assistant-input]');
    const searchUrl = chat.dataset.searchUrl;
    const contactUrl = chat.dataset.contactUrl || '/contact';

    if (!thread || !form || !input || !searchUrl) {
        return;
    }

    let busy = false;

    function sendMessage(text) {
        const message = (text || '').trim();
        if (!message || busy) {
            return;
        }

        busy = true;
        appendUserMessage(thread, message);
        input.value = '';
        appendTyping(thread);

        const url = new URL(searchUrl, window.location.origin);
        url.searchParams.set('message', message);

        fetch(url.toString(), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })
            .then(function (res) {
                if (!res.ok) {
                    throw new Error('failed');
                }
                return res.json();
            })
            .then(function (data) {
                removeTyping(thread);
                const reply = data.reply || {};
                thread.insertAdjacentHTML('beforeend', buildBotBubbleHtml(reply));
                scrollThreadToBottom(thread);
            })
            .catch(function () {
                removeTyping(thread);
                thread.insertAdjacentHTML(
                    'beforeend',
                    buildBotBubbleHtml({
                        title: 'Erreur',
                        body: 'Impossible d’obtenir une réponse. Réessayez ou contactez le support.',
                        links: [{ label: 'Nous contacter', url: contactUrl }],
                        related: [],
                    })
                );
                scrollThreadToBottom(thread);
            })
            .finally(function () {
                busy = false;
            });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        sendMessage(input.value);
    });

    chat.querySelectorAll('[data-assistant-prompt]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            sendMessage(btn.dataset.assistantPrompt || btn.textContent || '');
        });
    });

    scrollThreadToBottom(thread);

    return { sendMessage, focusInput: function () { input.focus(); } };
}

const FAB_POS_KEY = 'salang_assistant_fab_pos';
const FAB_ENABLED_KEY = 'salang_assistant_fab_enabled';

function isAssistantFabEnabled() {
    try {
        return localStorage.getItem(FAB_ENABLED_KEY) !== '0';
    } catch (e) {
        return true;
    }
}

function applyAssistantFabVisibility() {
    const enabled = isAssistantFabEnabled();
    document.querySelectorAll('[data-assistant-fab-root]').forEach(function (root) {
        root.classList.toggle('is-hidden', !enabled);
        root.setAttribute('aria-hidden', enabled ? 'false' : 'true');
        if (!enabled) {
            root.classList.remove('is-open');
            const panel = root.querySelector('[data-assistant-fab-panel]');
            const fab = root.querySelector('[data-assistant-fab]');
            if (panel) {
                panel.hidden = true;
            }
            if (fab) {
                fab.setAttribute('aria-expanded', 'false');
            }
        }
    });
    syncAssistantFabToggleButtons(enabled);
}

function syncAssistantFabToggleButtons(enabled) {
    document.querySelectorAll('[data-assistant-fab-switch]').forEach(function (input) {
        input.checked = enabled;
        const label = input.closest('.profile-assistant-fab-switch');
        if (label) {
            label.classList.toggle('is-on', enabled);
        }
        input.setAttribute(
            'aria-label',
            enabled ? 'Désactiver l’assistant flottant' : 'Activer l’assistant flottant'
        );
    });
}

function setAssistantFabEnabled(enabled) {
    try {
        localStorage.setItem(FAB_ENABLED_KEY, enabled ? '1' : '0');
    } catch (e) {
        /* ignore */
    }
    applyAssistantFabVisibility();
    window.dispatchEvent(
        new CustomEvent('salang-assistant-fab-changed', { detail: { enabled: !!enabled } })
    );
    if (!enabled && typeof window.showToast === 'function') {
        window.showToast('Assistant flottant désactivé.', 'success', 3200);
    }
}

applyAssistantFabVisibility();

function clampFabPosition(root, x, y) {
    const margin = 8;
    const w = root.offsetWidth || 56;
    const h = root.offsetHeight || 56;
    const maxX = window.innerWidth - w - margin;
    const maxY = window.innerHeight - h - margin;
    return {
        x: Math.min(Math.max(margin, x), maxX),
        y: Math.min(Math.max(margin, y), maxY),
    };
}

function applyFabPosition(root, x, y) {
    const clamped = clampFabPosition(root, x, y);
    root.style.left = clamped.x + 'px';
    root.style.top = clamped.y + 'px';
    root.style.right = 'auto';
    root.style.bottom = 'auto';
    return clamped;
}

function initAssistantFab(root) {
    const fab = root.querySelector('[data-assistant-fab]');
    const panel = root.querySelector('[data-assistant-fab-panel]');
    const chat = panel ? panel.querySelector('[data-assistant-chat]') : null;

    if (!fab || !panel || !chat) {
        return;
    }

    const chatApi = initAssistantChat(chat);

    try {
        const saved = JSON.parse(localStorage.getItem(FAB_POS_KEY) || 'null');
        if (saved && typeof saved.x === 'number' && typeof saved.y === 'number') {
            applyFabPosition(root, saved.x, saved.y);
        }
    } catch (e) {
        /* ignore */
    }

    function setOpen(open) {
        panel.hidden = !open;
        fab.setAttribute('aria-expanded', open ? 'true' : 'false');
        root.classList.toggle('is-open', open);
        if (open && chatApi) {
            chatApi.focusInput();
        }
    }

    panel.querySelectorAll('[data-assistant-panel-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setOpen(false);
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && root.classList.contains('is-open')) {
            setOpen(false);
        }
    });

    let dragging = false;
    let didDrag = false;
    let startX = 0;
    let startY = 0;
    let originX = 0;
    let originY = 0;
    let pointerId = null;

    function onPointerMove(e) {
        if (!dragging || e.pointerId !== pointerId) {
            return;
        }
        const dx = e.clientX - startX;
        const dy = e.clientY - startY;
        if (Math.abs(dx) + Math.abs(dy) > 6) {
            didDrag = true;
        }
        const pos = applyFabPosition(root, originX + dx, originY + dy);
        e.preventDefault();
    }

    function onPointerUp(e) {
        if (!dragging || e.pointerId !== pointerId) {
            return;
        }
        dragging = false;
        fab.classList.remove('is-dragging');
        try {
            fab.releasePointerCapture(pointerId);
        } catch (err) {
            /* ignore */
        }
        pointerId = null;

        const rect = root.getBoundingClientRect();
        localStorage.setItem(
            FAB_POS_KEY,
            JSON.stringify({ x: rect.left, y: rect.top })
        );

        window.removeEventListener('pointermove', onPointerMove);
        window.removeEventListener('pointerup', onPointerUp);
        window.removeEventListener('pointercancel', onPointerUp);

        if (!didDrag) {
            setOpen(!root.classList.contains('is-open'));
        }
    }

    fab.addEventListener('pointerdown', function (e) {
        if (e.button !== 0 && e.pointerType === 'mouse') {
            return;
        }
        dragging = true;
        didDrag = false;
        pointerId = e.pointerId;
        startX = e.clientX;
        startY = e.clientY;

        const rect = root.getBoundingClientRect();
        originX = rect.left;
        originY = rect.top;
        applyFabPosition(root, originX, originY);

        fab.classList.add('is-dragging');
        try {
            fab.setPointerCapture(pointerId);
        } catch (err) {
            /* ignore */
        }

        window.addEventListener('pointermove', onPointerMove, { passive: false });
        window.addEventListener('pointerup', onPointerUp);
        window.addEventListener('pointercancel', onPointerUp);
    });

    window.addEventListener('resize', function () {
        const rect = root.getBoundingClientRect();
        applyFabPosition(root, rect.left, rect.top);
    });
}

function closeAssistantFabPanels() {
    document.querySelectorAll('[data-assistant-fab-root]').forEach(function (root) {
        root.classList.remove('is-open');
        const panel = root.querySelector('[data-assistant-fab-panel]');
        const fab = root.querySelector('[data-assistant-fab]');
        if (panel) {
            panel.hidden = true;
        }
        if (fab) {
            fab.setAttribute('aria-expanded', 'false');
        }
    });
}

function initAssistantFabPreferenceToggles() {
    document.querySelectorAll('[data-assistant-fab-switch]').forEach(function (input) {
        if (input.dataset.assistantFabSwitchBound === '1') {
            return;
        }
        input.dataset.assistantFabSwitchBound = '1';

        const label = input.closest('.profile-assistant-fab-switch');
        if (label) {
            label.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        input.addEventListener('click', function (e) {
            e.stopPropagation();
        });

        input.addEventListener('change', function (e) {
            e.stopPropagation();
            setAssistantFabEnabled(input.checked);
            if (!input.checked) {
                closeAssistantFabPanels();
            }
        });
    });

    syncAssistantFabToggleButtons(isAssistantFabEnabled());

    window.addEventListener('salang-assistant-fab-changed', function (e) {
        syncAssistantFabToggleButtons(e.detail?.enabled !== false);
    });
}

function initMemberAssistant() {
    applyAssistantFabVisibility();

    document.querySelectorAll('[data-assistant-chat]').forEach(function (chat) {
        if (!chat.closest('[data-assistant-fab-panel]')) {
            initAssistantChat(chat);
        }
    });

    document.querySelectorAll('[data-assistant-fab-root]').forEach(initAssistantFab);
    initAssistantFabPreferenceToggles();
}

document.addEventListener('DOMContentLoaded', initMemberAssistant);

export {
    initMemberAssistant,
    initAssistantChat,
    isAssistantFabEnabled,
    setAssistantFabEnabled,
    applyAssistantFabVisibility,
};
