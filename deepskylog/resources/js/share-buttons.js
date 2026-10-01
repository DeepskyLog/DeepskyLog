// Delegated, so the buttons of cards that Livewire renders later work as well.
document.addEventListener('click', socialButtonClickHandler);

// The share sheet button (`a.native-share`) is hidden by CSS unless the browser has the share sheet.
if (typeof navigator.share === 'function') {
    document.documentElement.classList.add('can-native-share');
}

// The image of a share block (the `data-share-image` of its `[data-share]` container) is fetched
// as soon as the pointer or focus reaches the X or share sheet button: the clipboard and the share
// sheet only accept it within the click that asks for it.
['pointerover', 'focusin', 'touchstart'].forEach((type) => {
    document.addEventListener(type, (e) => {
        const el = e.target.closest ? e.target.closest('a.social-button') : null;
        if (!el) return;
        const image = shareImageUrl(el);
        if (!image) return;
        if (el.classList.contains('native-share')) {
            shareFile(image).catch(() => {});
        } else if (imageNoticeFor(el) && canCopyImage()) {
            pngBlob(image).catch(() => {});
        }
    }, { passive: true });
});

function socialButtonClickHandler(e) {
    const popupWidth = 780;
    const popupHeight = 550;

    const el = e.target.closest ? e.target.closest('a.social-button') : null;
    // A button with a handler of its own (the copy link button of an observing list) is left alone.
    if (!el || e.defaultPrevented) return;

    if (el.classList.contains('native-share')) {
        e.preventDefault();
        e.stopImmediatePropagation();
        nativeShare(el);
        return;
    }

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

    // Neither X nor a mail link takes an image: copy the sketch, so it can be pasted into the post
    // or the mail.
    const imageNotice = imageNoticeFor(el);
    if (imageNotice) {
        const image = shareImageUrl(el);
        if (image && canCopyImage()) {
            copyImage(image).then(() => showShareNotice(imageNotice)).catch(() => {});
        }
    }

    // A mail link opens the mail program and needs no window; WhatsApp opens in a tab of its own
    // (its links have target="_blank"), where its page hands the text to the app or WhatsApp Web.
    if (el.protocol === 'mailto:' || isWhatsAppShare(el.href)) return;

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

function isXShare(href) {
    try {
        const url = new URL(href);
        return /(^|\.)(twitter|x)\.com$/.test(url.hostname) && url.pathname.startsWith('/intent/');
    } catch (_) {
        return false;
    }
}

// The notice shown when the sketch has been copied for this button; null for buttons that do not copy it.
function imageNoticeFor(el) {
    if (isXShare(el.href)) {
        return document.querySelector('meta[name="share-image-notice"]')?.content
            || 'The sketch has been copied: paste it (Ctrl+V) into your post on X.';
    }
    if (el.protocol === 'mailto:') {
        return document.querySelector('meta[name="share-image-mail-notice"]')?.content
            || 'The sketch has been copied: paste it (Ctrl+V) into your mail.';
    }
    return null;
}

function isWhatsAppShare(href) {
    try {
        const url = new URL(href);
        return /(^|\.)(wa\.me|whatsapp\.com)$/.test(url.hostname);
    } catch (_) {
        return false;
    }
}

function shareContainer(el) {
    return el.closest('[data-share]');
}

function shareImageUrl(el) {
    const image = shareContainer(el)?.dataset.shareImage || '';
    return image.trim() !== '' ? new URL(image, document.baseURI).href : null;
}

function canCopyImage() {
    if (!window.isSecureContext || !navigator.clipboard?.write || typeof ClipboardItem === 'undefined') {
        return false;
    }
    return typeof ClipboardItem.supports !== 'function' || ClipboardItem.supports('image/png');
}

// The ClipboardItem gets the promise of the image, so the write starts within the click (Safari
// refuses it otherwise) while the image is still being fetched and converted.
function copyImage(url) {
    return navigator.clipboard.write([new ClipboardItem({ 'image/png': pngBlob(url) })]);
}

const pngBlobs = new Map();

// Browsers only put PNG images on the clipboard; X accepts them up to 5 MB, so large sketches are
// scaled down to PNG_MAX_SIDE pixels on their longest side.
const PNG_MAX_SIDE = 2048;

function pngBlob(url) {
    if (!pngBlobs.has(url)) {
        const promise = fetchImage(url).then(async (blob) => {
            const bitmap = await createImageBitmap(blob);
            const scale = Math.min(1, PNG_MAX_SIDE / Math.max(bitmap.width, bitmap.height));
            if (blob.type === 'image/png' && scale === 1) {
                bitmap.close?.();
                return blob;
            }
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(bitmap.width * scale);
            canvas.height = Math.round(bitmap.height * scale);
            canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
            bitmap.close?.();
            return new Promise((resolve, reject) => canvas.toBlob(
                (png) => png ? resolve(png) : reject(new Error('PNG conversion failed')), 'image/png'));
        });
        promise.catch(() => pngBlobs.delete(url));
        pngBlobs.set(url, promise);
    }
    return pngBlobs.get(url);
}

function fetchImage(url) {
    return fetch(url, { credentials: 'same-origin' }).then((response) => {
        if (!response.ok) throw new Error('Image not available: ' + response.status);
        return response.blob();
    });
}

const shareFiles = new Map();
const readyShareFiles = new Map();

function shareFile(url) {
    if (!shareFiles.has(url)) {
        const promise = fetchImage(url).then((blob) => {
            const name = decodeURIComponent(new URL(url).pathname.split('/').pop() || 'sketch')
                .replace(/\.[^.]*$/, '') || 'sketch';
            const extension = (blob.type.split('/')[1] || 'jpg').replace('jpeg', 'jpg');
            const file = new File([blob], name + '.' + extension, { type: blob.type || 'image/jpeg' });
            readyShareFiles.set(url, file);
            return file;
        });
        promise.catch(() => shareFiles.delete(url));
        shareFiles.set(url, promise);
    }
    return shareFiles.get(url);
}

// Opens the share sheet of the system with the text, the link and (where the browser can share
// files) the sketch, so that, for example, the X app opens with the image attached.
async function nativeShare(el) {
    const container = shareContainer(el);
    const title = container?.dataset.shareTitle || document.title;
    const text = container?.dataset.shareText || '';
    const link = container?.dataset.shareUrl || el.href;
    const image = shareImageUrl(el);

    let file = image ? readyShareFiles.get(image) : null;
    if (image && !file) {
        try {
            file = await shareFile(image);
        } catch (_) {
            file = null;
        }
    }

    // Apps that receive files mostly drop the separate url, so it goes into the text then (unless
    // the text holds it already, as the sketch of the week text does).
    const data = file && navigator.canShare?.({ files: [file] })
        ? { title, text: text.includes(link) ? text : [text, link].filter(Boolean).join('\n\n'), files: [file] }
        : { title, text, url: link };

    try {
        await navigator.share(data);
    } catch (error) {
        // Waiting for the image can take longer than the browser lets a click count (Safari): the
        // image is there now, so a second tap works.
        if (error?.name === 'NotAllowedError' && data.files) {
            showShareNotice(document.querySelector('meta[name="share-retry-notice"]')?.content
                || 'The sketch is ready: tap the share button again.');
        }
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
