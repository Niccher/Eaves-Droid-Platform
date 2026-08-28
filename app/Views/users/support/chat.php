<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-comments mr-2 text-primary"></i>Support Chat</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Support Chat</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid" style="max-width: 800px;">
            <!-- DIRECT CHAT PRIMARY -->
            <div class="card card-primary card-outline direct-chat direct-chat-primary shadow-sm" id="chatCard">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-headset mr-1"></i> Help & Support
                        <small class="ml-2 font-weight-normal text-muted" id="supportStatus">
                            <?php if ($admins_status['is_online']): ?>
                                <span class="badge badge-success"><i class="fas fa-circle mr-1 text-white font-xs"></i> Online</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">
                                    Offline 
                                    <?php if ($admins_status['last_active']): ?>
                                        (Active <?= date('M d, H:i:s A', strtotime($admins_status['last_active'])) ?>)
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </small>
                    </h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <!-- Conversations are loaded here -->
                    <div class="direct-chat-messages" id="chatMessages" style="height: 480px; overflow-y: auto; padding: 15px;">
                        <?php if (empty($thread)): ?>
                            <div class="text-center py-5" id="chatEmptyState">
                                <i class="far fa-comments fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">Start a conversation</h5>
                                <p class="text-muted small">Send a message to our support administrators. We are here to help!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($thread as $msg): ?>
                                <?php
                                $isMe = (int)$msg['sender_id'] === (int)$user_info['id'];
                                $avatar = $msg['profile_image'] ? base_url('uploads/profiles/' . $msg['profile_image']) : null;
                                $timeStr = date('M d, H:i:s A', strtotime($msg['created_at']));
                                ?>
                                <!-- Message -->
                                <div class="direct-chat-msg <?= $isMe ? 'right' : '' ?>" data-uuid="<?= esc($msg['uuid']) ?>">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="direct-chat-name <?= $isMe ? 'float-right' : 'float-left' ?>">
                                            <?= esc(ucwords($msg['username'])) ?>
                                        </span>
                                    </div>
                                    <!-- /.direct-chat-infos -->
                                    <?php if ($avatar): ?>
                                        <img class="direct-chat-img border" src="<?= $avatar ?>" alt="Avatar">
                                    <?php else: ?>
                                        <?php
                                        $colors = ['#f56954', '#f39c12', '#0073b7', '#00c0ef', '#00a65a', '#3c8dbc', '#39cccc', '#605ca8', '#ff851b'];
                                        $colorIndex = abs(crc32($msg['username'])) % count($colors);
                                        $avatarColor = $colors[$colorIndex];
                                        $initials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $msg['username']), 0, 2));
                                        if (empty($initials)) {
                                            $initials = 'UD';
                                        }
                                        ?>
                                        <div class="direct-chat-img d-flex align-items-center justify-content-center rounded-circle text-white font-weight-bold text-uppercase border" 
                                             style="background-color: <?= $avatarColor ?>; width: 40px; height: 40px; font-size: 14px; display: flex !important; justify-content: center; align-items: center;">
                                            <?= esc($initials) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="direct-chat-text">
                                        <?php if (!empty($msg['message'])): ?>
                                            <div class="message-body"><?= nl2br(esc($msg['message'])) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($msg['attachment'])): ?>
                                            <?php if (str_ends_with(strtolower($msg['attachment']), '.pdf')): ?>
                                                <div class="message-attachment mt-2">
                                                    <a href="<?= base_url($msg['attachment']) ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-file-pdf mr-1"></i> Open PDF Document
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <div class="message-attachment mt-2">
                                                    <a href="<?= base_url($msg['attachment']) ?>" target="_blank">
                                                        <img src="<?= base_url($msg['attachment']) ?>" class="img-fluid rounded border shadow-sm" style="max-height: 180px; object-fit: contain; cursor: zoom-in;" alt="Attachment">
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <div class="text-right mt-1 bubble-timestamp-wrapper">
                                            <span class="bubble-timestamp"><?= $timeStr ?></span>
                                            <?php if ($isMe): ?>
                                                <span class="ml-1 read-receipt">
                                                    <?php if ($msg['is_read'] === '1' || $msg['is_read'] == 1): ?>
                                                        <i class="fas fa-check-double text-primary" title="Read"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-check-double text-muted" title="Delivered"></i>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- /.direct-chat-text -->
                                </div>
                                <!-- /.direct-chat-msg -->
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <!--/.direct-chat-messages-->
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <form id="chatForm" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <!-- Attachment Preview Section -->
                        <div id="attachmentPreviewWrapper" class="d-none border-bottom p-2 mb-2 bg-light rounded align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-image text-success fa-2x mr-2"></i>
                                <div>
                                    <span class="small font-weight-bold" id="previewFileName">filename.jpg</span>
                                    <br><span class="small text-muted" id="previewFileSize">0 KB</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger" id="btnCancelAttachment" title="Remove Attachment">
                                <i class="fas fa-times-circle fa-lg"></i>
                            </button>
                        </div>

                        <div class="input-group">
                            <!-- Image Attachment Button Trigger -->
                            <span class="input-group-prepend">
                                <button type="button" class="btn btn-default" id="btnAttachment" title="Add Image or PDF Attachment">
                                    <i class="fas fa-paperclip text-muted"></i>
                                </button>
                            </span>
                            <input type="file" id="chatAttachment" name="attachment" style="display:none;" accept="image/jpeg,image/jpg,image/png,image/gif,application/pdf">
                            
                            <input type="text" name="message" id="chatMessageInput" placeholder="Type a message to support..." class="form-control" autocomplete="off">
                            <span class="input-group-append">
                                <button type="submit" class="btn btn-primary" id="btnSend">
                                    <i class="fas fa-paper-plane mr-1"></i> Send
                                </button>
                            </span>
                        </div>
                    </form>
                </div>
                <!-- /.card-footer -->
            </div>
            <!--/.direct-chat -->
        </div>
    </section>
</div>

<style>
/* Chat bubble size wrap styles */
.direct-chat-messages {
    display: flex;
    flex-direction: column;
}
.direct-chat-msg {
    width: 100%;
    margin-bottom: 15px;
    clear: both;
}
.direct-chat-text {
    display: inline-block !important;
    float: left;
    max-width: 72%;
    width: auto;
    margin-left: 10px;
    margin-right: 0;
    word-wrap: break-word;
}
.right .direct-chat-text {
    float: right;
    margin-right: 10px;
    margin-left: 0;
}
.direct-chat-msg::after {
    content: "";
    display: table;
    clear: both;
}
.direct-chat-msg .direct-chat-text .bubble-timestamp {
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    font-size: 9px;
    color: #8a8d91; /* Default for left/incoming */
    margin-right: 4px;
}
.direct-chat-msg.right .direct-chat-text .bubble-timestamp {
    color: #cbd5e1; /* For right/outgoing (on primary background) */
}
.bubble-timestamp-wrapper .read-receipt i {
    font-size: 9px;
    margin-left: 2px;
}
.direct-chat-msg.right .direct-chat-text .bubble-timestamp-wrapper .read-receipt i.text-muted {
    color: #cbd5e1 !important; /* light gray on blue for unread double-check */
}
.direct-chat-msg.right .direct-chat-text .bubble-timestamp-wrapper .read-receipt i.text-primary {
    color: #38bdf8 !important; /* bright blue/cyan on blue for read double-check */
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chatMessages');
    const chatForm = document.getElementById('chatForm');
    const chatMessageInput = document.getElementById('chatMessageInput');
    const btnAttachment = document.getElementById('btnAttachment');
    const chatAttachment = document.getElementById('chatAttachment');
    const attachmentPreviewWrapper = document.getElementById('attachmentPreviewWrapper');
    const previewFileName = document.getElementById('previewFileName');
    const previewFileSize = document.getElementById('previewFileSize');
    const btnCancelAttachment = document.getElementById('btnCancelAttachment');
    const chatEmptyState = document.getElementById('chatEmptyState');
    const supportStatus = document.getElementById('supportStatus');

    let currentUserId = <?= (int)$user_info['id'] ?>;
    let lastSeenUuid = '';

    // Track the latest message UUID
    const msgElements = chatMessages.querySelectorAll('.direct-chat-msg');
    if (msgElements.length > 0) {
        lastSeenUuid = msgElements[msgElements.length - 1].dataset.uuid || '';
    }

    // Scroll to bottom helper
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    scrollToBottom();

    // Date formatting helper: Aug 27, 23:25:45 PM
    function formatChatDate(dateObj) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const month = months[dateObj.getMonth()];
        const day = String(dateObj.getDate()).padStart(2, '0');
        const hours = String(dateObj.getHours()).padStart(2, '0');
        const minutes = String(dateObj.getMinutes()).padStart(2, '0');
        const seconds = String(dateObj.getSeconds()).padStart(2, '0');
        const ampm = dateObj.getHours() >= 12 ? 'PM' : 'AM';
        return `${month} ${day}, ${hours}:${minutes}:${seconds} ${ampm}`;
    }

    // Browser chime sound generator via Web Audio API (does not require external file)
    function playNotificationSound() {
        if (document.hasFocus()) return; // Don't ring if the user is looking at the tab
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5 note
            gainNode.gain.setValueAtTime(0.08, audioCtx.currentTime);
            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 0.15);
        } catch (e) {
            console.error("Audio beep failed: ", e);
        }
    }

    // Trigger File Input Click
    btnAttachment.addEventListener('click', () => {
        chatAttachment.click();
    });

    // Handle File Attachment Selection
    chatAttachment.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Validate size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size exceeds the 5MB limit.');
                this.value = '';
                return;
            }
            // Validate file type (Images or PDF)
            const validMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
            if (!validMimes.includes(file.type)) {
                alert('Only image attachments (JPEG, PNG, GIF) and PDF files are allowed.');
                this.value = '';
                return;
            }

            // Update preview icon based on file type
            const previewIcon = attachmentPreviewWrapper.querySelector('.fas');
            if (previewIcon) {
                if (file.type === 'application/pdf') {
                    previewIcon.className = 'fas fa-file-pdf text-danger fa-2x mr-2';
                } else {
                    previewIcon.className = 'fas fa-file-image text-success fa-2x mr-2';
                }
            }

            previewFileName.innerText = file.name;
            previewFileSize.innerText = (file.size / 1024).toFixed(1) + ' KB';
            attachmentPreviewWrapper.classList.remove('d-none');
            attachmentPreviewWrapper.classList.add('d-flex');
        }
    });

    // Remove Selected Attachment
    btnCancelAttachment.addEventListener('click', () => {
        chatAttachment.value = '';
        attachmentPreviewWrapper.classList.remove('d-flex');
        attachmentPreviewWrapper.classList.add('d-none');
    });

    // Append Message to view
    function appendMessage(msg) {
        if (chatEmptyState) {
            chatEmptyState.remove();
        }

        // Avoid duplication if message element already exists
        if (document.querySelector(`.direct-chat-msg[data-uuid="${msg.uuid}"]`)) {
            return;
        }

        const isMe = parseInt(msg.sender_id) === currentUserId;
        
        // Build Avatar
        let avatarHTML = '';
        if (msg.profile_image) {
            const avatarUrl = `<?= base_url('uploads/profiles/') ?>${msg.profile_image}`;
            avatarHTML = `<img class="direct-chat-img border" src="${avatarUrl}" alt="Avatar">`;
        } else {
            const colors = ['#f56954', '#f39c12', '#0073b7', '#00c0ef', '#00a65a', '#3c8dbc', '#39cccc', '#605ca8', '#ff851b'];
            let hash = 0;
            for (let i = 0; i < msg.username.length; i++) {
                hash = msg.username.charCodeAt(i) + ((hash << 5) - hash);
            }
            const colorIndex = Math.abs(hash) % colors.length;
            const color = colors[colorIndex];
            const initials = msg.username.replace(/[^a-zA-Z0-9]/g, '').substring(0, 2).toUpperCase() || 'UD';
            avatarHTML = `<div class="direct-chat-img d-flex align-items-center justify-content-center rounded-circle text-white font-weight-bold text-uppercase border" style="background-color: ${color}; width: 40px; height: 40px; font-size: 14px; display: flex !important; justify-content: center; align-items: center;">${initials}</div>`;
        }
        
        // Format timestamp
        const date = new Date(msg.created_at);
        const timeStr = formatChatDate(date);

        let attachmentHTML = '';
        if (msg.attachment) {
            const isPdf = msg.attachment.toLowerCase().endsWith('.pdf');
            if (isPdf) {
                attachmentHTML = `
                    <div class="message-attachment mt-2">
                        <a href="<?= base_url() ?>${msg.attachment}" target="_blank" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf mr-1"></i> Open PDF Document
                        </a>
                    </div>`;
            } else {
                attachmentHTML = `
                    <div class="message-attachment mt-2">
                        <a href="<?= base_url() ?>${msg.attachment}" target="_blank">
                            <img src="<?= base_url() ?>${msg.attachment}" class="img-fluid rounded border shadow-sm" style="max-height: 180px; object-fit: contain; cursor: zoom-in;" alt="Attachment">
                        </a>
                    </div>`;
            }
        }

        let receiptHTML = '';
        if (isMe) {
            receiptHTML = `
                <span class="ml-1 read-receipt">
                    ${msg.is_read == 1 ? '<i class="fas fa-check-double text-primary" title="Read"></i>' : '<i class="fas fa-check-double text-muted" title="Delivered"></i>'}
                </span>`;
        }

        const msgHtml = `
            <div class="direct-chat-msg ${isMe ? 'right' : ''}" data-uuid="${msg.uuid}">
                <div class="direct-chat-infos clearfix">
                    <span class="direct-chat-name ${isMe ? 'float-right' : 'float-left'}">
                        ${escapeHtml(msg.username)}
                    </span>
                </div>
                ${avatarHTML}
                <div class="direct-chat-text">
                    ${msg.message ? `<div class="message-body">${nl2br(escapeHtml(msg.message))}</div>` : ''}
                    ${attachmentHTML}
                    <div class="text-right mt-1 bubble-timestamp-wrapper">
                        <span class="bubble-timestamp">${timeStr}</span>
                        ${receiptHTML}
                    </div>
                </div>
            </div>`;

        chatMessages.insertAdjacentHTML('beforeend', msgHtml);
    }

    // HTML Escape Helper
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Convert newlines to breaks
    function nl2br(str) {
        return str.replace(/\n/g, '<br>');
    }

    // Update Admins Online Status labels
    function updateAdminsStatus(status) {
        if (status.is_online) {
            supportStatus.innerHTML = '<span class="badge badge-success"><i class="fas fa-circle mr-1 text-white font-xs"></i> Online</span>';
        } else {
            let activeStr = '';
            if (status.last_active) {
                const date = new Date(status.last_active);
                activeStr = ` (Active ${formatChatDate(date)})`;
            }
            supportStatus.innerHTML = `<span class="badge badge-secondary">Offline ${activeStr}</span>`;
        }
    }

    // Handle Form Submit (Restful Endpoint)
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const messageVal = chatMessageInput.value.trim();
        const fileVal = chatAttachment.value;

        if (messageVal === '' && fileVal === '') {
            return;
        }

        const formData = new FormData(this);

        // Clear input immediately for seamless response UX
        chatMessageInput.value = '';
        chatAttachment.value = '';
        attachmentPreviewWrapper.classList.remove('d-flex');
        attachmentPreviewWrapper.classList.add('d-none');

        fetch('<?= base_url('api/v1/support/chat/send') ?>', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                appendMessage(data.message);
                scrollToBottom();
                lastSeenUuid = data.message.uuid;
            } else {
                alert('Error sending message: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Submit error:', error);
            alert('Failed to send message. Please try again.');
        });
    });

    // -------------------------------------------------------
    // AJAX Polling — polls for new messages every 5 seconds
    // -------------------------------------------------------
    let pollTimer = null;
    const POLL_INTERVAL = 5000; // 5 seconds

    async function pollMessages() {
        try {
            const res = await fetch(`<?= base_url('api/v1/support/chat/poll') ?>?last_seen_uuid=${lastSeenUuid}`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();

            // Append any new messages
            if (data.messages && data.messages.length > 0) {
                let hasNewReply = false;
                data.messages.forEach(msg => {
                    appendMessage(msg);
                    lastSeenUuid = msg.uuid;
                    if (parseInt(msg.sender_id) !== currentUserId) {
                        hasNewReply = true;
                    }
                });
                scrollToBottom();
                if (hasNewReply) {
                    playNotificationSound();
                }
            }

            // Update read receipts for our own outgoing messages
            if (data.messages) {
                data.messages.forEach(msg => {
                    if (parseInt(msg.sender_id) === currentUserId && (msg.is_read == 1 || msg.is_read === '1')) {
                        const msgDiv = document.querySelector(`.direct-chat-msg[data-uuid="${msg.uuid}"]`);
                        if (msgDiv) {
                            const receiptSpan = msgDiv.querySelector('.read-receipt');
                            if (receiptSpan) {
                                receiptSpan.innerHTML = '<i class="fas fa-check-double text-primary" title="Read"></i>';
                            }
                        }
                    }
                });
            }

            // Update admin online status indicator
            if (data.admins_status) {
                updateAdminsStatus(data.admins_status);
            }

            // Update the top navbar badge live
            const badge = document.getElementById('support-chat-badge');
            if (badge) {
                const count = data.unread_count || 0;
                if (count > 0) {
                    badge.textContent = count;
                    badge.classList.remove('d-none');
                } else {
                    badge.textContent = '';
                    badge.classList.add('d-none');
                }
            }
        } catch (e) {
            // Network error — silently ignore, retry on next interval
        }
    }

    function startPolling() {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(pollMessages, POLL_INTERVAL);
    }

    function stopPolling() {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = null;
    }

    // Pause polling when tab is hidden, resume when visible (saves server load)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopPolling();
        } else {
            pollMessages(); // immediate poll on tab focus
            startPolling();
        }
    });

    // Start polling on page load
    startPolling();
});
</script>
