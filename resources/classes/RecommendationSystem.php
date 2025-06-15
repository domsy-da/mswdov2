<?php

class RecommendationSystem
{
    private PDO $pdo;

    // You can tweak these as needed
    private array $weights = [
        'location'     => 0.3,
        'transactions' => 0.2,
        'demographic'  => 0.3,
        'sitio'        => 0.2,
        'relatives'    => 0.2,
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Entry point: Calculate, store, and return recommendations
     */
    public function generateRecommendations(int $program_id): array
    {
        $this->clearOldRecommendations($program_id);

        $beneficiaries = $this->fetchBeneficiaries();

        foreach ($beneficiaries as $beneficiary) {
            $scores = $this->computeScores($beneficiary);
            $finalScore = $this->combineScores($scores);

            $reason = $this->buildReason($scores, $beneficiary);

            // Pass full scores array to storeRecommendation
            $this->storeRecommendation($program_id, $beneficiary['id'], $finalScore, $reason, $scores);
        }

        return $this->fetchProgramRecommendations($program_id);
    }

    /**
     * Clear old recommendations for this program
     */
    private function clearOldRecommendations(int $program_id): void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM program_recommendations WHERE program_id = ?"
        );
        $stmt->execute([$program_id]);
    }

    /**
     * Fetch all beneficiaries
     */
    private function fetchBeneficiaries(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM beneficiaries");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compute all score components
     */
    private function computeScores(array $b): array
    {
        return [
            'location'     => $this->scoreLocation($b),
            'transactions' => $this->scoreTransaction($b['id']),
            'demographic'  => $this->scoreDemographic($b),
            'sitio'        => $this->scoreSitio($b),
            'relatives'    => $this->scoreRelatives($b['id']),
        ];
    }

    /**
     * Weighted sum of scores
     */
    private function combineScores(array $scores): float
    {
        $sum = 0;
        $timestamp = date('Y-m-d H:i:s');
        
        // Build the log message
        $logMessage = "[$timestamp]\n";
        $logMessage .= "Score Breakdown:\n";
        
        foreach ($scores as $key => $score) {
            $weightedScore = $score * ($this->weights[$key] ?? 0);
            $sum += $weightedScore;
            
            $logMessage .= sprintf(
                "%s: Raw Score = %.2f, Weight = %.2f, Weighted Score = %.2f\n",
                ucfirst($key),
                $score,
                ($this->weights[$key] ?? 0),
                $weightedScore
            );
        }
        
        $logMessage .= sprintf("Final Combined Score: %.2f", $sum);
        
        // Output to browser console
        echo "<script>
            console.log(`$logMessage`);
        </script>";
        
        return round($sum, 2);
    }

    private function scoreLocation(array $b): float
    {
        // ✅ 1) Look up barangay_id by name
        $stmt = $this->pdo->prepare(
            "SELECT id FROM barangays WHERE name = ?"
        );
        $stmt->execute([$b['barangay'] ?? '']);
        $barangay_id = $stmt->fetchColumn() ?: null;

        if (!$barangay_id) return 0; // no matching barangay

        // ✅ 2) Get attributes using barangay_id only
        $stmt = $this->pdo->prepare(
            "SELECT * FROM barangay_sitio_attributes WHERE barangay_id = ?"
        );
        $stmt->execute([$barangay_id]);
        $attr = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$attr) return 0;

        // ✅ 3) Same scoring logic
        $score = 0;

        switch (strtolower($attr['road_access'])) {
            case 'footpath only': $score += 30; break;
            case 'gravel road':   $score += 20; break;
            case 'paved road':    $score += 0;  break;
        }

        $time = (float)($attr['travel_time_to_market'] ?? 0);
        if ($time > 0 && $time < 3) $score += 3;
        elseif ($time >= 3 && $time < 10) $score += 5;
        elseif ($time >= 10 && $time < 30) $score += 10;
        elseif ($time >= 30 && $time < 120) $score += 20;

        $dist = (float)($attr['distance_km'] ?? 0);
        if ($dist >= 0 && $dist < 1) $score += 1;
        elseif ($dist >= 1 && $dist < 5) $score += 3;
        elseif ($dist >= 5 && $dist < 10) $score += 5;
        elseif ($dist >= 10 && $dist < 20) $score += 10;

        switch (strtolower($attr['public_transport'])) {
            case 'none': $score += 20; break;
            case 'limited': $score += 10; break;
            case 'available': $score += 0; break;
        }

        switch (strtolower($attr['communication_signal'])) {
            case 'none': $score += 10; break;
            case 'weak': $score += 5; break;
            case 'strong': $score += 0; break;
        }

        if (!empty($attr['near_river'])) $score += 5;
        if (!empty($attr['near_ocean'])) $score += 5;
        if (!empty($attr['near_forest'])) $score += 5;

        if (strtolower($attr['hazard_zone']) !== 'none') $score += 10;

        return min($score, 115) / 115;
    }

    /**
     * Improved: Score relatives, always gives a value.
     * More dependents + lower education + no job => higher.
     * If relatives don't match the "needier" conditions, they add smaller points.
     * If no relatives, returns a minimum baseline.
     */
    private function scoreRelatives(int $beneficiary_id): float
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM relatives WHERE beneficiary_id = ?"
        );
        $stmt->execute([$beneficiary_id]);
        $relatives = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $score = 0;

        if (!$relatives) {
            // 🟢 No relatives = minimal need, so small baseline, e.g. 20% score.
            return 0.2; 
        }

        foreach ($relatives as $r) {
            // Age
            $age = (int)$r['age'];
            if (strtolower($r['relationship']) === 'child') {
                if ($age < 18) {
                    $score += 10; // child & underage = max
                } else {
                    $score += 5; // child but adult = some support
                }
            } else {
                // Not a child: e.g. spouse or parent
                $score += 3; // small base
            }

            // Education
            $edu = strtolower($r['educational_attainment'] ?? '');
            if (in_array($edu, ['none', 'elementary', 'primary'])) {
                $score += 5; // low education
            } else {
                $score += 2; // has education but still count
            }

            // Occupation
            $occ = strtolower($r['occupation'] ?? '');
            if ($occ === 'none' || $occ === 'unemployed' || empty($occ)) {
                $score += 5; // no job = more burden
            } else {
                $score += 2; // has job = less burden
            }
        }

        // Normalize: say realistic max is ~60
        return min($score, 60) / 60;
    }


    /**
     * Score: fewer transactions → higher score
     */
    private function scoreTransaction(int $beneficiary_id): float
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM transactions WHERE beneficiary_id = ?"
        );
        $stmt->execute([$beneficiary_id]);
        $count = (int)$stmt->fetchColumn();

        // Cap at 10 to avoid negatives
        return max(0, 1 - ($count / 10));
    }

    /**
     * Score: age, occupation, education, civil status
     */
    private function scoreDemographic(array $b): float
    {
        $score = 0;

        // Age: older gets more (0 to 30 pts)
        $age = (int)($b['age'] ?? 0);
        $score += min($age, 70) * (30 / 70);

        // Occupation: no occupation or 'unemployed' gets full 30 pts
        if (empty($b['occupation']) || strtolower($b['occupation']) === 'unemployed') {
            $score += 30;
        } else {
            $score += 10; // minimal for having one
        }

        // Education: elementary gets 20 pts
        if (strtolower($b['education'] ?? '') === 'elementary') {
            $score += 20;
        }else {
            $score += 5; // minimal for other levels
        }

        // Civil status: single parent gets 20 pts
        if (strtolower($b['civil_status'] ?? '') === 'single parent') {
            $score += 20;
        } elseif (strtolower($b['civil_status'] ?? '') === 'widowed' || strtolower($b['civil_status'] ?? '') === 'widower') {
            $score += 10; // minimal for widowed
        } else {
            $score += 5; // minimal for other statuses
        }

        return min($score, 100) / 100;
    }

    /**
     * Sitio: same logic as location for now (can customize later)
     */
    private function scoreSitio(array $b): float
    {
        return $this->scoreLocation($b);
    }

    /**
     * Save recommendation INCLUDING all component scores
     */
    private function storeRecommendation(
        int $program_id,
        int $beneficiary_id,
        float $final_score,
        string $reason,
        array $component_scores
    ): void {
        $stmt = $this->pdo->prepare(
            "INSERT INTO program_recommendations 
            (
                program_id, beneficiary_id, eligibility_score, recommendation_reason,
                location_score, transaction_score, demographic_score, relatives_score, sitio_score
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $program_id,
            $beneficiary_id,
            $final_score,
            $reason,
            $component_scores['location'] ?? 0,
            $component_scores['transactions'] ?? 0,
            $component_scores['demographic'] ?? 0,
            $component_scores['relatives'] ?? 0,
            $component_scores['sitio'] ?? 0
        ]);
    }

    /**
     * Retrieve recommendations for UI
     */
    private function fetchProgramRecommendations(int $program_id): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT b.*, pr.eligibility_score, pr.recommendation_reason
             FROM program_recommendations pr
             JOIN beneficiaries b ON b.id = pr.beneficiary_id
             WHERE pr.program_id = ?
             ORDER BY pr.eligibility_score DESC"
        );
        $stmt->execute([$program_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate explanation based on score thresholds
     */
    private function buildReason(array $scores, array $b): string
    {
        $reasons = [];

        $reasons[] = "Location accessibility: " . round($scores['location'] * 100) . "%";
        $reasons[] = "Transactions inaccessibility: " . round($scores['transactions'] * 100) . "%";
        $reasons[] = "Demographic accessibility: " . round($scores['demographic'] * 100) . "%";
        $reasons[] = "Relatives accessibility: " . round($scores['relatives'] * 100) . "%";

        if ($scores['location'] > 0.7) $reasons[] = "High-need barangay (poor road, remote, or risky)";
        if ($scores['transactions'] > 0.7) $reasons[] = "Few or no previous benefits received";

        if ($scores['demographic'] > 0.5) {
            if (($b['age'] ?? 0) > 60) $reasons[] = "Elderly beneficiary";
            if (empty($b['occupation']) || strtolower($b['occupation'] === 'unemployed')) $reasons[] = "No stable occupation";
            if (strtolower($b['education'] ?? '') === 'elementary') $reasons[] = "Low educational attainment";
            if (strtolower($b['civil_status'] ?? '') === 'single parent') $reasons[] = "Single parent";
        }

        if ($scores['relatives'] > 0.5) $reasons[] = "Has dependents with low education or no job";

        if ($scores['sitio'] > 0.7) $reasons[] = "Remote sitio condition";

        return implode("; ", $reasons);
    }
}
