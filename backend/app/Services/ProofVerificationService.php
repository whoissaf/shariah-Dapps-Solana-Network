<?php

namespace App\Services;

use App\Models\Proof;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProofVerificationService
{
    public function __construct(private RuleEvaluator $ruleEvaluator)
    {
    }

    public function verify(User $verifier, int $proofId): array
    {
        return DB::transaction(function () use ($verifier, $proofId) {
            $proof = Proof::with(['claim', 'identity'])->find($proofId);

            if (! $proof) {
                throw ValidationException::withMessages([
                    'proof_id' => ['Proof not found.'],
                ]);
            }

            if ($proof->status !== 'shared') {
                throw ValidationException::withMessages([
                    'proof_id' => ['Proof is not ready for verification.'],
                ]);
            }

            $hasFinalDecision = Verification::where('proof_id', $proof->id)
                ->whereIn('status', ['verified', 'rejected'])
                ->exists();

            if ($hasFinalDecision) {
                throw ValidationException::withMessages([
                    'proof_id' => ['Proof already has final verification decision.'],
                ]);
            }

            $qrValidation = $this->validateQr($proof);
            $identityValidation = $this->validateIdentityCommitment($proof);
            $ruleValidation = $this->validateRule($proof);

            $checks = [
                [
                    'check' => 'qr_valid',
                    'passed' => $qrValidation['passed'],
                    'reason' => $qrValidation['reason'],
                ],
                [
                    'check' => 'identity_commitment_valid',
                    'passed' => $identityValidation['passed'],
                    'reason' => $identityValidation['reason'],
                ],
                [
                    'check' => 'rule_valid',
                    'passed' => $ruleValidation['passed'],
                    'reason' => $ruleValidation['reason'],
                ],
            ];

            $technicalPassed = collect($checks)->every(function (array $check) {
                return $check['passed'];
            });

            $result = [
                'technical_passed' => $technicalPassed,
                'checks' => $checks,
                'rule_results' => $ruleValidation['results'],
            ];

            $pendingVerification = Verification::where('proof_id', $proof->id)
                ->where('status', 'pending')
                ->latest('id')
                ->first();

            if ($pendingVerification) {
                $pendingVerification->forceFill([
                    'verifier_id' => $verifier->id,
                    'result' => $result,
                ])->save();

                $verification = $pendingVerification;
            } else {
                $verification = Verification::create([
                    'proof_id' => $proof->id,
                    'verifier_id' => $verifier->id,
                    'status' => 'pending',
                    'result' => $result,
                ]);
            }

            return [
                'message' => $technicalPassed
                    ? 'Verification checks passed. Awaiting final decision.'
                    : 'Verification checks completed with failures.',
                'verification' => $this->format($verification),
            ];
        });
    }

    private function validateQr(Proof $proof): array
    {
        if (! $proof->qr_nonce || ! $proof->qr_signature || ! $proof->qr_expires_at) {
            return [
                'passed' => false,
                'reason' => 'QR is not available.',
            ];
        }

        if ($proof->qr_expires_at->isPast()) {
            return [
                'passed' => false,
                'reason' => 'QR expired.',
            ];
        }

        $expectedSignature = hash_hmac('sha256', implode(':', [
            $proof->id,
            $proof->qr_nonce,
            $proof->proof_hash,
            $proof->qr_expires_at->toIso8601String(),
        ]), (string) config('app.key'));

        if (! hash_equals($expectedSignature, $proof->qr_signature)) {
            return [
                'passed' => false,
                'reason' => 'QR signature invalid.',
            ];
        }

        return [
            'passed' => true,
            'reason' => null,
        ];
    }

    private function validateIdentityCommitment(Proof $proof): array
    {
        $identity = $proof->identity;

        if (! $identity) {
            return [
                'passed' => false,
                'reason' => 'Identity not found.',
            ];
        }

        if ($identity->status !== 'active') {
            return [
                'passed' => false,
                'reason' => 'Identity is not active.',
            ];
        }

        $commitment = $proof->proof_payload['identity_commitment'] ?? null;

        if (! $commitment || ! hash_equals($identity->identity_commitment, $commitment)) {
            return [
                'passed' => false,
                'reason' => 'Identity commitment mismatch.',
            ];
        }

        return [
            'passed' => true,
            'reason' => null,
        ];
    }

    private function validateRule(Proof $proof): array
    {
        $claim = $proof->claim;

        if (! $claim) {
            return [
                'passed' => false,
                'reason' => 'Claim not found.',
                'results' => [],
            ];
        }

        if (! in_array($claim->status, ['eligible', 'proof_generated'])) {
            return [
                'passed' => false,
                'reason' => 'Claim is not eligible.',
                'results' => [],
            ];
        }

        $evaluation = $this->ruleEvaluator->evaluate($claim);

        if (empty($evaluation['results'])) {
            return [
                'passed' => false,
                'reason' => 'No active rules found.',
                'results' => [],
            ];
        }

        if (! $evaluation['eligible']) {
            return [
                'passed' => false,
                'reason' => 'Rule validation failed.',
                'results' => $evaluation['results'],
            ];
        }

        return [
            'passed' => true,
            'reason' => null,
            'results' => $evaluation['results'],
        ];
    }

    private function format(Verification $verification): array
    {
        $result = $verification->result ?? [];

        return [
            'id' => $verification->id,
            'proof_id' => $verification->proof_id,
            'verifier_id' => $verification->verifier_id,
            'status' => $verification->status,
            'technical_passed' => $result['technical_passed'] ?? false,
            'checks' => $result['checks'] ?? [],
            'rule_results' => $result['rule_results'] ?? [],
            'created_at' => $verification->created_at?->toIso8601String(),
            'updated_at' => $verification->updated_at?->toIso8601String(),
        ];
    }
}
