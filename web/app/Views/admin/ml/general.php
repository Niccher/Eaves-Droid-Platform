                            <div class="tab-pane active show" id="pane-general" role="tabpanel">
                                <form action="<?= base_url('admin/settings/update') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="section" value="ml">
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label font-weight-bold">Enable ML Features</label>
                                        <div class="col-sm-10">
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="ml_enabled" value="0">
                                                <input type="checkbox" name="ml_enabled" class="custom-control-input" id="ml_enabled" value="1" <?= ($settings['ml_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="ml_enabled">Enable machine learning analysis</label>
                                            </div>
                                            <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                <i class="fas fa-lightbulb text-info mr-1"></i>
                                                Master switch for all ML-powered features. When disabled, no algorithms execute and the anomaly detection pipeline is bypassed entirely.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label font-weight-bold">Anomaly Detection</label>
                                        <div class="col-sm-10">
                                            <div class="custom-control custom-switch">
                                                <input type="hidden" name="ml_anomaly_enabled" value="0">
                                                <input type="checkbox" name="ml_anomaly_enabled" class="custom-control-input" id="ml_anomaly_enabled" value="1" <?= ($settings['ml_anomaly_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="ml_anomaly_enabled">Enable anomaly detection on uploaded data</label>
                                            </div>
                                            <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                <i class="fas fa-lightbulb text-info mr-1"></i>
                                                Automatically runs anomaly detection against new device uploads. Requires ML Features enabled. Triggers analysis on SMS, calls, locations, contacts, and app data per upload event.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <label class="col-sm-2 col-form-label font-weight-bold">Analysis Schedule</label>
                                        <div class="col-sm-10">
                                            <select name="ml_schedule_interval" class="form-control">
                                                <option value="hourly" <?= ($settings['ml_schedule_interval'] ?? 'daily') === 'hourly' ? 'selected' : '' ?>>Hourly</option>
                                                <option value="daily" <?= ($settings['ml_schedule_interval'] ?? 'daily') === 'daily' ? 'selected' : '' ?>>Daily</option>
                                                <option value="weekly" <?= ($settings['ml_schedule_interval'] ?? 'daily') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                                            </select>
                                            <div class="callout callout-info bg-light py-2 px-3 mt-2 mb-0 small">
                                                <i class="fas fa-lightbulb text-info mr-1"></i>
                                                How often to re-run batch analysis. <strong>Hourly</strong> &mdash; near real-time, higher server load. <strong>Daily</strong> &mdash; balanced for most deployments. <strong>Weekly</strong> &mdash; minimal overhead, suitable for low-traffic environments.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-top pt-3 mt-3 text-right">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save General ML Settings</button>
                                    </div>
                                </form>
                            </div>

