<?php

namespace App\Services;

use App\Models\User;
use App\Models\Verification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AiExplanationService
{
    public function explain(User $verifier, int $verificationId): array
    {
        return DB::transaction(function () use ($verifier, $verificationId) {
            $verification = Verification::find($verificationId);

            if (! $verification) {
                throw ValidationException::withMessages([
                    'verification_id' => ['Verification not found.'],
                ]);
            }

            if ($verification->status !== 'pending') {
                throw ValidationException::withMessages([
                    'verification_id' => ['AI explanation can only be generated for pending verification.'],
                ]);
            }

            $result = $verification->result ?? [];

            $technicalPassed = $result['technical_passed'] ?? false;
            $checks = $result['checks'] ?? [];
            $ruleResults = $result['rule_results'] ?? [];

            $whyPass = [];
            $whyReject = [];
            $ruleViolated = [];

            foreach ($checks as $check) {
                if ($check['passed']) {
                    $whyPass[] = $this->checkPassMessage($check['check']);
                } else {
                    $whyReject[] = $check['reason'] ?: $this->checkFailMessage($check['check']);
                }
            }

            foreach ($ruleResults as $rule) {
                if ($rule['passed']) {
                    $whyPass[] = 'Rule ' . $rule['rule_name'] . ' passed.';
                } else {
                    $whyReject[] = $rule['reason'] ?: 'Rule validation failed.';

                    $ruleViolated[] = [
                        'rule_code' => $rule['rule_code'],
                        'rule_name' => $rule['rule_name'],
                        'reason' => $rule['reason'],
                    ];
                }
            }

            $recommendation = ($technicalPassed && empty($whyReject)) ? 'approve' : 'reject';

            $summary = $recommendation === 'approve'
                ? 'All verification checks passed. The proof satisfies the required rules.'
                : 'One or more verification checks failed. Review the failed checks before rejecting.';

            $explanation = [
                'model' => 'mvp-simulated-ai',
                'verification_id' => $verification->id,
                'verifier_id' => $verifier->id,
                'recommendation' => $recommendation,
                'summary' => $summary,
                'why_pass' => $whyPass,
                'why_reject' => $whyReject,
                'rule_violated' => $ruleViolated,
                'generated_at' => now()->toIso8601String(),
            ];

            $verification->forceFill([
                'ai_explanation' => $explanation,
            ])->save();

            return [
                'message' => 'AI explanation generated.',
                'explanation' => $explanation,
            ];
        });
    }

    private function checkPassMessage(string $check): string
    {
        return match ($check) {
            'qr_valid' => 'QR signature and expiry are valid.',
            'identity_commitment_valid' => 'Identity commitment matches the proof.',
            'rule_valid' => 'Rule validation passed.',
            default => 'Verification check passed.',
        };
    }

    private function checkFailMessage(string $check): string
    {
        return match ($check) {
            'qr_valid' => 'QR validation failed.',
            'identity_commitment_valid' => 'Identity commitment validation failed.',
            'rule_valid' => 'Rule validation failed.',
            default => 'Verification check failed.',
        };
    }
}
