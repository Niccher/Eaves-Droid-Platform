<?php
$dataHubTabs = [
    'sms' => ['label' => '💬 SMS', 'url' => base_url('sms'), 'count' => $total_sms ?? 0],
    'call_logs' => ['label' => '📞 Calls', 'url' => base_url('call_logs'), 'count' => $total_calls ?? 0],
    'files' => ['label' => '📁 Files', 'url' => base_url('files'), 'count' => $total_files ?? 0],
    'contacts' => ['label' => '👥 Contacts', 'url' => base_url('contacts'), 'count' => $total_contacts ?? 0],
    'location' => ['label' => '📍 Location', 'url' => base_url('location'), 'count' => $total_location_activity ?? (($total_locations ?? 0) + ($total_activities ?? 0))],
    'apps' => ['label' => '📱 Apps', 'url' => base_url('apps'), 'count' => $total_apps ?? 0],
];

// Determine active tab based on $pag variable
$activeTab = $pag ?? 'sms';
if ($activeTab == 'activities' || $activeTab == 'activity') $activeTab = 'location';
if ($activeTab == 'sms_analyse') $activeTab = 'contacts';
?>


<section class="content mb-1">
    <div class="container-fluid">
        <div class="card card-outline card-primary shadow-sm mb-0">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="dataHubTabs" role="tablist">
                    <?php foreach ($dataHubTabs as $key => $tab): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activeTab == $key) ? 'active font-weight-bold text-primary' : 'text-muted' ?>" href="<?= $tab['url'] ?>" style="border-radius: 0; padding: 12px 18px; transition: all 0.2s;">
                            <?= $tab['label'] ?>
                            <span class="badge <?= ($activeTab == $key) ? 'badge-primary' : 'badge-light border' ?> ml-2"><?= $tab['count'] ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>
