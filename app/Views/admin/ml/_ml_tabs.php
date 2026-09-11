                        <ul class="nav nav-pills mb-3" id="ml-tabs">
                            <li class="nav-item">
                                <a class="nav-link <?= (empty($active_tab) || $active_tab === 'general' || $active_tab === 'overview') ? 'active' : '' ?>" href="<?= base_url('admin/ml/general') ?>">
                                    <i class="fas fa-heartbeat mr-1 text-danger"></i> Telemetry &amp; Overview
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($active_tab === 'engines') ? 'active' : '' ?>" href="<?= base_url('admin/ml/engines') ?>">
                                    <i class="fas fa-microchip mr-1 text-info"></i> Detection Engines
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($active_tab === 'algorithms') ? 'active' : '' ?>" href="<?= base_url('admin/ml/algorithms') ?>">
                                    <i class="fas fa-sliders-h mr-1 text-warning"></i> Algorithms &amp; Rules
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($active_tab === 'python') ? 'active' : '' ?>" href="<?= base_url('admin/ml/python') ?>">
                                    <i class="fab fa-python mr-1 text-warning"></i> Python Backend
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($active_tab === 'phpml') ? 'active' : '' ?>" href="<?= base_url('admin/ml/phpml') ?>">
                                    <i class="fab fa-php mr-1 text-primary"></i> PHP-ML Local
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= ($active_tab === 'history') ? 'active' : '' ?>" href="<?= base_url('admin/ml/history') ?>">
                                    <i class="fas fa-history mr-1 text-secondary"></i> Run History
                                </a>
                            </li>
                        </ul>
