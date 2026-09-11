<?= view('headers_footers/head_landing', ['pag' => 'account_suspended']) ?>

<div class="content-wrapper" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); min-height: 100vh; padding: 40px 15px;">
    <div class="container" style="max-width: 960px;">
        
        <!-- Header Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 text-white pb-3 border-bottom border-secondary">
            <div class="d-flex align-items-center">
                <img src="<?= base_url('assets/img/logo.png') ?>" alt="Logo" class="brand-image img-circle elevation-2 mr-3" style="width: 48px; height: 48px; object-fit: contain;">
                <div>
                    <h4 class="mb-0 font-weight-bold">Eaves Droid</h4>
                    <span class="text-danger small font-weight-bold"><i class="fas fa-shield-alt mr-1"></i> Security &amp; Compliance Quarantine</span>
                </div>
            </div>
            <div>
                <a href="<?= base_url('logout') ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt mr-1"></i> Sign Out
                </a>
            </div>
        </div>

        <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i> <?= session()->getFlashdata('message') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php endif; ?>

        <!-- Suspension Alert Card -->
        <div class="card card-outline card-danger shadow-lg mb-4">
            <div class="card-header bg-danger text-white py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-slash fa-2x mr-3"></i>
                    <div>
                        <h4 class="card-title font-weight-bold mb-0 text-white">Your Account Has Been Suspended</h4>
                        <div class="small text-white-50">Standard dashboard and telemetry access is locked</div>
                    </div>
                </div>
            </div>
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-id-card mr-1 text-secondary"></i> Account Information</h6>
                        <ul class="list-group list-group-unbordered small">
                            <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between">
                                <span class="text-muted">Username:</span>
                                <strong class="text-dark"><?= esc($user->username) ?></strong>
                            </li>
                            <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between">
                                <span class="text-muted">Account ID:</span>
                                <span class="badge badge-secondary">#<?= esc($user->id) ?></span>
                            </li>
                            <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between">
                                <span class="text-muted">Status:</span>
                                <span class="badge badge-danger"><i class="fas fa-ban mr-1"></i> Suspended</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="font-weight-bold text-danger mb-2"><i class="fas fa-info-circle mr-1"></i> Reason / Notice</h6>
                        <div class="p-3 bg-white border border-danger rounded small text-dark mb-2" style="min-height: 80px;">
                            <?= esc($suspensionReason) ?>
                        </div>
                        <span class="text-muted small">
                            <i class="fas fa-lock mr-1"></i> All device data streams, remote controls, and automated jobs are paused.
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appeal & Admin Live Chat -->
        <div class="card card-outline card-primary direct-chat direct-chat-primary shadow-lg" id="appealChatCard">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-dark font-weight-bold mb-0">
                        <i class="fas fa-comments text-primary mr-2"></i>Live Appeal &amp; Support Chat
                    </h5>
                    <div class="card-tools">
                        <span class="badge <?= ($adminsStatus['is_online'] ?? false) ? 'badge-success' : 'badge-secondary' ?> py-1 px-2" id="adminStatusBadge">
                            <i class="fas fa-circle font-xs mr-1"></i>
                            <?= ($adminsStatus['is_online'] ?? false) ? 'Admins Online' : 'Admins Offline (Ticketing Mode)' ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="direct-chat-messages p-3" id="appealMessages" style="height: 380px; overflow-y: auto; background-color: #f8fafc;">
                    <?php if (empty($thread)): ?>
                        <div class="text-center py-5" id="appealEmptyState">
                            <i class="far fa-comments fa-4x text-muted mb-3"></i>
                            <h5 class="text-dark font-weight-bold">Submit Your Appeal</h5>
                            <p class="text-muted small" style="max-width: 480px; margin: 0 auto;">
                                If you believe this suspension is a mistake or wish to provide verification documents, write your explanation below. Our administration team will review and reply directly.
                            </p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($thread as $msg): ?>
                            <?php
                            $isMe = (int)($msg['sender_id'] ?? 0) === (int)$user->id;
                            $timeStr = date('M d, H:i A', strtotime($msg['created_at'] ?? 'now'));
                            ?>
                            <div class="direct-chat-msg <?= $isMe ? 'right' : '' ?> mb-3">
                                <div class="direct-chat-infos clearfix mb-1">
                                    <span class="direct-chat-name <?= $isMe ? 'float-right' : 'float-left' ?> font-weight-bold small text-dark">
                                        <?= $isMe ? 'You (Appeal)' : 'Administrator' ?>
                                    </span>
                                    <span class="direct-chat-timestamp <?= $isMe ? 'float-left' : 'float-right' ?> small text-muted">
                                        <?= esc($timeStr) ?>
                                    </span>
                                </div>
                                <div class="direct-chat-text <?= $isMe ? 'bg-primary text-white border-primary' : 'bg-white text-dark shadow-sm' ?> py-2 px-3 rounded">
                                    <?= nl2br(esc($msg['message'] ?? '')) ?>
                                    <?php if (!empty($msg['attachment'])): ?>
                                        <div class="mt-2 pt-2 border-top border-light">
                                            <a href="<?= base_url($msg['attachment']) ?>" target="_blank" class="<?= $isMe ? 'text-white font-weight-bold' : 'text-primary' ?> small">
                                                <i class="fas fa-paperclip mr-1"></i> View Attachment
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-footer bg-white border-top py-3">
                <form id="appealChatForm" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="input-group">
                        <input type="text" name="message" id="appealInput" placeholder="Type your appeal explanation or response to admins..." class="form-control" autocomplete="off" required>
                        <div class="input-group-append">
                            <label class="btn btn-outline-secondary mb-0" for="appealAttachment" title="Attach file (PNG, JPG, PDF)">
                                <i class="fas fa-paperclip"></i>
                                <input type="file" id="appealAttachment" name="attachment" style="display:none;" accept=".png,.jpg,.jpeg,.gif,.pdf">
                            </label>
                            <button type="submit" class="btn btn-primary" id="appealSubmitBtn">
                                <i class="fas fa-paper-plane mr-1"></i> Send Appeal
                            </button>
                        </div>
                    </div>
                    <div id="filePreview" class="small text-muted mt-2" style="display:none;">
                        <i class="fas fa-file mr-1 text-primary"></i> <span id="fileName"></span>
                        <a href="javascript:void(0)" onclick="clearAttachment()" class="text-danger ml-2"><i class="fas fa-times"></i> Remove</a>
                    </div>
                    <div id="chatAlert" class="mt-2 small" style="display:none;"></div>
                </form>
            </div>
        </div>

        <div class="text-center text-muted small mt-4">
            <span>Need further help? Contact <a href="mailto:support@eavesdroid.com" class="text-info">support@eavesdroid.com</a></span>
        </div>

    </div>
</div>

<script>
const currentUserId = <?= (int)$user->id ?>;
const pollUrl = '<?= base_url('account/suspended/chat/poll') ?>';
const sendUrl = '<?= base_url('account/suspended/chat/send') ?>';

function scrollChatToBottom() {
    const container = document.getElementById('appealMessages');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    scrollChatToBottom();

    const attachInput = document.getElementById('appealAttachment');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');

    if (attachInput) {
        attachInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileName.textContent = this.files[0].name + ' (' + Math.round(this.files[0].size / 1024) + ' KB)';
                filePreview.style.display = 'block';
            } else {
                filePreview.style.display = 'none';
            }
        });
    }

    const form = document.getElementById('appealChatForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('appealInput');
            const submitBtn = document.getElementById('appealSubmitBtn');
            const text = input.value.trim();
            const file = attachInput && attachInput.files ? attachInput.files[0] : null;

            if (!text && !file) return;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sending...';

            const formData = new FormData(form);

            fetch(sendUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Send Appeal';
                if (data.success) {
                    input.value = '';
                    clearAttachment();
                    showChatAlert('success', data.message || 'Appeal message sent successfully.');
                    pollMessages();
                } else {
                    showChatAlert('danger', data.error || 'Failed to send message.');
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Send Appeal';
                showChatAlert('danger', 'Network error. Please try again.');
            });
        });
    }

    // Auto-poll messages every 4 seconds
    setInterval(pollMessages, 4000);
});

function clearAttachment() {
    const attachInput = document.getElementById('appealAttachment');
    const filePreview = document.getElementById('filePreview');
    if (attachInput) attachInput.value = '';
    if (filePreview) filePreview.style.display = 'none';
}

function showChatAlert(type, msg) {
    const el = document.getElementById('chatAlert');
    if (!el) return;
    el.className = 'alert alert-' + type + ' py-1 px-2 mb-0 small mt-2';
    el.innerHTML = msg;
    el.style.display = 'block';
    setTimeout(() => { el.style.display = 'none'; }, 6000);
}

function pollMessages() {
    fetch(pollUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.thread) return;
        renderMessages(data.thread);
    })
    .catch(() => {});
}

function renderMessages(thread) {
    const container = document.getElementById('appealMessages');
    if (!container) return;

    if (thread.length === 0) return;

    const emptyState = document.getElementById('appealEmptyState');
    if (emptyState) emptyState.remove();

    let html = '';
    thread.forEach(msg => {
        const isMe = parseInt(msg.sender_id) === currentUserId;
        const timeStr = msg.created_at ? new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
        const attachmentHtml = msg.attachment ? 
            `<div class="mt-2 pt-2 border-top border-light"><a href="<?= base_url() ?>/${msg.attachment}" target="_blank" class="${isMe ? 'text-white font-weight-bold' : 'text-primary'} small"><i class="fas fa-paperclip mr-1"></i> View Attachment</a></div>` : '';

        html += `
            <div class="direct-chat-msg ${isMe ? 'right' : ''} mb-3">
                <div class="direct-chat-infos clearfix mb-1">
                    <span class="direct-chat-name ${isMe ? 'float-right' : 'float-left'} font-weight-bold small text-dark">
                        ${isMe ? 'You (Appeal)' : 'Administrator'}
                    </span>
                    <span class="direct-chat-timestamp ${isMe ? 'float-left' : 'float-right'} small text-muted">
                        ${timeStr}
                    </span>
                </div>
                <div class="direct-chat-text ${isMe ? 'bg-primary text-white border-primary' : 'bg-white text-dark shadow-sm'} py-2 px-3 rounded">
                    ${escapeHtml(msg.message || '')}
                    ${attachmentHtml}
                </div>
            </div>
        `;
    });

    const isNearBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;
    container.innerHTML = html;
    if (isNearBottom) {
        scrollChatToBottom();
    }
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}
</script>

<?= view('headers_footers/footer_landing') ?>
