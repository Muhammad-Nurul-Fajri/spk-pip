<?php
/**
 * AhpWpHelper — Centralized AHP + WP Calculation Engine
 * 
 * Implements:
 * 1. Analytical Hierarchy Process (AHP) for criteria weight derivation & consistency validation
 * 2. Weighted Product (WP) for alternative ranking using AHP-derived weights
 */

class AhpWpHelper {

    /**
     * Random Index (RI) table based on Saaty (n = 1..15)
     */
    private static $ri_table = [
        1 => 0.00,
        2 => 0.00,
        3 => 0.58,
        4 => 0.90,
        5 => 1.12,
        6 => 1.24,
        7 => 1.32,
        8 => 1.41,
        9 => 1.45,
        10 => 1.49,
        11 => 1.51,
        12 => 1.48,
        13 => 1.56,
        14 => 1.57,
        15 => 1.59
    ];

    /**
     * Get Random Index (RI) value for a given matrix size n
     */
    public static function getRI($n) {
        return self::$ri_table[$n] ?? 1.59;
    }

    /**
     * Fetch all criteria ordered by kode_kriteria
     */
    public static function getKriteria($koneksi) {
        $list = [];
        $res = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY kode_kriteria ASC");
        while ($row = mysqli_fetch_assoc($res)) {
            $list[] = $row;
        }
        return $list;
    }

    /**
     * Fetch raw pairwise comparison values from DB as an associative lookup:
     * $matrix[id_kriteria_1][id_kriteria_2] = nilai
     */
    public static function getPairwiseData($koneksi) {
        $data = [];
        $res = mysqli_query($koneksi, "SELECT * FROM ahp_perbandingan");
        while ($row = mysqli_fetch_assoc($res)) {
            $data[$row['id_kriteria_1']][$row['id_kriteria_2']] = floatval($row['nilai']);
        }
        return $data;
    }

    /**
     * Build complete n x n pairwise comparison matrix.
     * Fills diagonal with 1.0, uses stored values or defaults to 1.0,
     * and calculates reciprocals (1/nilai) for lower triangle.
     */
    public static function buildPairwiseMatrix($kriteria, $raw_data) {
        $matrix = [];
        $ids = array_column($kriteria, 'id');

        foreach ($ids as $i) {
            foreach ($ids as $j) {
                if ($i == $j) {
                    $matrix[$i][$j] = 1.0;
                } else if (isset($raw_data[$i][$j])) {
                    $val = floatval($raw_data[$i][$j]);
                    $matrix[$i][$j] = $val;
                    $matrix[$j][$i] = ($val > 0) ? (1.0 / $val) : 1.0;
                } else if (isset($raw_data[$j][$i])) {
                    $val = floatval($raw_data[$j][$i]);
                    $matrix[$j][$i] = $val;
                    $matrix[$i][$j] = ($val > 0) ? (1.0 / $val) : 1.0;
                } else {
                    $matrix[$i][$j] = 1.0;
                    $matrix[$j][$i] = 1.0;
                }
            }
        }
        return $matrix;
    }

    /**
     * Calculate column sums of pairwise matrix
     */
    public static function calculateColumnSums($matrix, $kriteria_ids) {
        $col_sums = [];
        foreach ($kriteria_ids as $j) {
            $sum = 0;
            foreach ($kriteria_ids as $i) {
                $sum += $matrix[$i][$j];
            }
            $col_sums[$j] = $sum;
        }
        return $col_sums;
    }

    /**
     * Step 2: Normalize Pairwise Comparison Matrix (rij = xij / Σxkj)
     */
    public static function normalizeMatrix($matrix, $col_sums, $kriteria_ids) {
        $normalized = [];
        foreach ($kriteria_ids as $i) {
            foreach ($kriteria_ids as $j) {
                $denom = $col_sums[$j] > 0 ? $col_sums[$j] : 1.0;
                $normalized[$i][$j] = $matrix[$i][$j] / $denom;
            }
        }
        return $normalized;
    }

    /**
     * Step 3: Calculate Priority Vector / AHP Weights (Wi = Σrij / n)
     */
    public static function calculatePriorityVector($normalized_matrix, $kriteria_ids) {
        $priority_vector = [];
        $n = count($kriteria_ids);
        foreach ($kriteria_ids as $i) {
            $row_sum = array_sum($normalized_matrix[$i]);
            $priority_vector[$i] = ($n > 0) ? ($row_sum / $n) : 0;
        }
        return $priority_vector;
    }

    /**
     * Step 4: Calculate λmax (Eigenvalue)
     * Weighted sum vector = Matrix * Priority Vector
     * λmax = Average of (Weighted Sum / Priority Vector)
     */
    public static function calculateLambdaMax($matrix, $priority_vector, $kriteria_ids) {
        $n = count($kriteria_ids);
        if ($n == 0) return 0;

        $weighted_sum = [];
        foreach ($kriteria_ids as $i) {
            $sum = 0;
            foreach ($kriteria_ids as $j) {
                $sum += $matrix[$i][$j] * $priority_vector[$j];
            }
            $weighted_sum[$i] = $sum;
        }

        $ratios = [];
        foreach ($kriteria_ids as $i) {
            if ($priority_vector[$i] > 0) {
                $ratios[] = $weighted_sum[$i] / $priority_vector[$i];
            }
        }

        return (count($ratios) > 0) ? (array_sum($ratios) / count($ratios)) : 0;
    }

    /**
     * Step 5: Calculate Consistency Index (CI = (λmax - n) / (n - 1))
     */
    public static function calculateCI($lambda_max, $n) {
        if ($n <= 1) return 0;
        return ($lambda_max - $n) / ($n - 1);
    }

    /**
     * Step 6 & 7: Calculate Consistency Ratio (CR = CI / RI) & Validate
     */
    public static function calculateCR($ci, $n) {
        $ri = self::getRI($n);
        if ($ri == 0) return 0; // For n=1,2 CR is always 0 (consistent)
        return $ci / $ri;
    }

    /**
     * Check if matrix is consistent (CR <= 0.10)
     */
    public static function isConsistent($cr) {
        return $cr <= 0.10;
    }

    /**
     * Complete AHP Pipeline
     */
    public static function processAHP($koneksi) {
        $kriteria = self::getKriteria($koneksi);
        $kriteria_ids = array_column($kriteria, 'id');
        $n = count($kriteria_ids);

        if ($n == 0) {
            return [
                'success' => false,
                'message' => 'Belum ada data kriteria.'
            ];
        }

        $raw_data = self::getPairwiseData($koneksi);
        $matrix = self::buildPairwiseMatrix($kriteria, $raw_data);
        $col_sums = self::calculateColumnSums($matrix, $kriteria_ids);
        $normalized = self::normalizeMatrix($matrix, $col_sums, $kriteria_ids);
        $priority_vector = self::calculatePriorityVector($normalized, $kriteria_ids);
        $lambda_max = self::calculateLambdaMax($matrix, $priority_vector, $kriteria_ids);
        $ci = self::calculateCI($lambda_max, $n);
        $ri = self::getRI($n);
        $cr = self::calculateCR($ci, $n);
        $consistent = self::isConsistent($cr);

        return [
            'success' => true,
            'kriteria' => $kriteria,
            'kriteria_ids' => $kriteria_ids,
            'n' => $n,
            'matrix' => $matrix,
            'col_sums' => $col_sums,
            'normalized' => $normalized,
            'priority_vector' => $priority_vector,
            'lambda_max' => $lambda_max,
            'ci' => $ci,
            'ri' => $ri,
            'cr' => $cr,
            'is_consistent' => $consistent,
            'status' => $consistent ? 'konsisten' : 'tidak_konsisten'
        ];
    }

    /**
     * Save AHP Calculation Results to DB
     */
    public static function saveAhpResults($koneksi, $ahp_result) {
        if (!$ahp_result['success']) return false;

        mysqli_begin_transaction($koneksi);
        try {
            // Save consistency check
            mysqli_query($koneksi, "DELETE FROM ahp_konsistensi");
            $st1 = mysqli_prepare($koneksi, "INSERT INTO ahp_konsistensi (lambda_max, ci, ri, cr, status, jumlah_kriteria) VALUES (?, ?, ?, ?, ?, ?)");
            $status_str = $ahp_result['status'];
            mysqli_stmt_bind_param($st1, "ddddsi", 
                $ahp_result['lambda_max'], 
                $ahp_result['ci'], 
                $ahp_result['ri'], 
                $ahp_result['cr'], 
                $status_str, 
                $ahp_result['n']
            );
            mysqli_stmt_execute($st1);
            mysqli_stmt_close($st1);

            // Save priority vectors (AHP weights)
            mysqli_query($koneksi, "DELETE FROM ahp_hasil");
            $st2 = mysqli_prepare($koneksi, "INSERT INTO ahp_hasil (id_kriteria, priority_vector) VALUES (?, ?)");
            foreach ($ahp_result['priority_vector'] as $id_kriteria => $pv) {
                mysqli_stmt_bind_param($st2, "id", $id_kriteria, $pv);
                mysqli_stmt_execute($st2);
            }
            mysqli_stmt_close($st2);

            mysqli_commit($koneksi);
            return true;
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            return false;
        }
    }

    /**
     * Fetch validated AHP weights from DB
     */
    public static function getAhpWeightsFromDb($koneksi) {
        // Check consistency status first
        $cq = mysqli_query($koneksi, "SELECT * FROM ahp_konsistensi ORDER BY id DESC LIMIT 1");
        $cons = mysqli_fetch_assoc($cq);

        if (!$cons || $cons['status'] !== 'konsisten') {
            return null; // Not consistent or not calculated
        }

        $weights = [];
        $res = mysqli_query($koneksi, "SELECT * FROM ahp_hasil");
        while ($row = mysqli_fetch_assoc($res)) {
            $weights[$row['id_kriteria']] = floatval($row['priority_vector']);
        }
        return [
            'consistency' => $cons,
            'weights' => $weights
        ];
    }

    /**
     * Process Weighted Product (WP) using AHP-derived weights
     */
    public static function processWP($koneksi) {
        $ahp_data = self::getAhpWeightsFromDb($koneksi);
        if (!$ahp_data) {
            return [
                'can_calculate' => false,
                'message' => 'Bobot AHP belum tersedia atau status perbandingan matriks belum KONSISTEN (CR > 0.10).'
            ];
        }

        $ahp_weights = $ahp_data['weights'];

        // Get kriteria
        $kriteria = self::getKriteria($koneksi);
        if (empty($kriteria)) {
            return ['can_calculate' => false, 'message' => 'Data kriteria kosong.'];
        }

        // Get siswa (alternatives)
        $siswa_arr = [];
        $sq = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY kode_alternatif ASC");
        while ($s = mysqli_fetch_assoc($sq)) $siswa_arr[] = $s;

        if (empty($siswa_arr)) {
            return ['can_calculate' => false, 'message' => 'Data alternatif (siswa) kosong.'];
        }

        // Get scores
        $penilaian_map = [];
        $pq = mysqli_query($koneksi, "SELECT * FROM penilaian");
        while ($p = mysqli_fetch_assoc($pq)) {
            $penilaian_map[$p['id_siswa']][$p['id_kriteria']] = floatval($p['nilai']);
        }

        // Step 1: Normalize AHP weights (Wj = Wj / ΣWj) & handle cost (negative weight)
        $total_ahp_weight = array_sum($ahp_weights);
        $bobot_normal = [];
        foreach ($kriteria as $kr) {
            $w = ($total_ahp_weight > 0) ? ($ahp_weights[$kr['id']] / $total_ahp_weight) : 0;
            if ($kr['jenis'] == 'cost') {
                $w = -$w;
            }
            $bobot_normal[$kr['id']] = $w;
        }

        // Step 2: Calculate Vector S (Si = Π xij ^ Wj)
        $vektor_s = [];
        foreach ($siswa_arr as $siswa) {
            $s = 1.0;
            $complete = true;
            foreach ($kriteria as $kr) {
                $nilai = $penilaian_map[$siswa['id']][$kr['id']] ?? 0;
                if ($nilai <= 0) {
                    $complete = false;
                    break;
                }
                $s *= pow($nilai, $bobot_normal[$kr['id']]);
            }
            if ($complete) {
                $vektor_s[$siswa['id']] = $s;
            }
        }

        // Step 3: Calculate Preference Value (Vector V = Si / ΣSi)
        $total_s = array_sum($vektor_s);
        $vektor_v = [];
        if ($total_s > 0) {
            foreach ($vektor_s as $id => $sv) {
                $vektor_v[$id] = $sv / $total_s;
            }
        }

        // Step 4: Rank students by descending Vi
        $ranking = [];
        $sorted_v = $vektor_v;
        arsort($sorted_v);
        $rank = 1;
        foreach ($sorted_v as $id => $vv) {
            $ranking[$id] = $rank++;
        }

        // Filter list of calculated students only
        $siswa_calculated = array_values(array_filter($siswa_arr, function($s) use ($vektor_s) {
            return isset($vektor_s[$s['id']]);
        }));

        return [
            'can_calculate' => true,
            'kriteria' => $kriteria,
            'siswa' => $siswa_arr,
            'siswa_calculated' => $siswa_calculated,
            'penilaian_map' => $penilaian_map,
            'ahp_weights' => $ahp_weights,
            'bobot_normal' => $bobot_normal,
            'vektor_s' => $vektor_s,
            'total_s' => $total_s,
            'vektor_v' => $vektor_v,
            'ranking' => $ranking,
            'consistency' => $ahp_data['consistency']
        ];
    }
}
