<div class="text-center p-5 anomaly-empty">
    <div class="ae-icon mx-auto mb-4" style="width: 80px; height: 80px; border-radius: 50%; background: var(--primary, #007bff); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(0,123,255,.35);">
        <i class="fas <?= esc($icon ?? 'fa-inbox') ?> fa-2x text-white"></i>
    </div>
    <h4 style="font-size: 1.3rem; font-weight: 700; color: inherit;"><?= esc($title ?? 'No Data Found') ?></h4>
    <p class="text-muted" style="max-width: 400px; margin: 0 auto;"><?= esc($message ?? 'There is currently no data available to display here.') ?></p>
</div>
