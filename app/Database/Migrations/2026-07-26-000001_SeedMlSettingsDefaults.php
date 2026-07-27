<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedMlSettingsDefaults extends Migration
{
    public function up()
    {
        $db = db_connect();

        $defaults = [
            // General
            ['ml_enabled', '1'],
            ['ml_anomaly_enabled', '1'],
            ['ml_schedule_interval', 'daily'],

            // PHP-ML: K-Means Clustering
            ['ml_phpml_kmeans_k', '3'],

            // PHP-ML: DBSCAN Density-Based Clustering
            ['ml_phpml_dbscan_epsilon', '0.01'],
            ['ml_phpml_dbscan_minpoints', '2'],

            // PHP-ML: Isolation Forest
            ['ml_phpml_isolationforest_trees', '100'],
            ['ml_phpml_isolationforest_samples', '256'],

            // Python: Connection Settings
            ['ml_python_enabled', '0'],
            ['ml_python_host', 'ml-eaves-droid'],
            ['ml_python_port', '9070'],
            ['ml_python_endpoint', '/api/analyze'],
            ['ml_python_url', ''],

            // Python: Autoencoder Neural Network
            ['ml_python_autoencoder_latent', '16'],
            ['ml_python_autoencoder_epochs', '50'],
            ['ml_python_autoencoder_threshold', '3.0'],

            // Python: LSTM Sequence Predictor
            ['ml_python_lstm_sequence', '20'],
            ['ml_python_lstm_units', '64'],

            // Python: One-Class SVM
            ['ml_python_oneclass_nu', '0.05'],
            ['ml_python_oneclass_gamma', '0.01'],

            // Python: Isolation Forest
            ['ml_python_iforest_trees', '200'],
            ['ml_python_iforest_samples', '512'],
            ['ml_python_iforest_contamination', '0.05'],
        ];

        foreach ($defaults as [$key, $value]) {
            $existing = $db->table('settings')
                ->where('class', 'ml')
                ->where('key', $key)
                ->get()
                ->getRow();

            if (!$existing) {
                $db->table('settings')->insert([
                    'class'      => 'ml',
                    'key'        => $key,
                    'value'      => $value,
                    'type'       => 'string',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down()
    {
        $db = db_connect();
        $db->table('settings')
            ->where('class', 'ml')
            ->delete();
    }
}
