// Delegated, so the buttons of cards that Livewire renders later work as well.
document.addEventListener('click', socialButtonClickHandler);

function socialButtonClickHandler(e) {
    const popupWidth = 780;
    const popupHeight = 550;

    const el = e.target.closest ? e.target.closest('a.social-button') : null;
    if (!el) return;

    if (el.id === 'clip') {
        e.preventDefault();
        e.stopImmediatePropagation();
        copyToClipboard(el.href);
        return;
    }

    // Facebook only takes the link and fills the post with the page preview; it ignores (and its
    // policy forbids) text passed along. Copy the text so the observer can paste it into the post.
    if (isFacebookShare(el.href)) {
        const text = el.dataset.shareText || new URL(el.href).searchParams.get('quote') || '';
        if (text.trim() !== '') {
            copyToClipboard(text);
            showShareNotice(document.querySelector('meta[name="share-notice"]')?.content
                || 'The text has been copied: paste it into your Facebook post.');
        }
    }

    const windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    const windowHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;

    const vPosition = Math.floor((windowWidth - popupWidth) / 2),
        hPosition = Math.floor((windowHeight - popupHeight) / 2);

    const popup = window.open(el.href, 'social',
        'width=' + popupWidth + ',height=' + popupHeight +
        ',left=' + vPosition + ',top=' + hPosition +
        ',location=0,menubar=0,toolbar=0,status=0,scrollbars=1,resizable=1');

    if (popup) {
        e.preventDefault();
        e.stopImmediatePropagation();
        popup.focus();
    }
}

function isFacebookShare(href) {
    try {
        const url = new URL(href);
        return /(^|\.)facebook\.com$/.test(url.hostname) && url.pathname.startsWith('/sharer');
    } catch (_) {
        return false;
    }
}

function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).catch(() => copyWithTextarea(text));
        return;
    }
    copyWithTextarea(text);
}

function copyWithTextarea(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.select();
    try {
        document.execCommand('copy');  // Security exception may be thrown by some browsers.
    } catch (_) {
    }
    textArea.remove();
}

function showShareNotice(message) {
    let notice = document.getElementById('share-notice');
    if (!notice) {
        notice = document.createElement('div');
        notice.id = 'share-notice';
        notice.setAttribute('role', 'status');
        notice.className = 'fixed bottom-6 left-1/2 z-50 -translate-x-1/2 rounded-lg bg-gray-800 px-4 py-2 text-sm text-white shadow-lg ring-1 ring-gray-600';
        document.body.appendChild(notice);
    }
    notice.textContent = message;
    notice.hidden = false;
    clearTimeout(notice._hideTimer);
    notice._hideTimer = setTimeout(() => { notice.hidden = true; }, 6000);
}
