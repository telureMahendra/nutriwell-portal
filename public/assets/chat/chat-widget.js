/*
 * Healthie4U floating chat widget.
 *
 * Features:
 *   - WhatsApp-web-style two-pane UI
 *   - Firestore real-time sync (same schema as Flutter app)
 *   - Firebase Auth sign-in with a backend-minted custom token (if configured)
 *   - Cross-tab unread badge (listener on every associated conversation)
 *   - File upload (images + PDFs), with mentor-only restricted categories
 *   - Client-side image compression before upload
 *   - Sound + browser notification on new incoming messages
 *   - Read receipts (sent / delivered / read double-ticks)
 *   - window.openChatWith(clientId) — global entry point for "Chat" buttons
 *
 * Firestore contract (must stay in sync with Flutter app):
 *   chat/{clientId}:
 *     chatHistory:           [ message, ... ]
 *     unreadMessageCount:    number
 *     token:                 FCM device token of the client
 *     readCursors:           { "admin": <idx>, "mentor:7": <idx>, "<clientId>": <idx> }  // portal-added
 *     lastMessageAt:         serverTimestamp (portal-added)
 *
 *   message: {
 *     chatMessage:   string,
 *     senderName:    string,
 *     userId:        "admin" | "mentor:<id>" | "<clientId>",
 *     timeStamp:     "dd/MM/yyyy hh:mm aa",
 *     messageType:   "1" | "2",
 *     fileUrl:       string,
 *     fileDesc:      string,
 *     category:      "general" | "diet_plan" | "workout_plan" | "prescription",
 *     restricted:    bool,
 *     fileMime:      string
 *   }
 */
(function () {
  'use strict';

  // -------- guard rails --------
  if (!window.H4U_CHAT_USER)                                   return warn('H4U_CHAT_USER missing');
  if (!window.firebase || !firebase.firestore)                 return warn('Firebase SDK not loaded');
  if (!window.H4U_FIREBASE_CONFIG ||
      String(window.H4U_FIREBASE_CONFIG.apiKey).indexOf('PASTE_') === 0) {
    return warn('Firebase config not set in firebase-config.js');
  }
  function warn(m) { console.warn('[h4u-chat] ' + m + ' — widget disabled'); }

  if (!firebase.apps.length) firebase.initializeApp(window.H4U_FIREBASE_CONFIG);
  var db = firebase.firestore();

  var U = window.H4U_CHAT_USER;
  var COLLECTION = window.H4U_CHAT_COLLECTION || 'chat';
  var SELF_ID = (U.role === 'admin') ? 'admin'
              : (U.role === 'mentor') ? 'mentor:' + U.id
              : String(U.id);

  // -------- state --------
  var state = {
    open: false,
    contacts: [],
    activeClient: null,
    unsubActive: null,
    globalSubs: {},                       // clientId → unsubscribe fn
    messages: [],
    staged: null,
    unreadByClient: {},                   // clientId → count
    windowFocused: !document.hidden,
    docs: {},                             // clientId → last snapshot data
  };

  // last-seen pointer per contact, persisted so reloads don't re-fire notifications
  var LS_KEY = 'h4u_chat_last_seen_' + SELF_ID;
  var lastSeen = (function () {
    try { return JSON.parse(localStorage.getItem(LS_KEY) || '{}') || {}; }
    catch (_) { return {}; }
  })();
  function saveLastSeen() {
    try { localStorage.setItem(LS_KEY, JSON.stringify(lastSeen)); } catch (_) {}
  }

  // ======================================================================
  // BOOT
  // ======================================================================
  function boot() {
    mount();
    window.addEventListener('focus', function () { state.windowFocused = true; });
    window.addEventListener('blur',  function () { state.windowFocused = false; });
    document.addEventListener('visibilitychange', function () {
      state.windowFocused = !document.hidden;
    });

    // Auth (optional — needs backend service account). Token bridge is
    // non-fatal; widget works in open-rules mode if the endpoint is 503.
    signInWithCustomTokenIfPossible()
      .then(preloadContactsForUnread)
      .catch(function (e) { console.warn('[h4u-chat] auth skipped:', e.message || e); });
  }

  function signInWithCustomTokenIfPossible() {
    if (!firebase.auth || !U.endpoints.firebaseToken) return Promise.resolve();
    return fetch(U.endpoints.firebaseToken, { credentials: 'same-origin' })
      .then(function (r) {
        if (r.status === 503) return null;  // service account not configured
        return r.json();
      })
      .then(function (j) {
        if (!j || !j.status || !j.token) return null;
        return firebase.auth().signInWithCustomToken(j.token);
      });
  }

  /** Subscribe to every associated conversation so the FAB shows a global
   *  unread badge even when the window is closed. */
  function preloadContactsForUnread() {
    return fetch(U.endpoints.contacts, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (!j || !j.status) return;
        state.contacts = j.data || [];
        // cap the live listeners to keep Firestore read costs sane
        var limit = Math.min(state.contacts.length, 150);
        for (var i = 0; i < limit; i++) {
          attachGlobalListener(String(state.contacts[i].client_id));
        }
      }).catch(function () { /* ignore, widget will retry on open */ });
  }

  function attachGlobalListener(clientId) {
    if (state.globalSubs[clientId]) return;
    state.globalSubs[clientId] = db.collection(COLLECTION).doc(clientId)
      .onSnapshot(function (snap) {
        var data = snap.exists ? snap.data() : {};
        state.docs[clientId] = data;
        var history = Array.isArray(data.chatHistory) ? data.chatHistory : [];
        var seenCount = lastSeen[clientId] || 0;
        var unread = 0, firstUnread = null;
        for (var i = seenCount; i < history.length; i++) {
          var m = history[i] || {};
          if (String(m.userId || '') !== SELF_ID) {
            unread++;
            if (!firstUnread) firstUnread = m;
          }
        }
        var prev = state.unreadByClient[clientId] || 0;
        state.unreadByClient[clientId] = unread;

        // notify + sound when a NEW incoming arrives and we're not actively
        // viewing this conversation
        var activeId = state.activeClient && String(state.activeClient.client_id);
        if (unread > prev && firstUnread && activeId !== clientId) {
          fireIncomingEffects(clientId, firstUnread);
        }

        // if this conversation IS the active one, auto-mark read up to tail
        if (activeId === clientId) {
          lastSeen[clientId] = history.length;
          saveLastSeen();
          state.unreadByClient[clientId] = 0;
          maybeAdvanceReadCursor(clientId, history.length - 1);
        }

        updateUnreadBadge();
        renderContacts();
      }, function (err) {
        console.warn('[h4u-chat] global listener err', clientId, err.message);
      });
  }

  // ======================================================================
  // INCOMING EFFECTS — sound + browser notification
  // ======================================================================
  var audioCtx = null;
  function beep() {
    try {
      audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
      var o = audioCtx.createOscillator(), g = audioCtx.createGain();
      o.type = 'sine'; o.frequency.value = 880;
      g.gain.setValueAtTime(0.001, audioCtx.currentTime);
      g.gain.exponentialRampToValueAtTime(0.18, audioCtx.currentTime + 0.02);
      g.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.35);
      o.connect(g); g.connect(audioCtx.destination);
      o.start(); o.stop(audioCtx.currentTime + 0.38);
    } catch (_) {}
  }

  function fireIncomingEffects(clientId, msg) {
    beep();
    if (!state.windowFocused && 'Notification' in window && Notification.permission === 'granted') {
      var contact = (state.contacts.filter(function (c) {
        return String(c.client_id) === clientId;
      })[0]) || { client_name: 'Client' };
      var body = msg.chatMessage || (msg.messageType === '2' ? '📎 Attachment' : 'New message');
      try {
        var n = new Notification(msg.senderName || contact.client_name, {
          body: body, tag: 'h4u-chat-' + clientId,
          icon: window.location.origin + '/public/assets/admin/pages/img/favicon.png',
        });
        n.onclick = function () { window.focus(); openWith(clientId); n.close(); };
      } catch (_) {}
    }
  }

  function requestNotificationPermission() {
    if ('Notification' in window && Notification.permission === 'default') {
      Notification.requestPermission();
    }
  }

  // ======================================================================
  // DOM
  // ======================================================================
  function mount() {
    var fab = document.createElement('button');
    fab.className = 'h4u-chat-fab';
    fab.title = 'Chat';
    fab.innerHTML = '<i class="fa fa-comments"></i><span class="h4u-badge">0</span>';
    document.body.appendChild(fab);

    var win = document.createElement('div');
    win.className = 'h4u-chat-window';
    win.innerHTML =
      '<div class="h4u-chat-side">' +
        '<div class="h4u-chat-side-head">' +
          '<h4>' + escapeHtml(U.role === 'admin' ? 'Chats' : 'My Clients') + '</h4>' +
          '<button class="h4u-close" title="Close">&times;</button>' +
        '</div>' +
        '<div class="h4u-chat-search"><input type="text" placeholder="Search or start new chat" /></div>' +
        '<div class="h4u-chat-contacts"><div class="h4u-contacts-empty">Loading…</div></div>' +
      '</div>' +
      '<div class="h4u-chat-main">' +
        '<div class="h4u-chat-main-head">' +
          '<button class="h4u-back"><i class="fa fa-arrow-left"></i></button>' +
          '<div class="h4u-avatar" style="width:36px;height:36px;font-size:12px;">&nbsp;</div>' +
          '<div style="flex:1;min-width:0;">' +
            '<div class="h4u-title">Select a conversation</div>' +
            '<div class="h4u-sub"></div>' +
          '</div>' +
        '</div>' +
        '<div class="h4u-thread"><div class="h4u-empty-state">Pick a contact from the left to start chatting.</div></div>' +
        '<div class="h4u-staged">' +
          '<div class="h4u-thumb"><i class="fa fa-file-pdf"></i></div>' +
          '<div style="flex:1;min-width:0;">' +
            '<div class="h4u-staged-name" style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>' +
            '<div class="h4u-cat-wrap" style="margin-top:4px;"></div>' +
          '</div>' +
          '<button class="h4u-discard" title="Discard">&times;</button>' +
        '</div>' +
        '<form class="h4u-composer" autocomplete="off">' +
          '<button type="button" class="h4u-iconbtn h4u-attach" title="Attach image or PDF"><i class="fa fa-paperclip"></i></button>' +
          '<input type="file" class="h4u-file" accept="image/*,application/pdf" aria-hidden="true" tabindex="-1" style="display:none !important;position:absolute;width:0;height:0;opacity:0;" />' +
          '<input type="text" class="h4u-input" placeholder="Type a message" disabled />' +
          '<button type="submit" class="h4u-send" disabled><i class="fa fa-paper-plane"></i></button>' +
        '</form>' +
      '</div>';
    document.body.appendChild(win);

    var lb = document.createElement('div');
    lb.className = 'h4u-lightbox';
    lb.innerHTML = '<button class="h4u-lightbox-close">&times;</button><img alt="" />';
    document.body.appendChild(lb);

    var toast = document.createElement('div');
    toast.className = 'h4u-toast';
    document.body.appendChild(toast);

    bindUI({ fab: fab, win: win, lb: lb, toast: toast });
  }

  // ======================================================================
  // BINDING
  // ======================================================================
  var el = {};
  function bindUI(refs) {
    el.fab       = refs.fab;
    el.win       = refs.win;
    el.close     = el.win.querySelector('.h4u-close');
    el.search    = el.win.querySelector('.h4u-chat-search input');
    el.contacts  = el.win.querySelector('.h4u-chat-contacts');
    el.mainAvatar= el.win.querySelector('.h4u-chat-main-head .h4u-avatar');
    el.title     = el.win.querySelector('.h4u-chat-main-head .h4u-title');
    el.sub       = el.win.querySelector('.h4u-chat-main-head .h4u-sub');
    el.thread    = el.win.querySelector('.h4u-thread');
    el.composer  = el.win.querySelector('.h4u-composer');
    el.input     = el.win.querySelector('.h4u-input');
    el.send      = el.win.querySelector('.h4u-send');
    el.attach    = el.win.querySelector('.h4u-attach');
    el.file      = el.win.querySelector('.h4u-file');
    el.staged    = el.win.querySelector('.h4u-staged');
    el.stagedName= el.win.querySelector('.h4u-staged-name');
    el.stagedThumb = el.win.querySelector('.h4u-staged .h4u-thumb');
    el.stagedCat = el.win.querySelector('.h4u-cat-wrap');
    el.discard   = el.win.querySelector('.h4u-discard');
    el.back      = el.win.querySelector('.h4u-back');
    el.lb        = refs.lb;
    el.lbImg     = refs.lb.querySelector('img');
    el.lbClose   = refs.lb.querySelector('.h4u-lightbox-close');
    el.toast     = refs.toast;

    el.fab.addEventListener('click', function () {
      requestNotificationPermission();
      toggleOpen();
    });
    el.close.addEventListener('click', close);
    el.search.addEventListener('input', renderContacts);
    el.attach.addEventListener('click', function () { el.file.click(); });
    el.file.addEventListener('change', onFilePick);
    el.discard.addEventListener('click', clearStaged);
    el.back.addEventListener('click', function () { el.win.classList.remove('show-thread'); });
    el.composer.addEventListener('submit', onSend);
    el.input.addEventListener('input', syncSendEnabled);
    el.lbClose.addEventListener('click', function () { el.lb.classList.remove('open'); });
    el.lb.addEventListener('click', function (e) { if (e.target === el.lb) el.lb.classList.remove('open'); });
  }

  function toggleOpen() { state.open ? close() : open(); }
  function open() {
    state.open = true;
    el.win.classList.add('open');
    if (state.contacts.length === 0) loadContacts();
    else renderContacts();
  }
  function close() {
    state.open = false;
    el.win.classList.remove('open');
  }

  // ======================================================================
  // CONTACTS
  // ======================================================================
  function loadContacts() {
    fetch(U.endpoints.contacts, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        state.contacts = (j && j.status && Array.isArray(j.data)) ? j.data : [];
        // ensure every contact has an unread listener
        for (var i = 0; i < Math.min(state.contacts.length, 150); i++) {
          attachGlobalListener(String(state.contacts[i].client_id));
        }
        renderContacts();
      })
      .catch(function () {
        el.contacts.innerHTML = '<div class="h4u-contacts-empty">Failed to load contacts.</div>';
      });
  }

  function renderContacts() {
    var q = (el.search && el.search.value || '').toLowerCase().trim();
    var list = state.contacts.filter(function (c) {
      if (!q) return true;
      return (c.client_name || '').toLowerCase().indexOf(q) >= 0
          || (c.client_email || '').toLowerCase().indexOf(q) >= 0
          || (c.client_mobile_number || '').indexOf(q) >= 0;
    });
    // sort: unread first, then alpha
    list.sort(function (a, b) {
      var ua = state.unreadByClient[a.client_id] || 0;
      var ub = state.unreadByClient[b.client_id] || 0;
      if (ua !== ub) return ub - ua;
      return (a.client_name || '').localeCompare(b.client_name || '');
    });

    if (list.length === 0) {
      el.contacts.innerHTML = '<div class="h4u-contacts-empty">' +
        (state.contacts.length === 0 ? 'No clients available.' : 'No matches.') +
        '</div>';
      return;
    }
    el.contacts.innerHTML = list.map(function (c) {
      var unread = state.unreadByClient[c.client_id] || 0;
      var isActive = state.activeClient && String(state.activeClient.client_id) === String(c.client_id);
      return '<div class="h4u-contact' + (isActive ? ' active' : '') + '" data-id="' + c.client_id + '">' +
        '<div class="h4u-avatar">' + initials(c.client_name) + '</div>' +
        '<div class="h4u-contact-meta">' +
          '<div class="h4u-contact-name">' + escapeHtml(c.client_name || 'Client #' + c.client_id) + '</div>' +
          '<div class="h4u-contact-sub">' + escapeHtml(c.client_email || c.client_mobile_number || '') + '</div>' +
        '</div>' +
        (unread > 0 ? '<span class="h4u-unread">' + unread + '</span>' : '') +
      '</div>';
    }).join('');
    Array.prototype.forEach.call(el.contacts.querySelectorAll('.h4u-contact'), function (row) {
      row.addEventListener('click', function () {
        var id = row.getAttribute('data-id');
        var c = state.contacts.filter(function (x) { return String(x.client_id) === String(id); })[0];
        if (c) openConversation(c);
      });
    });
  }

  // ======================================================================
  // THREAD
  // ======================================================================
  function openConversation(client) {
    state.activeClient = client;
    state.messages = [];
    clearStaged();
    el.win.classList.add('show-thread');
    el.mainAvatar.textContent = initials(client.client_name);
    el.title.textContent = client.client_name || ('Client #' + client.client_id);
    el.sub.textContent = client.client_email || client.client_mobile_number || '';
    el.input.disabled = false;
    el.input.value = '';
    el.input.focus();
    el.thread.innerHTML = '<div class="h4u-empty-state">Loading messages…</div>';
    syncSendEnabled();

    // reset unread locally
    state.unreadByClient[client.client_id] = 0;
    renderContacts();
    updateUnreadBadge();

    // main thread listener (detailed render)
    if (state.unsubActive) { state.unsubActive(); state.unsubActive = null; }
    state.unsubActive = db.collection(COLLECTION).doc(String(client.client_id))
      .onSnapshot(function (snap) {
        var data = snap.exists ? snap.data() : {};
        state.docs[String(client.client_id)] = data;
        state.messages = Array.isArray(data.chatHistory) ? data.chatHistory : [];
        renderThread();
        // mark everything read
        lastSeen[client.client_id] = state.messages.length;
        saveLastSeen();
        maybeAdvanceReadCursor(String(client.client_id), state.messages.length - 1);
      }, function (err) {
        el.thread.innerHTML = '<div class="h4u-empty-state">Could not load messages.<br><small>' +
          escapeHtml(err.message || '') + '</small></div>';
      });

    // also make sure the global listener exists
    attachGlobalListener(String(client.client_id));
  }

  function renderThread() {
    if (!state.messages.length) {
      el.thread.innerHTML = '<div class="h4u-empty-state">No messages yet. Say hello 👋</div>';
      return;
    }
    var activeId = String(state.activeClient.client_id);
    var doc = state.docs[activeId] || {};
    var cursors = doc.readCursors || {};
    var html = '';
    var lastDateKey = '';
    state.messages.forEach(function (m, idx) {
      var datePart = String(m.timeStamp || '').trim().split(' ')[0]; // "dd/MM/yyyy"
      if (datePart && datePart !== lastDateKey) {
        lastDateKey = datePart;
        html += '<div class="h4u-date-sep"><span>' + escapeHtml(labelForDate(datePart)) + '</span></div>';
      }
      html += bubble(m, idx, cursors, activeId);
    });
    el.thread.innerHTML = html;
    Array.prototype.forEach.call(el.thread.querySelectorAll('img.h4u-img'), function (img) {
      img.addEventListener('click', function () {
        el.lbImg.src = img.getAttribute('data-full') || img.src;
        el.lb.classList.add('open');
      });
    });
    el.thread.scrollTop = el.thread.scrollHeight;
  }

  /**
   * Resolve the human-readable sender name from a Firestore message object.
   * The Flutter client sometimes stores its numeric user ID in senderName
   * (e.g. "207") instead of the real name. We detect that and substitute.
   */
  function resolveSenderName(m) {
    var raw = String(m.senderName || '').trim();
    var uid = String(m.userId   || '').trim();

    // Numeric string → client stored their ID instead of name
    if (/^\d+$/.test(raw)) {
      if (state.activeClient && String(state.activeClient.client_id) === raw) {
        return state.activeClient.client_name || ('Client #' + raw);
      }
      // Check contacts list
      var matched = state.contacts.filter(function(c) { return String(c.client_id) === raw; })[0];
      if (matched) return matched.client_name || ('Client #' + raw);
      return 'Client #' + raw;
    }

    // Empty senderName → derive from userId
    if (!raw) {
      if (uid === 'admin')              return 'Admin';
      if (uid.indexOf('mentor:') === 0) return 'Mentor';
      if (/^\d+$/.test(uid)) {
        if (state.activeClient && String(state.activeClient.client_id) === uid)
          return state.activeClient.client_name || ('Client #' + uid);
        var mc = state.contacts.filter(function(c){ return String(c.client_id) === uid; })[0];
        return mc ? (mc.client_name || ('Client #' + uid)) : ('Client #' + uid);
      }
      return uid || 'Unknown';
    }

    return raw;
  }

  /** Colour the sender label consistently based on who sent it. */
  function senderColor(m) {
    var uid = String(m.userId || '');
    if (uid === 'admin')              return '#c0392b';
    if (uid.indexOf('mentor:') === 0) return '#1fa855';
    return '#06a1ac'; // client
  }

  function bubble(m, idx, cursors, activeId) {
    var isOut = String(m.userId || '') === SELF_ID;
    var ts = m.timeStamp || '';
    var category = m.category || 'general';
    var restricted = !!m.restricted;
    var sender = escapeHtml(resolveSenderName(m));

    var body = '';
    if (m.messageType === '2' || m.messageType === 2) {
      var url = m.fileUrl || '';
      var mime = (m.fileMime || '').toLowerCase();
      var isImg = mime.indexOf('image/') === 0 || /\.(png|jpe?g|gif|webp)$/i.test(url);
      if (isImg) {
        body += '<img class="h4u-img" src="' + escapeAttr(url) + '" data-full="' + escapeAttr(url) + '" alt="attachment" />';
      } else {
        body += '<a class="h4u-fileblock" href="' + escapeAttr(url) + '" target="_blank" rel="noopener">' +
          '<i class="fa fa-file-pdf"></i>' +
          '<div><div class="h4u-fname">' + escapeHtml(m.fileDesc || prettyCategory(category) || 'Document') + '</div>' +
          '<div class="h4u-fsub">Open PDF</div></div>' +
        '</a>';
      }
      if (restricted && category !== 'general') {
        body += '<span class="h4u-cat-pill">' + escapeHtml(prettyCategory(category)) + '</span>';
      }
      if (m.chatMessage) body += '<div>' + linkify(escapeHtml(m.chatMessage)) + '</div>';
    } else {
      body += linkify(escapeHtml(m.chatMessage || ''));
    }

    // read receipt — only on outbound bubbles
    var receipt = '';
    if (isOut) {
      receipt = ' <span class="h4u-tick">' + tickFor(idx, cursors, activeId) + '</span>';
    }

    var senderHtml = !isOut
      ? '<div class="h4u-sender" style="color:' + senderColor(m) + ';">' + sender + '</div>'
      : '';

    return '<div class="h4u-bubble ' + (isOut ? 'out' : 'in') + '">' +
      senderHtml +
      '<div class="h4u-msg-body">' + body + '</div>' +
      '<div class="h4u-time">' + escapeHtml(ts) + receipt + '</div>' +
    '</div>';
  }

  /** Returns the tick mark for an outbound message at index `idx`.
   *    ✓     = sent (in Firestore — default since listener has it)
   *    ✓✓    = delivered (recipient has doc)
   *    ✓✓ (blue) = read (recipient's readCursor >= idx)
   */
  function tickFor(idx, cursors, activeId) {
    // recipients of this message = everyone who isn't SELF
    // primary recipient is the other party of the conversation
    var recipientIds = [];
    if (U.role === 'admin' || U.role === 'mentor') {
      recipientIds.push(activeId);  // the client
    } else {
      // client-side: mentor or admin viewing — but widget never runs as client
    }
    var readByAny = false;
    for (var i = 0; i < recipientIds.length; i++) {
      var c = cursors[recipientIds[i]];
      if (typeof c === 'number' && c >= idx) { readByAny = true; break; }
    }
    if (readByAny) return '<span style="color:#53bdeb;">&#10004;&#10004;</span>';
    return '<span style="color:#667781;">&#10004;&#10004;</span>';
  }

  /** Push the current user's last-read index up so the SENDER sees blue ticks. */
  function maybeAdvanceReadCursor(clientId, idx) {
    if (idx < 0) return;
    var doc = state.docs[clientId] || {};
    var cursors = doc.readCursors || {};
    if (typeof cursors[SELF_ID] === 'number' && cursors[SELF_ID] >= idx) return;
    var update = {};
    update['readCursors.' + SELF_ID] = idx;
    db.collection(COLLECTION).doc(clientId).set({
      readCursors: (function () { var o = {}; o[SELF_ID] = idx; return o; })(),
    }, { merge: true }).catch(function (e) {
      console.warn('[h4u-chat] read cursor update failed:', e.message);
    });
  }

  // ======================================================================
  // SEND
  // ======================================================================
  function syncSendEnabled() {
    var hasText = (el.input.value || '').trim().length > 0;
    el.send.disabled = !state.activeClient || (!hasText && !state.staged);
  }

  function onFilePick(e) {
    var f = e.target.files && e.target.files[0];
    e.target.value = '';
    if (!f) return;
    if (!/^image\/|^application\/pdf$/.test(f.type)) return toast('Only images and PDF files are allowed.');
    if (f.size > 10 * 1024 * 1024) return toast('File exceeds 10 MB limit.');

    var isImage = f.type.indexOf('image/') === 0;
    var prep = isImage ? compressImage(f) : Promise.resolve(f);
    prep.then(function (finalFile) {
      state.staged = { file: finalFile, isImage: isImage, category: 'general' };
      if (isImage) {
        var reader = new FileReader();
        reader.onload = function () {
          el.stagedThumb.innerHTML = '<img src="' + reader.result +
            '" style="width:100%;height:100%;object-fit:cover;border-radius:4px;" />';
        };
        reader.readAsDataURL(finalFile);
      } else {
        el.stagedThumb.innerHTML = '<i class="fa fa-file-pdf"></i>';
      }
      el.stagedName.textContent = finalFile.name || f.name;

      el.stagedCat.innerHTML = '';
      if (U.role === 'mentor' && !isImage) {
        el.stagedCat.innerHTML =
          '<label style="font-size:12px;color:#667781;">Category: ' +
          '<select class="h4u-category">' +
            '<option value="general">General</option>' +
            '<option value="diet_plan">Diet Plan</option>' +
            '<option value="workout_plan">Workout Plan</option>' +
            '<option value="prescription">Prescription</option>' +
          '</select></label>' +
          '<div style="font-size:11px;color:#8a6d00;margin-top:4px;">' +
            'Restricted categories are chat-only and cannot be accessed from other sections.' +
          '</div>';
        el.stagedCat.querySelector('.h4u-category').addEventListener('change', function (ev) {
          state.staged.category = ev.target.value;
        });
      }
      el.staged.classList.add('show');
      syncSendEnabled();
    });
  }

  /** Canvas-based image compression. 1600px max dimension, JPEG q=0.8. */
  function compressImage(file) {
    return new Promise(function (resolve) {
      var img = new Image();
      var url = URL.createObjectURL(file);
      img.onload = function () {
        URL.revokeObjectURL(url);
        var MAX = 1600;
        var w = img.width, h = img.height;
        if (w <= MAX && h <= MAX && file.size < 600 * 1024) return resolve(file);
        var scale = Math.min(MAX / w, MAX / h, 1);
        var tw = Math.round(w * scale), th = Math.round(h * scale);
        var canvas = document.createElement('canvas');
        canvas.width = tw; canvas.height = th;
        canvas.getContext('2d').drawImage(img, 0, 0, tw, th);
        canvas.toBlob(function (blob) {
          if (!blob) return resolve(file);
          var name = (file.name || 'photo').replace(/\.[^.]+$/, '') + '.jpg';
          resolve(new File([blob], name, { type: 'image/jpeg' }));
        }, 'image/jpeg', 0.82);
      };
      img.onerror = function () { URL.revokeObjectURL(url); resolve(file); };
      img.src = url;
    });
  }

  function clearStaged() {
    state.staged = null;
    el.staged.classList.remove('show');
    el.stagedName.textContent = '';
    el.stagedCat.innerHTML = '';
    syncSendEnabled();
  }

  function onSend(e) {
    e.preventDefault();
    if (!state.activeClient) return;
    var text = (el.input.value || '').trim();
    if (!text && !state.staged) return;

    el.send.disabled = true;
    var p = state.staged ? uploadStaged() : Promise.resolve(null);
    p.then(function (fileInfo) {
      var msg = buildMessage(text, fileInfo);
      return appendToFirestore(state.activeClient.client_id, msg).then(function () {
        return logToPortal(state.activeClient.client_id, msg, fileInfo);
      });
    }).then(function () {
      el.input.value = '';
      clearStaged();
    }).catch(function (err) {
      console.error(err);
      toast('Failed to send: ' + (err.message || err));
    }).finally(function () {
      syncSendEnabled();
      el.input.focus();
    });
  }

  function uploadStaged() {
    var fd = new FormData();
    fd.append('file', state.staged.file);
    fd.append('category', state.staged.category || 'general');
    fd.append('client_id', state.activeClient.client_id);
    return fetch(U.endpoints.upload, { method: 'POST', credentials: 'same-origin', body: fd })
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
      .then(function (res) {
        if (!res.ok || !res.body || !res.body.status) throw new Error((res.body && res.body.msg) || 'Upload failed');
        return res.body;
      });
  }

  function buildMessage(text, fileInfo) {
    var now = new Date();
    var msg = {
      chatMessage: text || '',
      senderName:  U.name || U.role,
      userId:      SELF_ID,
      timeStamp:   formatTimestamp(now),
      messageType: fileInfo ? '2' : '1',
    };
    if (fileInfo) {
      msg.fileUrl    = fileInfo.file_url;
      msg.fileMime   = fileInfo.mime_type || '';
      msg.fileDesc   = text || prettyCategory(fileInfo.category) || state.staged.file.name;
      msg.category   = fileInfo.category || 'general';
      msg.restricted = !!fileInfo.restricted;
    } else {
      msg.fileUrl = '';
      msg.fileDesc = '';
    }
    return msg;
  }

  function appendToFirestore(clientId, msg) {
    return db.collection(COLLECTION).doc(String(clientId)).set({
      chatHistory: firebase.firestore.FieldValue.arrayUnion(msg),
      unreadMessageCount: firebase.firestore.FieldValue.increment(1),
      lastMessageAt: firebase.firestore.FieldValue.serverTimestamp(),
    }, { merge: true });
  }

  function logToPortal(clientId, msg, fileInfo) {
    var fd = new FormData();
    fd.append('client_id', clientId);
    fd.append('message', msg.chatMessage);
    fd.append('message_type', fileInfo ? (fileInfo.message_type || 'file') : 'text');
    if (fileInfo) {
      fd.append('file_url',   msg.fileUrl);
      fd.append('file_desc',  msg.fileDesc);
      fd.append('category',   msg.category);
      fd.append('restricted', msg.restricted ? 1 : 0);
    }
    return fetch(U.endpoints.log, { method: 'POST', credentials: 'same-origin', body: fd })
      .catch(function () { /* audit log failure is non-fatal */ });
  }

  // ======================================================================
  // GLOBAL ENTRY POINT for "Chat" buttons on client detail pages
  // ======================================================================
  function openWith(clientId) {
    open();
    var cid = String(clientId);
    var existing = state.contacts.filter(function (c) { return String(c.client_id) === cid; })[0];
    if (existing) return openConversation(existing);
    // not in list yet — load contacts, then try again
    return fetch(U.endpoints.contacts, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        state.contacts = (j && j.status && Array.isArray(j.data)) ? j.data : [];
        for (var i = 0; i < Math.min(state.contacts.length, 150); i++) {
          attachGlobalListener(String(state.contacts[i].client_id));
        }
        renderContacts();
        var c = state.contacts.filter(function (x) { return String(x.client_id) === cid; })[0];
        if (c) openConversation(c);
        else toast('You are not authorized to chat with this client.');
      });
  }
  window.openChatWith = openWith;

  // ======================================================================
  // HELPERS
  // ======================================================================
  /** "dd/MM/yyyy hh:mm AA" → "hh:mm AA" for display inside bubbles. */
  function extractTime(ts) {
    if (!ts) return '';
    var parts = String(ts).trim().split(' ');
    // parts: ["06/05/2026", "01:43", "AM"]
    if (parts.length >= 3) return parts[1] + ' ' + parts[2];
    if (parts.length >= 2) return parts[1];
    return ts;
  }

  /** "dd/MM/yyyy" → "Today" / "Yesterday" / "6 May 2026". */
  function labelForDate(datePart) {
    if (!datePart) return '';
    var dp = datePart.split('/');
    if (dp.length !== 3) return datePart;
    var d = parseInt(dp[0], 10), mo = parseInt(dp[1], 10), y = parseInt(dp[2], 10);
    var today = new Date();
    var yest  = new Date(today); yest.setDate(today.getDate() - 1);
    if (d === today.getDate() && mo === today.getMonth() + 1 && y === today.getFullYear()) return 'Today';
    if (d === yest.getDate()  && mo === yest.getMonth()  + 1 && y === yest.getFullYear())  return 'Yesterday';
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return d + ' ' + (months[mo - 1] || '') + ' ' + y;
  }

  function formatTimestamp(d) {
    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    var h = d.getHours(), ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12; if (h === 0) h = 12;
    return pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear() +
      ' ' + pad(h) + ':' + pad(d.getMinutes()) + ' ' + ampm;
  }
  function initials(name) {
    if (!name) return '?';
    var parts = String(name).trim().split(/\s+/);
    return (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase();
  }
  function prettyCategory(c) {
    return { diet_plan: 'Diet Plan', workout_plan: 'Workout Plan', prescription: 'Prescription', general: '' }[c] || '';
  }
  function escapeHtml(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  function escapeAttr(s) { return escapeHtml(s); }
  function linkify(s) {
    return s.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');
  }
  function updateUnreadBadge() {
    var total = 0;
    Object.keys(state.unreadByClient).forEach(function (k) { total += state.unreadByClient[k] || 0; });
    var badge = el.fab.querySelector('.h4u-badge');
    badge.textContent = total > 99 ? '99+' : String(total);
    el.fab.classList.toggle('has-unread', total > 0);
  }
  var toastTimer = null;
  function toast(msg) {
    el.toast.textContent = msg;
    el.toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { el.toast.classList.remove('show'); }, 2800);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
