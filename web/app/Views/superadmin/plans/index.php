<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-tags mr-2 text-primary"></i>Plans & Pricing</h1>
                </div>
                <div class="col-sm-6 d-flex align-items-center justify-content-end">
                    <a href="<?= base_url('superadmin/plans/definitions') ?>" class="btn btn-info btn-sm mr-3">
                        <i class="fas fa-cogs mr-1"></i> Manage Dynamic Definitions
                    </a>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('superadmin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Plans & Pricing</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="callout callout-info bg-light shadow-sm border-left-info mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info fa-2x mr-3"></i>
                    <div>
                        <h5 class="text-info font-weight-bold mb-1">Subscription Plans</h5>
                        <p class="mb-0 small text-muted">Manage plans, pricing, device limits and available ML features. Changes create a new version for full audit history.</p>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm mb-3">
                <div class="card-header p-2 bg-light">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a href="<?= base_url('superadmin/subscriptions') ?>" class="nav-link">
                                <i class="fas fa-users mr-1 text-primary"></i> Subscribers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('superadmin/payments') ?>" class="nav-link">
                                <i class="fas fa-credit-card mr-1 text-success"></i> Payment History
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('superadmin/plans') ?>" class="nav-link active">
                                <i class="fas fa-tags mr-1 text-warning"></i> Plans &amp; Pricing
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <?php foreach ($plans as $plan): ?>
                    <?php $version = $versions[$plan['id']] ?? null; ?>
                    <?php
                        $tier = $plan['slug'];
                        $tierBg = $tier === 'free' ? 'bg-gradient-secondary' : ($tier === 'gold' ? 'bg-gradient-pink' : 'bg-gradient-purple');
                    ?>
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card card-primary card-outline card-hover h-100">
                             <div class="card-header <?= $tierBg ?> border-bottom-0 py-3">
                                 <div class="d-flex justify-content-between align-items-center">
                                     <h3 class="card-title text-white mb-0">
                                         <i class="fas fa-<?= $tier === 'free' ? 'star' : ($tier === 'gold' ? 'crown' : 'gem') ?> mr-2"></i>
                                         <?= esc($plan['name']) ?>
                                     </h3>
                                     <?php if ($version): ?>
                                         <span class="badge badge-light">v<?= $version['version'] ?></span>
                                     <?php else: ?>
                                         <span class="badge badge-secondary">No version</span>
                                     <?php endif; ?>
                                 </div>
                             </div>
                             <div class="card-body p-0">
                                 <?php if ($version): ?>
                                     <ul class="list-group list-group-flush">
                                         <li class="list-group-item d-flex justify-content-between align-items-center">
                                             <span><i class="fas fa-dollar-sign text-success mr-2"></i>Monthly Price</span>
                                             <span class="font-weight-bold text-success">$<?= number_format($version['price_monthly_cents'] / 100, 2) ?></span>
                                         </li>
                                         <li class="list-group-item d-flex justify-content-between align-items-center">
                                             <span><i class="fas fa-dollar-sign text-primary mr-2"></i>Yearly Price</span>
                                             <span class="font-weight-bold text-primary">$<?= number_format($version['price_yearly_cents'] / 100, 2) ?></span>
                                         </li>
                                         <li class="list-group-item d-flex justify-content-between align-items-center">
                                             <span><i class="fas fa-mobile-alt text-info mr-2"></i>Max Devices</span>
                                             <span class="badge badge-info"><?= $version['max_devices'] ?></span>
                                         </li>
                                         <li class="list-group-item d-flex justify-content-between align-items-center">
                                             <span><i class="fas fa-history text-warning mr-2"></i>History Retention</span>
                                             <span class="badge badge-warning"><?= $version['history_days'] ?> days</span>
                                         </li>
                                          <li class="list-group-item">
                                              <span class="d-block mb-2 font-weight-bold"><i class="fas fa-cubes text-indigo mr-2"></i>Allowed Profiles</span>
                                              <div>
                                                  <?php 
                                                      $featArr = json_decode($version['features'], true) ?: []; 
                                                      $hwProfile = $featArr['hardware_profile'] ?? 'basic';
                                                      $swProfile = $featArr['software_profile'] ?? 'basic';
                                                      $fcmGroups = $featArr['fcm_groups'] ?? [];
                                                      if (is_string($fcmGroups)) {
                                                          $fcmGroups = json_decode($fcmGroups, true) ?: [];
                                                      }

                                                      $standardFeaturesList = [
                                                          'risk_score' => 'Device Risk Score',
                                                          'geofencing' => 'Location Safety',
                                                          'forensic_export' => 'Forensic Export',
                                                          'wellbeing' => 'Wellbeing Insights',
                                                          'smart_timeline' => 'Smart Timeline',
                                                          'correlation' => 'Correlation Engine',
                                                          'care_plan' => 'Care Plans'
                                                      ];
                                                  ?>
                                                  <span class="badge bg-indigo mr-1 mb-1">
                                                      <i class="fas fa-microchip mr-1"></i>HW: <?= $hwProfile === 'all' ? 'All (Platinum)' : ($hwProfile === 'advanced' ? 'Advanced (Gold)' : 'Basic (Free)') ?>
                                                  </span>
                                                  <span class="badge bg-navy mr-1 mb-1">
                                                      <i class="fas fa-laptop-code mr-1"></i>SW: <?= $swProfile === 'all' ? 'All (Platinum)' : ($swProfile === 'advanced' ? 'Advanced (Gold)' : 'Basic (Free)') ?>
                                                  </span>
                                              </div>
                                          </li>
                                          <li class="list-group-item">
                                              <span class="d-block mb-2 font-weight-bold"><i class="fas fa-star text-warning mr-2"></i>Standard Features</span>
                                              <div>
                                                  <?php foreach ($standardFeaturesList as $key => $label): ?>
                                                      <?php $enabled = !empty($featArr[$key]); ?>
                                                      <span class="badge <?= $enabled ? 'badge-success' : 'badge-secondary' ?> mr-1 mb-1">
                                                          <i class="fas fa-<?= $enabled ? 'check' : 'times' ?> mr-1"></i><?= $label ?>
                                                      </span>
                                                  <?php endforeach; ?>
                                              </div>
                                          </li>
                                          <li class="list-group-item">
                                              <span class="d-block mb-2 font-weight-bold"><i class="fas fa-paper-plane text-success mr-2"></i>FCM Commands</span>
                                              <div>
                                                  <span class="badge <?= in_array('core', $fcmGroups, true) ? 'badge-success' : 'badge-secondary' ?> mr-1 mb-1">
                                                      <i class="fas fa-check-circle mr-1"></i>Core (Free)
                                                  </span>
                                                  <span class="badge <?= in_array('advanced', $fcmGroups, true) ? 'badge-warning' : 'badge-secondary' ?> mr-1 mb-1">
                                                      <i class="fas fa-crown mr-1"></i>Advanced (Gold)
                                                  </span>
                                                  <span class="badge <?= in_array('deep', $fcmGroups, true) ? 'badge-danger' : 'badge-secondary' ?> mr-1 mb-1">
                                                      <i class="fas fa-gem mr-1"></i>Deep (Platinum)
                                                  </span>
                                              </div>
                                          </li>
                                          <li class="list-group-item">
                                              <span class="d-block mb-2 font-weight-bold"><i class="fas fa-brain text-purple mr-2"></i>ML Algorithms</span>
                                              <div>
                                                   <?php
                                                       $algos = json_decode($version['ml_algorithms'] ?? '[]', true);
                                                       if (!is_array($algos)) {
                                                           $algos = [];
                                                       }
                                                   ?>
                                                   <span class="badge <?= in_array('core', $algos, true) ? 'badge-success' : 'badge-secondary' ?> mr-1 mb-1">
                                                       <i class="fas fa-shield-alt mr-1"></i>Core (Free)
                                                   </span>
                                                   <span class="badge <?= in_array('advanced', $algos, true) ? 'badge-warning' : 'badge-secondary' ?> mr-1 mb-1">
                                                       <i class="fas fa-project-diagram mr-1"></i>Advanced (Gold)
                                                   </span>
                                                   <span class="badge <?= in_array('deep', $algos, true) ? 'badge-danger' : 'badge-secondary' ?> mr-1 mb-1">
                                                       <i class="fas fa-brain mr-1"></i>Deep (Platinum)
                                                   </span>
                                              </div>
                                          </li>
                                          <li class="list-group-item d-flex justify-content-between align-items-center">
                                              <span><i class="fas fa-bell text-danger mr-2"></i>Alerts</span>
                                              <span class="badge badge-light">
                                                  <?php if ($version['alert_email']): ?><i class="fas fa-envelope mr-1"></i>Email<?php else: ?>None<?php endif; ?>
                                              </span>
                                          </li>
                                         <li class="list-group-item d-flex justify-content-between align-items-center">
                                             <span><i class="fas fa-heartbeat text-danger mr-2"></i>Wellbeing</span>
                                             <span class="badge badge-light"><?= ($version['wellbeing_depth'] ?? '7') === 'all' ? 'All Data' : ($version['wellbeing_depth'] ?? '7') . ' Days' ?></span>
                                         </li>
                                         <li class="list-group-item d-flex justify-content-between align-items-center">
                                             <span><i class="fas fa-headset text-primary mr-2"></i>Support</span>
                                             <span class="badge badge-light"><?= ucfirst($version['support_tier'] ?? 'standard') ?></span>
                                         </li>
                                    </ul>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-exclamation-circle fa-3x mb-3 text-warning"></i>
                                        <p class="mb-1">No version created yet</p>
                                        <p class="small mb-0">Click Edit to create the first version</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white d-flex align-items-center">
                                <a href="<?= base_url("superadmin/plans/editVersion/{$plan['id']}") ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <a href="<?= base_url("superadmin/plans/history/{$plan['id']}") ?>" class="btn btn-outline-secondary btn-sm ml-auto">
                                    <i class="fas fa-history mr-1"></i> History
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<style>
.card-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.text-purple { color: #6f42c1 !important; }
.bg-gradient-purple { background: linear-gradient(45deg, #6f42c1, #9058e6) !important; }
.bg-gradient-pink { background: linear-gradient(45deg, #e83e8c, #ffd700) !important; }
.bg-gradient-secondary { background: linear-gradient(45deg, #6c757d, #adb5bd) !important; }
.bg-purple { background-color: #6f42c1 !important; color: #fff; }
.card-outline.card-primary { border-top: 3px solid #007bff; }
</style>
